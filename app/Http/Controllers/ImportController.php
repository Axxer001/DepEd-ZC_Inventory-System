<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $sources = DB::table('acquisition_sources')
            ->orderBy('name')
            ->select('name', 'source_type')
            ->get();

        // subItemsMap is no longer needed (sub_items table dropped)
        $subItemsMap = collect();

        return view('partials.download-reports', compact('categories', 'itemsMap', 'subItemsMap', 'sources'));
    }

    /**
     * Download the CSV Template safely regardless of local server MIME limits.
     * Optionally accepts dynamic rows to pre-fill the template.
     */
    public function downloadTemplate(Request $request)
    {
        $headers = ['category', 'item_name', 'sub_item_name', 'quantity', 'condition', 'source', 'source_type', 'unit_price', 'date_acquired', 'is_serialized', 'property_number', 'serial_number'];
        
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
                        $r['source_type'] ?? 'School',
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
        $expectedHeaders = ['category', 'item_name', 'sub_item_name', 'quantity', 'condition', 'source', 'source_type', 'unit_price', 'date_acquired', 'is_serialized', 'property_number', 'serial_number'];
        $actualHeaders = array_map('strtolower', array_map('trim', $csvRows[0]));

        $missingHeaders = array_diff($expectedHeaders, $actualHeaders);
        if (!empty($missingHeaders)) {
            return back()->withErrors(['csv_file' => 'Missing required columns: ' . implode(', ', $missingHeaders) . '. Please use the standard CSV template.']);
        }

        session(['csv_import_data' => $csvRows]);

        // The view needs these variables for the JS builder data layer
        $categories = DB::table('categories')->orderBy('name')->pluck('name');
        $itemsMap   = DB::table('items')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select('items.name as item_name', 'categories.name as cat_name')
            ->get()->groupBy('cat_name')
            ->map(fn($rows) => $rows->pluck('item_name')->unique()->values());
        $subItemsMap = collect();
        $sources = DB::table('acquisition_sources')
            ->orderBy('name')->select('name', 'source_type')->get();

        $headers = $actualHeaders;
        $rawDataRows = array_slice($csvRows, 1);
        $previewRows = [];
        foreach ($rawDataRows as $rawRow) {
            $map = [];
            foreach ($headers as $i => $col) {
                $map[$col] = isset($rawRow[$i]) ? trim($rawRow[$i]) : '';
            }
            $previewRows[] = $map;
        }
        $csvRows = $previewRows;

        return view('partials.download-reports', compact('csvRows', 'headers', 'categories', 'itemsMap', 'subItemsMap', 'sources'));
    }

    /**
     * Confirm and execute the actual database import.
     * Now writes to the new schema: asset_sources (replaces sub_items).
     */
    public function confirm(Request $request)
    {
        $dataRows = $request->input('rows');

        if (!$dataRows || count($dataRows) === 0) {
            return redirect()->route('assets.reports')->withErrors(['csv_file' => 'No import data found in submission block. Please preview your import before confirming.']);
        }

        $userName = auth()->user() ? auth()->user()->name : 'System';

        $totalImported = 0;
        $totalSkipped = 0;

        DB::beginTransaction();
        try {
            foreach ($dataRows as $rowIndex => $data) {

                if (empty($data['item_name']) && empty($data['sub_item_name'])) {
                    $totalSkipped++;
                    continue;
                }

                $categoryName = $data['category'] ?? '';
                $itemName = $data['item_name'] ?? '';
                $description = $data['sub_item_name'] ?? '';
                $quantity = max(1, (int)($data['quantity'] ?? 1));
                $sourceName = $data['source'] ?? '';
                $sourceType = $data['source_type'] ?? '';

                // Validate source_type
                $allowedSourceTypes = ['Internal', 'External'];
                // Map old values to new
                $sourceTypeMap = ['School' => 'Internal', 'External' => 'External', 'Individual' => 'External'];
                if (!empty($sourceType)) {
                    $sourceType = $sourceTypeMap[$sourceType] ?? $sourceType;
                    if (!in_array($sourceType, $allowedSourceTypes, true)) {
                        $sourceType = 'Internal'; // Default fallback
                    }
                } else {
                    $sourceType = 'Internal';
                }

                $unitPriceRaw = !empty($data['unit_price']) ? str_replace(',', '', $data['unit_price']) : null;
                $unitPrice = $unitPriceRaw !== null ? (float)$unitPriceRaw : 0;

                $dateAcquiredRaw = !empty($data['date_acquired']) ? trim($data['date_acquired']) : '';
                $dateAcquired = now()->toDateString();
                if (!empty($dateAcquiredRaw)) {
                    try {
                        $dateAcquired = \Carbon\Carbon::parse($dateAcquiredRaw)->toDateString();
                    } catch (\Exception $e) {
                        // Keep the default now()
                    }
                }

                // ── Resolve Category ──
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

                // ── Resolve Acquisition Source ──
                $acquisitionSourceId = null;
                if (!empty($sourceName)) {
                    $existingSrc = DB::table('acquisition_sources')
                        ->whereRaw('LOWER(name) = ?', [strtolower($sourceName)])
                        ->first();

                    if ($existingSrc) {
                        $acquisitionSourceId = $existingSrc->id;
                    } else {
                        $acquisitionSourceId = DB::table('acquisition_sources')->insertGetId([
                            'name' => $sourceName,
                            'source_type' => $sourceType,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        DB::table('system_logs')->insert([
                            'user' => $userName,
                            'activity' => "[CSV Import] Auto-created acquisition source: {$sourceName}",
                            'module' => 'Acquisition Sources',
                            'action_type' => 'Create',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } else {
                    // Create a default "Unknown Source" if none provided
                    $defaultSrc = DB::table('acquisition_sources')
                        ->where('name', 'Unknown Source')->first();
                    if ($defaultSrc) {
                        $acquisitionSourceId = $defaultSrc->id;
                    } else {
                        $acquisitionSourceId = DB::table('acquisition_sources')->insertGetId([
                            'name' => 'Unknown Source',
                            'source_type' => 'Internal',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // ── Resolve Item ──
                if (empty($itemName)) {
                    $totalSkipped++;
                    continue;
                }

                $existingItem = DB::table('items')
                    ->whereRaw('LOWER(name) = ?', [strtolower($itemName)])
                    ->first();

                if ($existingItem) {
                    $itemId = $existingItem->id;
                } else {
                    $itemId = DB::table('items')->insertGetId([
                        'name' => $itemName,
                        'category_id' => $categoryId,
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

                // ── Insert Asset Source (replaces sub_items) ──
                DB::table('asset_sources')->insert([
                    'item_id' => $itemId,
                    'description' => !empty($description) ? $description : null,
                    'acquisition_source_id' => $acquisitionSourceId,
                    'mode_of_acquisition' => 'CSV Import',
                    'asset_cost' => $unitPrice,
                    'quantity' => $quantity,
                    'acceptance_date' => $dateAcquired,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalImported++;
            }

            DB::commit();

            DB::table('system_logs')->insert([
                'user' => $userName,
                'activity' => "Bulk CSV Import: {$totalImported} asset rows processed, {$totalSkipped} skipped",
                'module' => 'Import',
                'action_type' => 'Create',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            session()->forget('csv_import_data');

            return redirect()->route('assets.reports')->with('success', "Import complete! {$totalImported} asset(s) processed successfully." . ($totalSkipped > 0 ? " {$totalSkipped} row(s) were skipped." : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('assets.reports')->withErrors(['csv_file' => 'Import failed: ' . $e->getMessage()]);
        }
    }
}
