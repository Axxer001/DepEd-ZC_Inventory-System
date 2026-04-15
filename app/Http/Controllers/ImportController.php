<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    /**
     * Show the import page.
     */
    public function show()
    {
        $categories = DB::table('categories')->orderBy('name')->pluck('name');
        
        $itemsMap = DB::table('items')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select('items.name as item_name', 'categories.name as cat_name')
            ->get()
            ->groupBy('cat_name')
            ->map(function($rows) { return $rows->pluck('item_name')->unique()->values(); });

        $subItemsMap = DB::table('sub_items')
            ->join('items', 'sub_items.item_id', '=', 'items.id')
            ->select('sub_items.name as sub_name', 'items.name as item_name')
            ->get()
            ->groupBy('item_name')
            ->map(function($rows) { return $rows->pluck('sub_name')->unique()->values(); });
        
        $sources = DB::table('stakeholders')
            ->where('type', 'Distributor')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->pluck('name');

        return view('partials.import', compact('categories', 'itemsMap', 'subItemsMap', 'sources'));
    }

    /**
     * Download the CSV Template safely regardless of local server MIME limits.
     * Optionally accepts dynamic rows to pre-fill the template.
     */
    public function downloadTemplate(Request $request)
    {
        $headers = ['category', 'item_name', 'sub_item_name', 'quantity', 'condition', 'source', 'unit_price', 'date_acquired', 'is_serialized', 'property_number', 'serial_number'];
        
        $callback = function () use ($headers, $request) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers); // Write headers

            // If user provided custom rows, write them
            if ($request->has('rows') && is_array($request->rows)) {
                foreach ($request->rows as $r) {
                    $row = [
                        $r['category'] ?? '',
                        $r['item_name'] ?? '',
                        $r['sub_item_name'] ?? '',
                        $r['quantity'] ?? '1',
                        $r['condition'] ?? 'Serviceable',
                        $r['source'] ?? '',
                        '', // unit_price
                        now()->toDateString(), // date_acquired — auto-set to today
                        $r['is_serialized'] ?? 'no',
                        '', // property_number
                        ''  // serial_number
                    ];
                    fputcsv($file, $row);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="DepEd_Asset_Import_Template.csv"',
        ]);
    }

    /**
     * Process the uploaded CSV file — parse and preview.
     */
    public function process(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->withErrors(['csv_file' => 'Unable to read the uploaded file.']);
        }

        $csvRows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $csvRows[] = $row;
        }
        fclose($handle);

        if (count($csvRows) < 2) {
            return back()->withErrors(['csv_file' => 'The CSV file must contain at least a header row and one data row.']);
        }

        // Strip UTF-8 BOM from the first cell if present
        if (!empty($csvRows[0][0])) {
            $csvRows[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', $csvRows[0][0]);
        }

        // Validate headers
        $expectedHeaders = ['category', 'item_name', 'sub_item_name', 'quantity', 'condition', 'source', 'unit_price', 'date_acquired', 'is_serialized', 'property_number', 'serial_number'];
        $actualHeaders = array_map('strtolower', array_map('trim', $csvRows[0]));

        $missingHeaders = array_diff($expectedHeaders, $actualHeaders);
        if (!empty($missingHeaders)) {
            return back()->withErrors(['csv_file' => 'Missing required columns: ' . implode(', ', $missingHeaders) . '. Please use the standard CSV template.']);
        }

        // Store CSV data in session for the confirmation step
        session(['csv_import_data' => $csvRows]);

        // The view always needs these variables for the JS builder data layer
        $categories = DB::table('categories')->orderBy('name')->pluck('name');
        $itemsMap   = DB::table('items')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select('items.name as item_name', 'categories.name as cat_name')
            ->get()->groupBy('cat_name')
            ->map(fn($rows) => $rows->pluck('item_name')->unique()->values());
        $subItemsMap = DB::table('sub_items')
            ->join('items', 'sub_items.item_id', '=', 'items.id')
            ->select('sub_items.name as sub_name', 'items.name as item_name')
            ->get()->groupBy('item_name')
            ->map(fn($rows) => $rows->pluck('sub_name')->unique()->values());
        $sources = DB::table('stakeholders')
            ->where('type', 'Distributor')->whereNull('parent_id')
            ->orderBy('name')->pluck('name');

        return view('partials.import', compact('csvRows', 'categories', 'itemsMap', 'subItemsMap', 'sources'));
    }

    /**
     * Confirm and execute the actual database import.
     */
    public function confirm(Request $request)
    {
        $csvRows = session('csv_import_data');

        if (!$csvRows || count($csvRows) < 2) {
            return redirect()->route('assets.import')->withErrors(['csv_file' => 'No import data found. Please upload a CSV file first.']);
        }

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $headers = array_map('strtolower', array_map('trim', $csvRows[0]));
        $dataRows = array_slice($csvRows, 1);

        $totalImported = 0;
        $totalSkipped = 0;
        $messages = [];

        DB::beginTransaction();
        try {
            foreach ($dataRows as $rowIndex => $row) {
                // Map columns by header position
                $data = [];
                foreach ($headers as $i => $header) {
                    $data[$header] = isset($row[$i]) ? trim($row[$i]) : '';
                }

                // Skip completely empty rows
                if (empty($data['item_name']) && empty($data['sub_item_name'])) {
                    $totalSkipped++;
                    continue;
                }

                $categoryName = $data['category'] ?? '';
                $itemName = $data['item_name'] ?? '';
                $subItemName = $data['sub_item_name'] ?? '';
                $quantity = max(1, (int)($data['quantity'] ?? 1));
                $condition = !empty($data['condition']) ? $data['condition'] : 'Serviceable';
                $sourceName = $data['source'] ?? '';
                $unitPrice = !empty($data['unit_price']) ? (float)$data['unit_price'] : null;
                $dateAcquired = !empty($data['date_acquired']) ? $data['date_acquired'] : now()->toDateString();
                $isSerialized = in_array(strtolower($data['is_serialized'] ?? ''), ['yes', '1', 'true']);
                $propertyNumber = $data['property_number'] ?? null;
                $serialNumber = $data['serial_number'] ?? null;

                // Enforce serialized logic
                if ($isSerialized) {
                    $quantity = 1;
                }

                // --- Resolve Category (firstOrCreate) ---
                $categoryId = null;
                if (!empty($categoryName)) {
                    $existingCat = DB::table('categories')
                        ->whereRaw('LOWER(name) = ?', [strtolower($categoryName)])
                        ->first();

                    if ($existingCat) {
                        $categoryId = $existingCat->id;
                    } else {
                        $categoryId = DB::table('categories')->insertGetId([
                            'name' => $categoryName,
                            'created_at' => now(),
                        ]);
                        DB::table('system_logs')->insert([
                            'user' => $userName,
                            'activity' => "[CSV Import] Auto-created category: {$categoryName}",
                            'module' => 'Categories',
                            'action_type' => 'Create',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // --- Resolve Source/Distributor (firstOrCreate) ---
                $distributorId = null;
                if (!empty($sourceName)) {
                    $existingDist = DB::table('stakeholders')
                        ->whereRaw('LOWER(name) = ?', [strtolower($sourceName)])
                        ->where('type', 'Distributor')
                        ->whereNull('parent_id')
                        ->first();

                    if ($existingDist) {
                        $distributorId = $existingDist->id;
                    } else {
                        // New source: register with name only; all classification fields null
                        $distributorId = DB::table('stakeholders')->insertGetId([
                            'name'       => $sourceName,
                            'type'       => 'Distributor',
                            'status'     => 'Active',
                            'created_at' => now(),
                            'updated_at' => now(),
                            // entity_type, parent_id, school_id, position, person_name intentionally left null
                        ]);
                        DB::table('system_logs')->insert([
                            'user' => $userName,
                            'activity' => "[CSV Import] Auto-created distributor: {$sourceName}",
                            'module' => 'Stakeholders',
                            'action_type' => 'Create',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // --- Resolve Item (firstOrCreate) ---
                if (empty($itemName)) {
                    $totalSkipped++;
                    continue;
                }

                $existingItem = DB::table('items')
                    ->whereRaw('LOWER(name) = ?', [strtolower($itemName)])
                    ->first();

                if ($existingItem) {
                    $itemId = $existingItem->id;
                    // Update master quantity
                    DB::table('items')->where('id', $itemId)->update([
                        'master_quantity' => DB::raw("master_quantity + {$quantity}"),
                        'updated_at' => now(),
                    ]);
                } else {
                    $itemId = DB::table('items')->insertGetId([
                        'name' => $itemName,
                        'category_id' => $categoryId,
                        'master_quantity' => $quantity,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    DB::table('system_logs')->insert([
                        'user' => $userName,
                        'activity' => "[CSV Import] Registered item: {$itemName}",
                        'module' => 'Items',
                        'action_type' => 'Create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // --- Insert Sub-Item ---
                if (empty($subItemName)) {
                    $subItemName = $itemName; // Fall back to item name
                }

                // For non-serialized items, check if an identical sub-item already exists
                $existingSub = null;
                if (!$isSerialized) {
                    $existingSub = DB::table('sub_items')
                        ->where('item_id', $itemId)
                        ->where('is_serialized', false)
                        ->whereRaw('LOWER(name) = ?', [strtolower($subItemName)])
                        ->first();
                }

                if ($existingSub) {
                    // Stack quantity on existing sub-item
                    DB::table('sub_items')->where('id', $existingSub->id)->update([
                        'quantity' => DB::raw("quantity + {$quantity}"),
                        'condition' => $condition,
                        'updated_at' => now(),
                    ]);
                } else {
                    // Insert new sub-item
                    DB::table('sub_items')->insert([
                        'name' => $subItemName,
                        'item_id' => $itemId,
                        'distributor_id' => $distributorId,
                        'quantity' => $quantity,
                        'condition' => $condition,
                        'qr_hash' => Str::uuid()->toString(),
                        'is_serialized' => $isSerialized,
                        'unit_price' => $unitPrice,
                        'date_acquired' => $dateAcquired,
                        'property_number' => !empty($propertyNumber) ? $propertyNumber : null,
                        'serial_number' => !empty($serialNumber) ? $serialNumber : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $totalImported++;
            }

            DB::commit();

            // Log the bulk import event
            DB::table('system_logs')->insert([
                'user' => $userName,
                'activity' => "Bulk CSV Import: {$totalImported} asset rows processed, {$totalSkipped} skipped",
                'module' => 'Import',
                'action_type' => 'Create',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Clear session data
            session()->forget('csv_import_data');

            return redirect()->route('assets.import')->with('success', "Import complete! {$totalImported} asset(s) processed successfully." . ($totalSkipped > 0 ? " {$totalSkipped} row(s) were skipped." : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('assets.import')->withErrors(['csv_file' => 'Import failed: ' . $e->getMessage()]);
        }
    }
}
