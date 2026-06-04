<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BuildingImportController extends Controller
{
    /**
     * Show the PIF import page.
     */
    public function show()
    {
        return view('import-buildings');
    }

    /**
     * Parse the uploaded .xlsx, detect template type per sheet, extract rows,
     * store in session, return preview.
     */
    public function preview(Request $request)
    {
        set_time_limit(600);
        $request->validate(['xlsx_file' => 'required|file|mimes:xlsx,xls|max:51200']);

        try {
            $spreadsheet = IOFactory::load($request->file('xlsx_file')->getRealPath());
        } catch (\Exception $e) {
            return back()->withErrors(['xlsx_file' => 'Unable to read the uploaded file: ' . $e->getMessage()]);
        }

        $allGroups  = ['buildings' => [], 'assets' => []];
        $skipSheets = ['instruction', 'dropdown', 'sheet1'];

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if (in_array(strtolower(trim($sheetName)), $skipSheets)) continue;
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) continue;

            $type = $this->detectTemplateType($sheet);
            if ($type === 'buildings') {
                $allGroups['buildings'] = array_merge($allGroups['buildings'], $this->parseBuildingSheet($sheet));
            } elseif ($type === 'assets') {
                $allGroups['assets'] = array_merge($allGroups['assets'], $this->parseAssetSheet($sheet, $sheetName));
            }
        }

        // Release spreadsheet memory immediately to protect the 4GB local limit
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        if (empty($allGroups['buildings']) && empty($allGroups['assets'])) {
            return back()->withErrors(['xlsx_file' => 'No valid data rows found.']);
        }

        $gemini = new \App\Services\GeminiService();
        $allGroups['buildings'] = $gemini->sanitizeRows($allGroups['buildings'], 'buildings');
        $allGroups['assets']    = $gemini->sanitizeRows($allGroups['assets'], 'assets');

        // High-density memory maps for duplicate detection
        $duplicates = [];
        $caches = [
            'db_assets'    => array_change_key_case(DB::table('asset_assignments')->whereIn('property_number', collect($allGroups['assets'])->pluck('property_number')->filter()->toArray())->pluck('id', 'property_number')->toArray(), CASE_LOWER),
            'db_buildings' => array_change_key_case(DB::table('building_records')->whereIn('property_number', collect($allGroups['buildings'])->pluck('property_number')->filter()->toArray())->pluck('id', 'property_number')->toArray(), CASE_LOWER),
            'file_assets'  => [],
            'file_bldgs'   => [],
        ];

        $dbDuplicates = 0;
        $fileDuplicates = 0;

        foreach ($allGroups['assets'] as $i => $row) {
            $pn = strtolower(trim($row['property_number'] ?? ''));
            if (!$pn) continue;
            if (isset($caches['file_assets'][$pn])) {
                $duplicates[] = ['index' => $i, 'property_number' => $row['property_number'], 'reason' => 'Repeated in file', 'article' => $row['article'] ?? '', 'type' => 'asset'];
                $fileDuplicates++;
            } elseif (isset($caches['db_assets'][$pn])) {
                $duplicates[] = ['index' => $i, 'property_number' => $row['property_number'], 'reason' => 'Exists in database', 'article' => $row['article'] ?? '', 'type' => 'asset'];
                $dbDuplicates++;
            }
            $caches['file_assets'][$pn] = true;
        }

        foreach ($allGroups['buildings'] as $i => $row) {
            $pn = strtolower(trim($row['property_number'] ?? ''));
            if (!$pn) continue;
            if (isset($caches['file_bldgs'][$pn])) {
                $duplicates[] = ['index' => $i, 'property_number' => $row['property_number'], 'reason' => 'Repeated in file', 'article' => $row['article'] ?? '', 'type' => 'building'];
                $fileDuplicates++;
            } elseif (isset($caches['db_buildings'][$pn])) {
                $duplicates[] = ['index' => $i, 'property_number' => $row['property_number'], 'reason' => 'Exists in database', 'article' => $row['article'] ?? '', 'type' => 'building'];
                $dbDuplicates++;
            }
            $caches['file_bldgs'][$pn] = true;
        }

        session(['pif_import_data' => $allGroups]);

        return view('import-buildings', [
            'allGroups'      => $allGroups,
            'totalBuildings' => count($allGroups['buildings']),
            'totalAssets'    => count($allGroups['assets']),
            'duplicates'     => $duplicates,
            'dbDuplicates'   => $dbDuplicates,
            'fileDuplicates' => $fileDuplicates
        ]);
    }

    /**
     * Confirm and insert all parsed rows into their respective tables.
     */
    public function confirm(Request $request)
    {
        set_time_limit(0);
        $allGroups = session('pif_import_data');

        if (!$allGroups || (empty($allGroups['buildings']) && empty($allGroups['assets']))) {
            return redirect()->route('buildings.import')->withErrors(['xlsx_file' => 'No import data found.']);
        }

        $userName = auth()->user() ? auth()->user()->email : 'System';
        $duplicateAction = $request->input('duplicate_action', 'keep');

        // Apply duplicate action using modern collection pipeline
        if ($duplicateAction === 'overwrite') {
            DB::table('asset_assignments')->whereIn('property_number', collect($allGroups['assets'])->pluck('property_number')->filter()->toArray())->delete();
            DB::table('building_records')->whereIn('property_number', collect($allGroups['buildings'])->pluck('property_number')->filter()->toArray())->delete();
        } elseif (in_array($duplicateAction, ['remove', 'skip_existing'])) {
            $allGroups['assets'] = collect($allGroups['assets'])->unique('property_number')->reject(function($row) {
                return !empty($row['property_number']) && DB::table('asset_assignments')->where('property_number', $row['property_number'])->exists();
            })->toArray();
            $allGroups['buildings'] = collect($allGroups['buildings'])->unique('property_number')->reject(function($row) {
                return !empty($row['property_number']) && DB::table('building_records')->where('property_number', $row['property_number'])->exists();
            })->toArray();
        }

        // ── Optimized Memory Maps for Lookups ──
        $caches = [
            'class'   => array_change_key_case(DB::table('classifications')->pluck('id', 'name')->toArray(), CASE_LOWER),
            'cat'     => array_change_key_case(DB::table('categories')->pluck('id', 'name')->toArray(), CASE_LOWER),
            'item'    => array_change_key_case(DB::table('items')->pluck('id', 'name')->toArray(), CASE_LOWER),
            'source'  => array_change_key_case(DB::table('acquisition_sources')->pluck('id', 'name')->toArray(), CASE_LOWER),
            'mode'    => array_change_key_case(DB::table('procurement_modes')->pluck('id', 'name')->toArray(), CASE_LOWER),
            'schools' => DB::table('schools')->pluck('id', 'school_id')->toArray(),
            'b_class' => array_change_key_case(DB::table('building_classifications')->pluck('id', 'name')->toArray(), CASE_LOWER),
            'b_types' => array_change_key_case(DB::table('building_types')->pluck('id', 'name')->toArray(), CASE_LOWER),
        ];

        DB::beginTransaction();
        try {
            // Process Assets
            foreach ($allGroups['assets'] as $row) {
                $className = trim($row['classification'] ?? 'Unclassified');
                $classId   = $caches['class'][strtolower($className)] ??= DB::table('classifications')->insertGetId(['name' => $className, 'created_at' => now()]);

                $catName = 'Uncategorized';
                $catId   = $caches['cat'][strtolower($catName)] ??= DB::table('categories')->insertGetId(['classification_id' => $classId, 'name' => $catName, 'created_at' => now()]);

                $itemName = trim($row['article'] ?? 'Unknown Item');
                $itemId   = $caches['item'][strtolower($itemName)] ??= DB::table('items')->insertGetId(['category_id' => $catId, 'name' => $itemName, 'created_at' => now()]);

                $sourceName = trim($row['source_of_acquisition'] ?? 'DepEd Central Office');
                $sourceId   = $caches['source'][strtolower($sourceName)] ??= DB::table('acquisition_sources')->insertGetId(['name' => $sourceName, 'source_type' => 'Internal', 'created_at' => now()]);

                $modeName = trim($row['procurement_mode'] ?? 'Direct Purchase');
                $modeId   = $caches['mode'][strtolower($modeName)] ??= DB::table('procurement_modes')->insertGetId(['name' => $modeName, 'created_at' => now()]);

                $assetSourceId = DB::table('asset_sources')->insertGetId([
                    'item_id' => $itemId,
                    'description' => $row['description'] ?? null,
                    'unit_of_measurement' => $row['uom'] ?? 'Unit',
                    'acquisition_source_id' => $sourceId,
                    'procurement_mode_id' => $modeId,
                    'asset_cost' => floatval($row['unit_value'] ?? 0),
                    'quantity' => intval($row['quantity'] ?? 1),
                    'acceptance_date' => $row['acquisition_date'] ?? now()->toDateString(),
                    'created_at' => now(),
                ]);

                DB::table('asset_assignments')->insert([
                    'asset_source_id' => $assetSourceId,
                    'condition' => $row['remarks'] ?? 'Good Condition',
                    'office_school_type' => $row['office_type'] ?? 'School',
                    'location' => $row['location'] ?? 'Division Office',
                    'property_number' => $row['property_number'] ?? null,
                    'acquisition_cost' => floatval($row['total_value'] ?? 0),
                    'acquisition_date' => $row['acquisition_date'] ?? now()->toDateString(),
                    'created_at' => now(),
                ]);
            }

            // Process Buildings
            foreach ($allGroups['buildings'] as $row) {
                $classStr = trim($row['classification'] ?? 'Unclassified');
                $classId  = $caches['b_class'][strtolower($classStr)] ??= DB::table('building_classifications')->insertGetId(['name' => $classStr, 'created_at' => now()]);

                $typeStr  = trim($row['article'] ?? 'Building');
                $typeId   = $caches['b_types'][strtolower($typeStr)] ??= DB::table('building_types')->insertGetId(['building_classification_id' => $classId, 'name' => $typeStr, 'created_at' => now()]);

                $schoolId = $caches['schools'][$row['school_identifier'] ?? ''] ?? null;

                $specId = DB::table('building_specs')->insertGetId([
                    'building_type_id' => $typeId,
                    'description' => $row['description'] ?? null,
                    'storeys' => intval($row['storeys'] ?? 1),
                    'classrooms' => intval($row['classrooms'] ?? 0),
                    'created_at' => now()
                ]);

                DB::table('building_records')->insert([
                    'school_id' => $schoolId,
                    'building_spec_id' => $specId,
                    'office_type' => $row['office_type'] ?? null,
                    'address' => $row['address'] ?? null,
                    'location' => $row['location'] ?? null,
                    'occupancy_nature' => $row['occupancy_nature'] ?? null,
                    'date_constructed' => $row['date_constructed'] ?? null,
                    'acquisition_date' => $row['acquisition_date'] ?? null,
                    'property_number' => $row['property_number'] ?? null,
                    'acquisition_cost' => floatval($row['acquisition_cost'] ?? 0),
                    'estimated_useful_life' => intval($row['estimated_useful_life'] ?? 25),
                    'appraised_value' => floatval($row['appraised_value'] ?? 0),
                    'appraisal_date' => $row['appraisal_date'] ?? null,
                    'remarks' => $row['remarks'] ?? null,
                    'created_at' => now()
                ]);
            }

            DB::table('system_logs')->insert([
                'user' => $userName,
                'activity' => "PIF Import: Confirmed " . count($allGroups['buildings']) . " buildings and " . count($allGroups['assets']) . " assets.",
                'module' => 'Buildings',
                'action_type' => 'Import',
                'created_at' => now()
            ]);

            DB::commit();
            session()->forget('pif_import_data');
            return redirect('/inventory-setup')->with('success', 'Import completed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[Import] Confirm failure: " . $e->getMessage());
            return back()->withErrors(['xlsx_file' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    // ════════════════════════════════════════════════════════════
    // TEMPLATE DETECTION
    // ════════════════════════════════════════════════════════════

    private function detectTemplateType($sheet): string
    {
        $headerValues = [];
        for ($col = 1; $col <= 20; $col++) {
            $val = $sheet->getCellByColumnAndRow($col, 8)->getValue();
            if ($val !== null) {
                $headerValues[] = trim(str_replace("\n", ' ', (string)$val));
            }
        }

        // Try Gemini AI-based detection first
        $gemini = new \App\Services\GeminiService();
        $type = $gemini->detectTemplateType($headerValues);

        if ($type !== 'unknown') {
            return $type;
        }

        // Local keyword fallback logic
        $h = implode('|', array_map('strtolower', $headerValues));

        if (str_contains($h, 'storey') || str_contains($h, 'school address') || str_contains($h, 'date constructed')) {
            return 'buildings';
        }
        if (str_contains($h, 'article') || str_contains($h, 'item description') || str_contains($h, 'classification')) {
            return 'assets';
        }
        return 'unknown';
    }

    // ════════════════════════════════════════════════════════════
    // SHEET PARSERS
    // ════════════════════════════════════════════════════════════

    private function parseBuildingSheet($sheet): array
    {
        $maxRow = $sheet->getHighestRow();
        $rows   = [];

        for ($rowIdx = 11; $rowIdx <= $maxRow; $rowIdx++) {
            $v = [];
            for ($col = 1; $col <= 20; $col++) {
                $v[$col] = $sheet->getCellByColumnAndRow($col, $rowIdx)->getCalculatedValue();
            }
            if (!$this->rowHasData($v)) continue;
            if ($this->isSectionHeader($v)) continue;
            if (!$this->hasValidRegionDivision($v)) continue;
            if (empty(trim((string)($v[5] ?? '')))) continue;

            $sc = (string)($v[7] ?? '');
            $storeys = $classrooms = null;
            if (preg_match('/(\d+)\s*stor/i', $sc, $m))  $storeys    = (int)$m[1];
            if (preg_match('/(\d+)\s*class/i', $sc, $m)) $classrooms = (int)$m[1];

            $dateC = $this->parseDate($v[13] ?? null);
            $dateA = $this->parseDate($v[14] ?? null);
            if (empty($dateA) && !empty($dateC)) $dateA = $dateC;

            $addr = trim((string)($v[6] ?? ''));
            $loc  = trim((string)($v[12] ?? ''));
            if (empty($loc) && !empty($addr)) $loc = $addr;

            $rows[] = [
                'region'                 => trim((string)($v[1] ?? 'REGION IX')),
                'division'               => trim((string)($v[2] ?? 'Division of Zamboanga City')),
                'office_type'            => trim((string)($v[3] ?? '')),
                'school_identifier'      => trim((string)($v[4] ?? '')),
                'office_name'            => trim((string)($v[5] ?? '')),
                'address'                => $addr,
                'storeys'                => $storeys,
                'classrooms'             => $classrooms,
                'storeys_classrooms_raw' => trim($sc),
                'article'                => trim((string)($v[8] ?? '')),
                'description'            => trim((string)($v[9] ?? '')),
                'classification'         => trim((string)($v[10] ?? '')),
                'occupancy_nature'       => trim((string)($v[11] ?? '')),
                'location'               => $loc,
                'date_constructed'       => $dateC,
                'acquisition_date'       => $dateA,
                'property_number'        => trim((string)($v[15] ?? '')),
                'acquisition_cost'       => $this->parseDecimal($v[16] ?? null),
                'estimated_useful_life'  => !empty($v[20]) ? (int)$v[20] : 25, // Assuming col 20 for life or default 25
                'appraised_value'        => $this->parseDecimal($v[17] ?? null),
                'appraisal_date'         => $this->parseDate($v[18] ?? null),
                'remarks'                => trim((string)($v[19] ?? '')),
            ];
        }
        return $rows;
    }

    private function parseAssetSheet($sheet, string $sheetName): array
    {
        $maxRow = $sheet->getHighestRow();
        $rows   = [];

        for ($rowIdx = 11; $rowIdx <= $maxRow; $rowIdx++) {
            $v = [];
            for ($col = 1; $col <= 16; $col++) {
                $v[$col] = $sheet->getCellByColumnAndRow($col, $rowIdx)->getCalculatedValue();
            }
            if (!$this->rowHasData($v)) continue;
            if ($this->isSectionHeader($v)) continue;
            $article = trim((string)($v[6] ?? ''));
            if (empty($article)) continue;

            $rows[] = [
                'source_sheet'      => $sheetName,
                'region'            => trim((string)($v[1] ?? 'REGION IX')),
                'division'          => trim((string)($v[2] ?? 'Division of Zamboanga City')),
                'office_type'       => trim((string)($v[3] ?? '')),
                'school_identifier' => trim((string)($v[4] ?? '')),
                'office_name'       => trim((string)($v[5] ?? '')),
                'article'           => $article,
                'description'       => trim((string)($v[7] ?? '')),
                'classification'    => trim((string)($v[8] ?? '')),
                'occupancy_nature'  => trim((string)($v[9] ?? '')),
                'location'          => trim((string)($v[10] ?? '')),
                'acquisition_date'  => $this->parseDate($v[11] ?? null),
                'property_number'   => trim((string)($v[12] ?? '')),
                'acquisition_cost'  => $this->parseDecimal($v[13] ?? null),
            ];
        }
        return $rows;
    }

    // ════════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════════

    private function rowHasData(array $v): bool
    {
        foreach ($v as $val) {
            if ($val !== null && trim((string)$val) !== '') return true;
        }
        return false;
    }

    private function isSectionHeader(array $v): bool
    {
        return !empty(trim((string)($v[1] ?? '')))
            && empty(trim((string)($v[2] ?? '')))
            && empty(trim((string)($v[3] ?? '')))
            && empty(trim((string)($v[5] ?? '')));
    }

    private function hasValidRegionDivision(array $v): bool
    {
        $r = strtolower(trim((string)($v[1] ?? '')));
        $d = strtolower(trim((string)($v[2] ?? '')));
        return !empty($r) && str_contains($r, 'region')
            && !empty($d) && str_contains($d, 'division');
    }

    private function parseDate($value): ?string
    {
        if ($value === null || trim((string)$value) === '') return null;
        $val = trim((string)$value);
        if (preg_match('/^\d{4}$/', $val)) return $val . '-01-01';
        if (is_numeric($val) && (int)$val > 30000) {
            try { return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$val)->format('Y-m-d'); }
            catch (\Exception $e) { return null; }
        }
        try { return \Carbon\Carbon::parse($val)->toDateString(); }
        catch (\Exception $e) { return null; }
    }

    private function parseDecimal($value): ?float
    {
        if ($value === null || trim((string)$value) === '') return null;
        $c = str_replace(',', '', trim((string)$value));
        return is_numeric($c) ? (float)$c : null;
    }
}
