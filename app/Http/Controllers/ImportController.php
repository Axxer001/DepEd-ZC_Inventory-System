<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Classification;
use App\Models\Category;
use App\Models\Item;
use App\Models\AcquisitionSource;
use App\Models\ProcurementMode;
use App\Models\Employee;
use App\Models\School;
use App\Models\Office;
use App\Models\AssetSource;
use App\Models\AssetAssignment;

class ImportController extends Controller
{
    /**
     * Build in-memory lookup cache for bulk operations.
     */
    private function buildLookupCache(): array
    {
        $classifications = [];
        foreach (Classification::get() as $class) {
            $classifications[strtolower(trim($class->name))] = $class->id;
        }

        $categories = [];
        foreach (Category::get() as $cat) {
            $classId = $cat->classification_id;
            if (!isset($categories[$classId])) {
                $categories[$classId] = [];
            }
            $categories[$classId][strtolower(trim($cat->name))] = $cat;
        }

        $items = [];
        foreach (Item::get() as $item) {
            $catId = $item->category_id;
            if (!isset($items[$catId])) {
                $items[$catId] = [];
            }
            $items[$catId][strtolower(trim($item->name))] = $item;
        }

        return [
            'classifications'     => $classifications,
            'categories'          => $categories,
            'items'               => $items,
            'acquisition_sources' => array_change_key_case(AcquisitionSource::pluck('id', 'name')->toArray(), CASE_LOWER),
            'procurement_modes'   => array_change_key_case(ProcurementMode::pluck('id', 'name')->toArray(), CASE_LOWER),
            'employees'           => Employee::get()->keyBy(function($e) {
                                        return strtolower(trim("{$e->first_name} {$e->last_name}"));
                                    }),
            'schools'             => School::pluck('id', 'school_id')->toArray(),
            'offices'             => Office::pluck('id', 'office_id')->toArray(),
        ];
    }

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
        $headers = ['classification', 'category', 'item_name', 'sub_item_name', 'quantity', 'condition', 'source', 'source_type', 'unit_price', 'date_acquired', 'is_serialized'];
        
        $callback = function () use ($headers, $request) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers); // Write headers

            // If user provided custom rows, write them
            if ($request->has('rows') && is_array($request->rows)) {
                foreach ($request->rows as $r) {
                    $row = [
                        $r['classification'] ?? 'Unclassified',
                        $r['category'] ?? '',
                        $r['item_name'] ?? '',
                        $r['sub_item_name'] ?? '',
                        $r['quantity'] ?? '1',
                        $r['condition'] ?? 'Good Condition',
                        $r['source'] ?? '',
                        $r['source_type'] ?? 'Internal',
                        '', // unit_price
                        now()->toDateString(), // date_acquired
                        $r['is_serialized'] ?? 'no'
                    ];
                    fputcsv($file, $row);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="DepEd_Asset_Registration_Template.csv"',
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
        $expectedHeaders = ['classification', 'category', 'item_name', 'sub_item_name', 'quantity', 'condition', 'source', 'source_type', 'unit_price', 'date_acquired', 'is_serialized'];
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
     * Now writes to the new schema: asset_sources and asset_assignments.
     * Uses lookup cache and match-only employee resolution.
     */
    public function confirm(Request $request)
    {
        $dataRows = $request->input('rows');

        if (!$dataRows || count($dataRows) === 0) {
            return redirect()->route('assets.reports')->withErrors(['csv_file' => 'No import data found in submission block. Please preview your import before confirming.']);
        }

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $cache = $this->buildLookupCache();
        $totalImported = 0;
        $totalSkipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($dataRows as $rowIndex => $data) {

                if (empty($data['item_name']) && empty($data['sub_item_name'])) {
                    $totalSkipped++;
                    continue;
                }

                $className = trim($data['classification'] ?? '');
                $categoryName = trim($data['category'] ?? '');
                $itemName = trim($data['item_name'] ?? 'Unknown Item');
                $description = $data['sub_item_name'] ?? '';
                $quantity = max(1, (int)($data['quantity'] ?? 1));
                $sourceName = $data['source'] ?? '';
                $sourceType = $data['source_type'] ?? '';
                $rawCondition = strtolower(trim($data['condition'] ?? ''));

                $classNameLower = strtolower($className);
                $categoryNameLower = strtolower($categoryName);
                $itemNameLower = strtolower($itemName);

                if (empty($className)) {
                    $errors[] = "Row " . ($rowIndex + 1) . ": Classification is required.";
                    continue;
                }
                if (empty($categoryName)) {
                    $errors[] = "Row " . ($rowIndex + 1) . ": Category is required.";
                    continue;
                }

                // ── Resolve Hierarchy (Classification -> Category -> Item) ──
                $classId = $cache['classifications'][$classNameLower] ?? null;
                if (!$classId) {
                    $errors[] = "Row " . ($rowIndex + 1) . ": Classification '{$className}' does not exist. Please register it first.";
                    continue;
                }

                $category = $cache['categories'][$classId][$categoryNameLower] ?? null;
                if (!$category) {
                    $errors[] = "Row " . ($rowIndex + 1) . ": Category '{$categoryName}' does not exist under Classification '{$className}'. Please register it first.";
                    continue;
                }
                $catId = $category->id;

                $item = $cache['items'][$catId][$itemNameLower] ?? null;
                if (!$item) {
                    $item = Item::create([
                        'category_id' => $catId,
                        'name' => $itemName
                    ]);
                    $cache['items'][$catId][$itemNameLower] = $item;
                }
                $itemId = $item->id;

                // ── Resolve Acquisition Source ──
                // Map old values to new
                $sourceTypeMap = ['School' => 'Internal', 'External' => 'External', 'Individual' => 'External'];
                $sourceType = $sourceTypeMap[$sourceType] ?? $sourceType;
                if (!in_array($sourceType, ['Internal', 'External'])) {
                    $sourceType = 'Internal';
                }

                if (empty($sourceName)) {
                    $sourceName = 'Unknown Source';
                }

                $acqSourceId = $cache['acquisition_sources'][$sourceName] ?? null;
                if (!$acqSourceId) {
                    $acqSourceId = AcquisitionSource::create([
                        'name' => $sourceName,
                        'source_type' => $sourceType
                    ])->id;
                    $cache['acquisition_sources'][$sourceName] = $acqSourceId;
                }

                // ── Resolve Procurement Mode ──
                $modeName = 'CSV Import';
                $modeId = $cache['procurement_modes'][$modeName] ?? null;
                if (!$modeId) {
                    $modeId = ProcurementMode::create(['name' => $modeName])->id;
                    $cache['procurement_modes'][$modeName] = $modeId;
                }

                // ── Employee Logic Removed (Default to AMU/Warehouse) ──
                $employeeId = null;

                // ── Condition Mapping ──
                $conditionMap = [
                    'good'           => 'Good Condition',
                    'good condition' => 'Good Condition',
                    'serviceable'    => 'Good Condition',
                    'needs repair'   => 'Needs Repair',
                    'repair'         => 'Needs Repair',
                    'minor repair'   => 'Needs Repair',
                    'major repair'   => 'Needs Repair',
                    'unserviceable'  => 'Unserviceable',
                    'condemned'      => 'Unserviceable',
                    'not useable'    => 'Unserviceable',
                ];
                $condition = $conditionMap[$rawCondition] ?? 'Good Condition';

                $unitPriceRaw = !empty($data['unit_price']) ? str_replace(',', '', $data['unit_price']) : null;
                $unitPrice = $unitPriceRaw !== null ? (float)$unitPriceRaw : 0;

                $dateAcquiredRaw = !empty($data['date_acquired']) ? trim($data['date_acquired']) : '';
                $dateAcquired = now()->toDateString();
                if (!empty($dateAcquiredRaw)) {
                    try {
                        $dateAcquired = \Carbon\Carbon::parse($dateAcquiredRaw)->toDateString();
                    } catch (\Exception $e) { }
                }

                // ── Insert Asset Source ──
                $assetSource = AssetSource::create([
                    'item_id'                => $itemId,
                    'description'            => !empty($description) ? $description : null,
                    'acquisition_source_id'  => $acqSourceId,
                    'procurement_mode_id'    => $modeId,
                    'asset_cost'             => $unitPrice,
                    'quantity'               => $quantity,
                    'acceptance_date'        => $dateAcquired,
                    'condition'              => $condition,
                ]);

                // ── Insert Asset Assignment (Unassigned/AMU) ──
                AssetAssignment::create([
                    'asset_source_id'  => $assetSource->id,
                    'employee_id'      => null,
                    'property_number'  => null,
                    'acquisition_cost' => $unitPrice * $quantity,
                    'acquisition_date' => $dateAcquired,
                ]);

                $totalImported++;
            }

            if (!empty($errors)) {
                DB::rollBack();
                return redirect()->route('assets.reports')->withErrors(['csv_file' => 'Import failed due to validation errors: '])->with('import_errors', $errors);
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
