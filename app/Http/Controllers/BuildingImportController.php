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
     * Parse the uploaded .xlsx, detect template type per sheet, extract rows, store in session, return preview.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'xlsx_file' => 'required|file|mimes:xlsx,xls|max:51200',
        ]);

        $file = $request->file('xlsx_file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Exception $e) {
            return back()->withErrors(['xlsx_file' => 'Unable to read the uploaded file: ' . $e->getMessage()]);
        }

        $allGroups = []; // ['buildings' => [...], 'assets' => [...]]

        // Scan ALL sheets (skip utility sheets)
        $skipSheets = ['instruction', 'dropdown', 'sheet1'];
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if (in_array(strtolower(trim($sheetName)), $skipSheets)) {
                continue;
            }

            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) continue;

            $templateType = $this->detectTemplateType($sheet);

            if ($templateType === 'buildings') {
                $rows = $this->parseBuildingSheet($sheet);
                if (!empty($rows)) {
                    if (!isset($allGroups['buildings'])) {
                        $allGroups['buildings'] = [];
                    }
                    $allGroups['buildings'] = array_merge($allGroups['buildings'], $rows);
                }
            } elseif ($templateType === 'assets') {
                $rows = $this->parseAssetSheet($sheet, $sheetName);
                if (!empty($rows)) {
                    if (!isset($allGroups['assets'])) {
                        $allGroups['assets'] = [];
                    }
                    $allGroups['assets'] = array_merge($allGroups['assets'], $rows);
                }
            }
            // Unknown templates are silently skipped
        }

        // If file has only 1 sheet (no utility sheets), process it directly
        if (empty($allGroups)) {
            $sheet = $spreadsheet->getActiveSheet();
            $templateType = $this->detectTemplateType($sheet);

            if ($templateType === 'buildings') {
                $rows = $this->parseBuildingSheet($sheet);
                if (!empty($rows)) $allGroups['buildings'] = $rows;
            } elseif ($templateType === 'assets') {
                $rows = $this->parseAssetSheet($sheet, $sheet->getTitle());
                if (!empty($rows)) $allGroups['assets'] = $rows;
            }
        }

        if (empty($allGroups)) {
            return back()->withErrors(['xlsx_file' => 'No valid data rows found in the uploaded file. Please ensure the file follows the DepEd PIF template format.']);
        }

        // Store in session for the confirm step
        session(['pif_import_data' => $allGroups]);

        return view('import-buildings', [
            'allGroups'    => $allGroups,
            'totalBuildings' => count($allGroups['buildings'] ?? []),
            'totalAssets'    => count($allGroups['assets'] ?? []),
        ]);
    }

    /**
     * Confirm and insert all parsed rows into their respective tables.
     */
    public function confirm(Request $request)
    {
        $allGroups = session('pif_import_data');

        if (!$allGroups || (empty($allGroups['buildings']) && empty($allGroups['assets']))) {
            return redirect()->route('buildings.import')
                ->withErrors(['xlsx_file' => 'No import data found. Please upload and preview your file first.']);
        }

        $userName = auth()->user() ? auth()->user()->email : 'System';
        $totalBuildings = 0;
        $totalAssets = 0;

        DB::beginTransaction();
        try {
            // ── Insert Buildings ──
            if (!empty($allGroups['buildings'])) {
                foreach ($allGroups['buildings'] as $row) {
                    $schoolId = null;
                    if (!empty($row['school_identifier'])) {
                        $school = DB::table('schools')
                            ->where('school_id', $row['school_identifier'])
                            ->first();
                        if ($school) $schoolId = $school->id;
                    }

                    DB::table('buildings')->insert([
                        'school_id'         => $schoolId,
                        'region'            => $row['region'] ?: 'REGION IX',
                        'division'          => $row['division'] ?: 'Division of Zamboanga City',
                        'office_type'       => $row['office_type'] ?: null,
                        'school_identifier' => $row['school_identifier'] ?: null,
                        'office_name'       => $row['office_name'] ?: null,
                        'address'           => $row['address'] ?: null,
                        'storeys'           => $row['storeys'],
                        'classrooms'        => $row['classrooms'],
                        'article'           => $row['article'] ?: null,
                        'description'       => $row['description'] ?: null,
                        'classification'    => $row['classification'] ?: null,
                        'occupancy_nature'  => $row['occupancy_nature'] ?: null,
                        'location'          => $row['location'] ?: null,
                        'date_constructed'  => $row['date_constructed'],
                        'acquisition_date'  => $row['acquisition_date'],
                        'property_number'   => $row['property_number'] ?: null,
                        'acquisition_cost'  => $row['acquisition_cost'],
                        'appraised_value'   => $row['appraised_value'],
                        'appraisal_date'    => $row['appraisal_date'],
                        'remarks'           => $row['remarks'] ?: null,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                    $totalBuildings++;
                }
            }

            // ── Insert Assets (PPE PIF / Semi-PPE PIF) ──
            if (!empty($allGroups['assets'])) {
                // Ensure "PIF Import" acquisition source exists
                $pifSource = DB::table('acquisition_sources')
                    ->where('name', 'PIF Import')
                    ->first();
                if (!$pifSource) {
                    $pifSourceId = DB::table('acquisition_sources')->insertGetId([
                        'name'        => 'PIF Import',
                        'source_type' => 'Internal',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                } else {
                    $pifSourceId = $pifSource->id;
                }

                foreach ($allGroups['assets'] as $row) {
                    // 1. Resolve/create Classification
                    $classificationName = $row['classification'] ?? '';
                    $categoryId = null;
                    if (!empty($classificationName)) {
                        // Check classifications table first
                        $classification = DB::table('classifications')
                            ->whereRaw('LOWER(name) = ?', [strtolower($classificationName)])
                            ->first();
                        if (!$classification) {
                            $classificationId = DB::table('classifications')->insertGetId([
                                'name'       => $classificationName,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        } else {
                            $classificationId = $classification->id;
                        }

                        // Find or create a matching category linked to this classification (case-insensitive)
                        $category = DB::table('categories')
                            ->where('classification_id', $classificationId)
                            ->whereRaw('LOWER(name) = ?', [strtolower($classificationName)])
                            ->first();
                        if (!$category) {
                            // Also check if ANY category with this classification_id exists
                            $category = DB::table('categories')
                                ->where('classification_id', $classificationId)
                                ->first();
                        }
                        if ($category) {
                            $categoryId = $category->id;
                        } else {
                            $categoryId = DB::table('categories')->insertGetId([
                                'name'              => $classificationName,
                                'classification_id' => $classificationId,
                                'created_at'        => now(),
                                'updated_at'        => now(),
                            ]);
                        }
                    }

                    // 2. Resolve/create Item (Article/Item)
                    $itemName = $row['article'] ?? '';
                    $itemId = null;
                    if (!empty($itemName)) {
                        $existingItem = DB::table('items')
                            ->whereRaw('LOWER(name) = ?', [strtolower($itemName)])
                            ->first();
                        if ($existingItem) {
                            $itemId = $existingItem->id;
                        } else {
                            $itemId = DB::table('items')->insertGetId([
                                'name'        => $itemName,
                                'category_id' => $categoryId,
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);
                        }
                    } else {
                        // Fallback: create a generic item
                        $genericItem = DB::table('items')->where('name', 'Unspecified Item')->first();
                        if ($genericItem) {
                            $itemId = $genericItem->id;
                        } else {
                            $itemId = DB::table('items')->insertGetId([
                                'name'       => 'Unspecified Item',
                                'category_id' => $categoryId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    // 3. Insert asset_sources
                    $acquisitionCost = $row['acquisition_cost'] ?? 0;
                    $acquisitionDate = $row['acquisition_date'] ?? now()->toDateString();

                    $assetSourceId = DB::table('asset_sources')->insertGetId([
                        'item_id'              => $itemId,
                        'description'          => $row['description'] ?: null,
                        'acquisition_source_id' => $pifSourceId,
                        'mode_of_acquisition'  => 'PIF Import',
                        'asset_cost'           => $acquisitionCost ?? 0,
                        'quantity'             => 1,
                        'acceptance_date'      => $acquisitionDate ?: now()->toDateString(),
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ]);

                    // 4. Insert asset_distributions
                    $propertyNumber = $row['property_number'] ?? '';
                    // If property_number is empty, generate a unique placeholder
                    if (empty($propertyNumber)) {
                        $propertyNumber = 'PIF-' . now()->format('Ymd') . '-' . str_pad($totalAssets + 1, 6, '0', STR_PAD_LEFT);
                    }

                    DB::table('asset_distributions')->insert([
                        'asset_source_id'    => $assetSourceId,
                        'region'             => $row['region'] ?: 'Region IX',
                        'division'           => $row['division'] ?: 'Division of Zamboanga City',
                        'office_school_type' => $row['office_type'] ?: '',
                        'school_id'          => $row['school_identifier'] ?: null,
                        'office_school_name' => $row['office_name'] ?: '',
                        'nature_of_occupancy' => $row['occupancy_nature'] ?: '',
                        'location'           => $row['location'] ?: null,
                        'property_number'    => $propertyNumber,
                        'acquisition_cost'   => $acquisitionCost ?? 0,
                        'acquisition_date'   => $acquisitionDate ?: now()->toDateString(),
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);

                    $totalAssets++;
                }
            }

            DB::commit();

            // Log the import
            $parts = [];
            if ($totalBuildings > 0) $parts[] = "{$totalBuildings} building(s)";
            if ($totalAssets > 0) $parts[] = "{$totalAssets} asset(s)";

            DB::table('system_logs')->insert([
                'user'        => $userName,
                'activity'    => 'PIF Import: ' . implode(' and ', $parts) . ' registered from Excel upload',
                'module'      => 'Import',
                'action_type' => 'Create',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            session()->forget('pif_import_data');

            $successMsg = 'Import complete! ';
            if ($totalBuildings > 0) $successMsg .= "{$totalBuildings} building(s)";
            if ($totalBuildings > 0 && $totalAssets > 0) $successMsg .= ' and ';
            if ($totalAssets > 0) $successMsg .= "{$totalAssets} asset(s)";
            $successMsg .= ' registered successfully.';

            return redirect()->route('buildings.import')->with('success', $successMsg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('buildings.import')
                ->withErrors(['xlsx_file' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    // ════════════════════════════════════════════════════════════
    // TEMPLATE DETECTION
    // ════════════════════════════════════════════════════════════

    /**
     * Detect template type by scanning row 8 headers.
     * Returns: 'buildings', 'assets', or 'unknown'
     */
    private function detectTemplateType($sheet): string
    {
        $headerValues = [];
        for ($col = 1; $col <= 20; $col++) {
            $val = $sheet->getCellByColumnAndRow($col, 8)->getValue();
            if ($val !== null) {
                $headerValues[] = strtolower(trim(str_replace("\n", ' ', (string)$val)));
            }
        }

        $headerString = implode('|', $headerValues);

        // Buildings template has "storey" or "school address" or "date constructed"
        if (str_contains($headerString, 'storey') ||
            str_contains($headerString, 'school address') ||
            str_contains($headerString, 'date constructed')) {
            return 'buildings';
        }

        // PPE PIF / Semi-PPE PIF templates have "article/item" without "storey"
        if (str_contains($headerString, 'article') ||
            str_contains($headerString, 'item description') ||
            str_contains($headerString, 'classification')) {
            return 'assets';
        }

        return 'unknown';
    }

    // ════════════════════════════════════════════════════════════
    // BUILDING SHEET PARSER
    // ════════════════════════════════════════════════════════════

    private function parseBuildingSheet($sheet): array
    {
        $maxRow = $sheet->getHighestRow();
        $parsedRows = [];

        for ($rowIdx = 11; $rowIdx <= $maxRow; $rowIdx++) {
            // Only read within defined columns (1-19) to exclude signatories/notes outside the table
            $rawValues = [];
            for ($col = 1; $col <= 19; $col++) {
                $cell = $sheet->getCellByColumnAndRow($col, $rowIdx);
                $val = $cell->getCalculatedValue();
                $rawValues[$col] = $val;
            }

            if (!$this->rowHasData($rawValues)) continue;
            if ($this->isSectionHeader($rawValues)) continue;
            if (!$this->hasValidRegionDivision($rawValues)) continue;

            // Skip rows without Office/School Name — required field for buildings
            $officeName = trim((string)($rawValues[5] ?? ''));
            if (empty($officeName)) continue;

            // Parse storeys/classrooms
            $storeysClassrooms = (string)($rawValues[7] ?? '');
            $storeys = null;
            $classrooms = null;
            if (!empty($storeysClassrooms)) {
                if (preg_match('/(\d+)\s*stor/i', $storeysClassrooms, $m)) $storeys = (int)$m[1];
                if (preg_match('/(\d+)\s*class/i', $storeysClassrooms, $m)) $classrooms = (int)$m[1];
            }

            $dateConstructed = $this->parseDate($rawValues[13] ?? null);
            $acquisitionDate = $this->parseDate($rawValues[14] ?? null);
            if (empty($acquisitionDate) && !empty($dateConstructed)) $acquisitionDate = $dateConstructed;

            $address = trim((string)($rawValues[6] ?? ''));
            $location = trim((string)($rawValues[12] ?? ''));
            if (empty($location) && !empty($address)) $location = $address;

            $parsedRows[] = [
                'region'                 => trim((string)($rawValues[1] ?? 'REGION IX')),
                'division'               => trim((string)($rawValues[2] ?? 'Division of Zamboanga City')),
                'office_type'            => trim((string)($rawValues[3] ?? '')),
                'school_identifier'      => trim((string)($rawValues[4] ?? '')),
                'office_name'            => trim((string)($rawValues[5] ?? '')),
                'address'                => $address,
                'storeys'                => $storeys,
                'classrooms'             => $classrooms,
                'storeys_classrooms_raw' => trim($storeysClassrooms),
                'article'                => trim((string)($rawValues[8] ?? '')),
                'description'            => trim((string)($rawValues[9] ?? '')),
                'classification'         => trim((string)($rawValues[10] ?? '')),
                'occupancy_nature'       => trim((string)($rawValues[11] ?? '')),
                'location'               => $location,
                'date_constructed'       => $dateConstructed,
                'acquisition_date'       => $acquisitionDate,
                'property_number'        => trim((string)($rawValues[15] ?? '')),
                'acquisition_cost'       => $this->parseDecimal($rawValues[16] ?? null),
                'appraised_value'        => $this->parseDecimal($rawValues[17] ?? null),
                'appraisal_date'         => $this->parseDate($rawValues[18] ?? null),
                'remarks'                => trim((string)($rawValues[19] ?? '')),
            ];
        }

        return $parsedRows;
    }

    // ════════════════════════════════════════════════════════════
    // ASSET SHEET PARSER (PPE PIF / Semi-PPE PIF)
    // ════════════════════════════════════════════════════════════

    /**
     * PPE PIF / Semi-PPE PIF column layout (from row 8):
     * PPE PIF: items with acquisition cost >= 50,000
     * Semi-PPE PIF: items with acquisition cost < 50,000
     *
     * C1:Region  C2:Division  C3:Office/School Type  C4:School ID  C5:Office/School Name
     * C6:Article/Item  C7:Item Description  C8:Classification  C9:Nature of Occupancy
     * C10:Location  C11:Acquisition Date  C12:Property No.  C13:Acquisition Cost
     * C14:Market/Appraisal (SKIPPED)  C15:Date of Appraisal (SKIPPED)  C16:Remarks (SKIPPED)
     */
    private function parseAssetSheet($sheet, string $sheetName): array
    {
        $maxRow = $sheet->getHighestRow();
        $parsedRows = [];

        for ($rowIdx = 11; $rowIdx <= $maxRow; $rowIdx++) {
            // Only read within defined columns (1-16) to exclude signatories/notes outside the table
            $rawValues = [];
            for ($col = 1; $col <= 16; $col++) {
                $cell = $sheet->getCellByColumnAndRow($col, $rowIdx);
                $val = $cell->getCalculatedValue();
                $rawValues[$col] = $val;
            }

            if (!$this->rowHasData($rawValues)) continue;
            if ($this->isSectionHeader($rawValues)) continue;

            // Skip rows without Article/Item — the only required filter for assets
            $article = trim((string)($rawValues[6] ?? ''));
            if (empty($article)) continue;

            $acquisitionDate = $this->parseDate($rawValues[11] ?? null);

            $parsedRows[] = [
                'source_sheet'      => $sheetName,
                'region'            => trim((string)($rawValues[1] ?? 'REGION IX')),
                'division'          => trim((string)($rawValues[2] ?? 'Division of Zamboanga City')),
                'office_type'       => trim((string)($rawValues[3] ?? '')),
                'school_identifier' => trim((string)($rawValues[4] ?? '')),
                'office_name'       => trim((string)($rawValues[5] ?? '')),
                'article'           => trim((string)($rawValues[6] ?? '')),
                'description'       => trim((string)($rawValues[7] ?? '')),
                'classification'    => trim((string)($rawValues[8] ?? '')),
                'occupancy_nature'  => trim((string)($rawValues[9] ?? '')),
                'location'          => trim((string)($rawValues[10] ?? '')),
                'acquisition_date'  => $acquisitionDate,
                'property_number'   => trim((string)($rawValues[12] ?? '')),
                'acquisition_cost'  => $this->parseDecimal($rawValues[13] ?? null),
            ];
        }

        return $parsedRows;
    }

    // ════════════════════════════════════════════════════════════
    // SHARED HELPERS
    // ════════════════════════════════════════════════════════════

    private function rowHasData(array $rawValues): bool
    {
        foreach ($rawValues as $v) {
            if ($v !== null && trim((string)$v) !== '') return true;
        }
        return false;
    }

    private function isSectionHeader(array $rawValues): bool
    {
        $c1 = trim((string)($rawValues[1] ?? ''));
        $c2 = trim((string)($rawValues[2] ?? ''));
        $c3 = trim((string)($rawValues[3] ?? ''));
        $c5 = trim((string)($rawValues[5] ?? ''));
        return !empty($c1) && empty($c2) && empty($c3) && empty($c5);
    }

    /**
     * Validate that a row has the expected constant Region and Division values.
     * Filters out signatory rows, totals, and other non-data rows.
     */
    private function hasValidRegionDivision(array $rawValues): bool
    {
        $region = strtolower(trim((string)($rawValues[1] ?? '')));
        $division = strtolower(trim((string)($rawValues[2] ?? '')));

        // Region must contain "region" (e.g. "REGION IX")
        if (empty($region) || !str_contains($region, 'region')) return false;

        // Division must contain "division" (e.g. "Division of Zamboanga City")
        if (empty($division) || !str_contains($division, 'division')) return false;

        return true;
    }

    private function parseDate($value): ?string
    {
        if ($value === null || trim((string)$value) === '') return null;

        $val = trim((string)$value);

        // Pure 4-digit year
        if (preg_match('/^\d{4}$/', $val)) return $val . '-01-01';

        // Excel serial date number
        if (is_numeric($val) && (int)$val > 30000) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$val);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Standard date string
        try {
            return \Carbon\Carbon::parse($val)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseDecimal($value): ?float
    {
        if ($value === null || trim((string)$value) === '') return null;
        $cleaned = str_replace(',', '', trim((string)$value));
        return is_numeric($cleaned) ? (float)$cleaned : null;
    }
}
