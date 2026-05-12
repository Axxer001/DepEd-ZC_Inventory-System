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
        set_time_limit(300);
        $request->validate([
            'xlsx_file' => 'required|file|mimes:xlsx,xls|max:51200',
        ]);

        $file = $request->file('xlsx_file');
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Exception $e) {
            return back()->withErrors(['xlsx_file' => 'Unable to read the uploaded file: ' . $e->getMessage()]);
        }

        $allGroups  = [];
        $skipSheets = ['instruction', 'dropdown', 'sheet1'];

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if (in_array(strtolower(trim($sheetName)), $skipSheets)) continue;
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) continue;

            $type = $this->detectTemplateType($sheet);
            if ($type === 'buildings') {
                $rows = $this->parseBuildingSheet($sheet);
                if (!empty($rows)) {
                    $allGroups['buildings'] = array_merge($allGroups['buildings'] ?? [], $rows);
                }
            } elseif ($type === 'assets') {
                $rows = $this->parseAssetSheet($sheet, $sheetName);
                if (!empty($rows)) {
                    $allGroups['assets'] = array_merge($allGroups['assets'] ?? [], $rows);
                }
            }
        }

        // Fallback: single-sheet file
        if (empty($allGroups)) {
            $sheet = $spreadsheet->getActiveSheet();
            $type  = $this->detectTemplateType($sheet);
            if ($type === 'buildings') {
                $rows = $this->parseBuildingSheet($sheet);
                if (!empty($rows)) $allGroups['buildings'] = $rows;
            } elseif ($type === 'assets') {
                $rows = $this->parseAssetSheet($sheet, $sheet->getTitle());
                if (!empty($rows)) $allGroups['assets'] = $rows;
            }
        }

        if (empty($allGroups)) {
            return back()->withErrors(['xlsx_file' => 'No valid data rows found. Please ensure the file follows the DepEd PIF template format.']);
        }

        $duplicates = [];
        $seenInFile = [];
        $assetProps = [];

        $dbDuplicates = [];
        $fileDuplicates = [];

        foreach ($allGroups['assets'] ?? [] as $i => $row) {
            $pn = trim($row['property_number'] ?? '');
            if ($pn) {
                $assetProps[] = $pn;
                if (isset($seenInFile[$pn])) {
                    $fileDuplicates[] = ['index' => $i, 'property_number' => $pn, 'reason' => 'Repeated in file', 'article' => $row['article'] ?? ''];
                }
                $seenInFile[$pn] = true;
            }
        }

        if (!empty($assetProps)) {
            $existingInDb = array_flip(DB::table('asset_distributions')
                ->whereIn('property_number', $assetProps)
                ->pluck('property_number')
                ->all());
            
            foreach ($allGroups['assets'] ?? [] as $i => $row) {
                $pn = trim($row['property_number'] ?? '');
                if ($pn && isset($existingInDb[$pn])) {
                    $alreadyFlaggedFile = false;
                    foreach ($fileDuplicates as $d) {
                        if ($d['index'] === $i) { $alreadyFlaggedFile = true; break; }
                    }
                    if (!$alreadyFlaggedFile) {
                        $dbDuplicates[] = ['index' => $i, 'property_number' => $pn, 'reason' => 'Exists in database', 'article' => $row['article'] ?? ''];
                    }
                }
            }
        }

        // Also check building duplicates
        $buildingProps = [];
        $seenInFileB = [];
        foreach ($allGroups['buildings'] ?? [] as $i => $row) {
            $pn = trim($row['property_number'] ?? '');
            if ($pn) {
                $buildingProps[] = $pn;
                if (isset($seenInFileB[$pn])) {
                    $fileDuplicates[] = ['index' => $i, 'property_number' => $pn, 'reason' => 'Repeated in file', 'article' => $row['article'] ?? '', 'type' => 'building'];
                }
                $seenInFileB[$pn] = true;
            }
        }

        if (!empty($buildingProps)) {
            $existingInDbB = array_flip(DB::table('building_records')
                ->whereIn('property_number', $buildingProps)
                ->pluck('property_number')
                ->all());
            
            foreach ($allGroups['buildings'] ?? [] as $i => $row) {
                $pn = trim($row['property_number'] ?? '');
                if ($pn && isset($existingInDbB[$pn])) {
                    $alreadyFlaggedFile = false;
                    foreach ($fileDuplicates as $d) {
                        if ($d['index'] === $i && ($d['type'] ?? '') === 'building') { $alreadyFlaggedFile = true; break; }
                    }
                    if (!$alreadyFlaggedFile) {
                        $dbDuplicates[] = ['index' => $i, 'property_number' => $pn, 'reason' => 'Exists in database', 'article' => $row['article'] ?? '', 'type' => 'building'];
                    }
                }
            }
        }

        $duplicates = array_merge($dbDuplicates, $fileDuplicates);

        session(['pif_import_data' => $allGroups]);

        return view('import-buildings', [
            'allGroups'      => $allGroups,
            'totalBuildings' => count($allGroups['buildings'] ?? []),
            'totalAssets'    => count($allGroups['assets'] ?? []),
            'duplicates'     => $duplicates,
            'dbDuplicates'   => count($dbDuplicates),
            'fileDuplicates' => count($fileDuplicates)
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
            return redirect()->route('buildings.import')
                ->withErrors(['xlsx_file' => 'No import data found. Please upload and preview your file first.']);
        }

        $userName = auth()->user() ? auth()->user()->email : 'System';
        $duplicateAction = $request->input('duplicate_action', 'keep');

        // Extract all property numbers
        $buildingPropsToOverwrite = [];
        $assetPropsToOverwrite = [];

        // Apply duplicate action if remove or skip_existing
        if (in_array($duplicateAction, ['remove', 'skip_existing'])) {
            $seenAssets = [];
            $filteredAssets = [];
            
            $allProps = [];
            foreach ($allGroups['assets'] ?? [] as $row) {
                if (!empty($row['property_number'])) $allProps[] = trim($row['property_number']);
            }
            $inDb = !empty($allProps) ? array_flip(DB::table('asset_distributions')->whereIn('property_number', $allProps)->pluck('property_number')->all()) : [];

            foreach ($allGroups['assets'] ?? [] as $row) {
                $pn = trim($row['property_number'] ?? '');
                if ($pn) {
                    if (isset($inDb[$pn]) || isset($seenAssets[$pn])) continue; // Skip duplicate
                    $seenAssets[$pn] = true;
                }
                $filteredAssets[] = $row;
            }
            $allGroups['assets'] = $filteredAssets;

            $seenBuildings = [];
            $filteredBuildings = [];
            $allBProps = [];
            foreach ($allGroups['buildings'] ?? [] as $row) {
                if (!empty($row['property_number'])) $allBProps[] = trim($row['property_number']);
            }
            $inDbB = !empty($allBProps) ? array_flip(DB::table('building_records')->whereIn('property_number', $allBProps)->pluck('property_number')->all()) : [];

            foreach ($allGroups['buildings'] ?? [] as $row) {
                $pn = trim($row['property_number'] ?? '');
                if ($pn) {
                    if (isset($inDbB[$pn]) || isset($seenBuildings[$pn])) continue; // Skip duplicate
                    $seenBuildings[$pn] = true;
                }
                $filteredBuildings[] = $row;
            }
            $allGroups['buildings'] = $filteredBuildings;
        } elseif ($duplicateAction === 'overwrite') {
            // Collect property numbers to overwrite
            foreach ($allGroups['buildings'] ?? [] as $row) {
                if (!empty($row['property_number'])) $buildingPropsToOverwrite[] = trim($row['property_number']);
            }
            foreach ($allGroups['assets'] ?? [] as $row) {
                if (!empty($row['property_number'])) $assetPropsToOverwrite[] = trim($row['property_number']);
            }

            // Let's filter out file-level duplicates so we only insert them once during overwrite
            $seenAssets = [];
            $filteredAssets = [];
            foreach ($allGroups['assets'] ?? [] as $row) {
                $pn = trim($row['property_number'] ?? '');
                if ($pn) {
                    if (isset($seenAssets[$pn])) continue;
                    $seenAssets[$pn] = true;
                }
                $filteredAssets[] = $row;
            }
            $allGroups['assets'] = $filteredAssets;

            $seenBuildings = [];
            $filteredBuildings = [];
            foreach ($allGroups['buildings'] ?? [] as $row) {
                $pn = trim($row['property_number'] ?? '');
                if ($pn) {
                    if (isset($seenBuildings[$pn])) continue;
                    $seenBuildings[$pn] = true;
                }
                $filteredBuildings[] = $row;
            }
            $allGroups['buildings'] = $filteredBuildings;
        }

        // ── Pre-collect unique names for batch lookups ──
        $uniqueClassLower = [];
        $uniqueItemLower  = [];
        $buildingProps    = [];
        $assetProps       = [];

        foreach ($allGroups['buildings'] ?? [] as $row) {
            if (!empty($row['property_number'])) $buildingProps[] = $row['property_number'];
        }

        foreach ($allGroups['assets'] ?? [] as $row) {
            $c = trim($row['classification'] ?? '');
            $uniqueClassLower[] = strtolower(empty($c) ? 'Unclassified' : $c);
            $i = trim($row['article'] ?? '');
            if (!empty($i)) $uniqueItemLower[] = strtolower($i);
            if (!empty($row['property_number'])) $assetProps[] = trim($row['property_number']);
        }

        $uniqueClassLower = array_values(array_unique($uniqueClassLower));
        $uniqueItemLower  = array_values(array_unique($uniqueItemLower));

        // ── Batch DB lookups ──
        $classMap = [];
        if (!empty($uniqueClassLower)) {
            $ph   = implode(',', array_fill(0, count($uniqueClassLower), '?'));
            foreach (DB::select("SELECT id, name FROM classifications WHERE LOWER(name) IN ({$ph})", $uniqueClassLower) as $r) {
                $classMap[strtolower($r->name)] = $r;
            }
        }

        $catMap = [];
        $uniqueCatLower = ['uncategorized'];
        $ph   = implode(',', array_fill(0, count($uniqueCatLower), '?'));
        foreach (DB::select("SELECT id, name, classification_id FROM categories WHERE LOWER(name) IN ({$ph})", $uniqueCatLower) as $r) {
            $catMap[strtolower($r->name)][] = $r;
        }

        $itemMap = [];
        if (!empty($uniqueItemLower)) {
            $ph   = implode(',', array_fill(0, count($uniqueItemLower), '?'));
            foreach (DB::select("SELECT id, name, category_id FROM items WHERE LOWER(name) IN ({$ph})", $uniqueItemLower) as $r) {
                $itemMap[strtolower($r->name)] = $r;
            }
        }

        $existingBuildingProps = !empty($buildingProps)
            ? array_flip(DB::table('building_records')->whereIn('property_number', $buildingProps)->pluck('property_number')->all())
            : [];

        $existingAssetProps = !empty($assetProps)
            ? array_flip(DB::table('asset_distributions')->whereIn('property_number', $assetProps)->pluck('property_number')->all())
            : [];

        $totalBuildings = 0;
        $totalAssets    = 0;

        DB::beginTransaction();
        try {
            // ── Overwrite: Delete existing records ──
            if ($duplicateAction === 'overwrite') {
                if (!empty($assetPropsToOverwrite)) {
                    $existingAssets = DB::table('asset_distributions')
                        ->whereIn('property_number', $assetPropsToOverwrite)
                        ->select('id', 'asset_source_id')
                        ->get();
                    
                    if ($existingAssets->isNotEmpty()) {
                        $sourceIds = $existingAssets->pluck('asset_source_id')->unique()->filter()->all();
                        DB::table('asset_distributions')->whereIn('property_number', $assetPropsToOverwrite)->delete();
                        if (!empty($sourceIds)) {
                            // Check if source is still used by other distributions, if not delete it
                            $usedSourceIds = DB::table('asset_distributions')->whereIn('asset_source_id', $sourceIds)->pluck('asset_source_id')->unique()->all();
                            $orphanedSourceIds = array_diff($sourceIds, $usedSourceIds);
                            if (!empty($orphanedSourceIds)) {
                                DB::table('asset_sources')->whereIn('id', $orphanedSourceIds)->delete();
                            }
                        }
                    }
                }

                if (!empty($buildingPropsToOverwrite)) {
                    DB::table('building_records')->whereIn('property_number', $buildingPropsToOverwrite)->delete();
                }

                // Since we deleted them, we don't consider them 'existing' anymore for skipping
                $existingBuildingProps = [];
                $existingAssetProps = [];
            }

            // ── Insert Buildings ──
            foreach ($allGroups['buildings'] ?? [] as $row) {
                $propNo = $row['property_number'] ?? null;
                if ($propNo && isset($existingBuildingProps[$propNo])) continue;

                $schoolId = null;
                if (!empty($row['school_identifier'])) {
                    $school = DB::table('schools')->where('school_id', $row['school_identifier'])->first();
                    if ($school) $schoolId = $school->id;
                }

                // Taxonomy lookup
                $classStr = trim($row['classification'] ?: 'Unclassified');
                $classRec = DB::table('building_classifications')->where('name', $classStr)->first();
                $classId = $classRec ? $classRec->id : DB::table('building_classifications')->insertGetId(['name' => $classStr, 'created_at' => now(), 'updated_at' => now()]);

                $typeStr = trim($row['article'] ?: 'Building');
                $typeRec = DB::table('building_types')->where('building_classification_id', $classId)->where('name', $typeStr)->first();
                $typeId = $typeRec ? $typeRec->id : DB::table('building_types')->insertGetId(['building_classification_id' => $classId, 'name' => $typeStr, 'created_at' => now(), 'updated_at' => now()]);

                $descStr = trim($row['description'] ?: '');
                $st = $row['storeys'];
                $cr = $row['classrooms'];
                
                $specQuery = DB::table('building_specs')->where('building_type_id', $typeId)->where('description', $descStr);
                if ($st !== null) $specQuery->where('storeys', $st); else $specQuery->whereNull('storeys');
                if ($cr !== null) $specQuery->where('classrooms', $cr); else $specQuery->whereNull('classrooms');
                $specRec = $specQuery->first();
                
                $specId = $specRec ? $specRec->id : DB::table('building_specs')->insertGetId([
                    'building_type_id' => $typeId,
                    'description' => $descStr ?: null,
                    'storeys' => $st,
                    'classrooms' => $cr,
                    'created_at' => now(), 'updated_at' => now()
                ]);

                DB::table('building_records')->insert([
                    'school_id'         => $schoolId,
                    'building_spec_id'  => $specId,
                    'region'            => $row['region'] ?: 'REGION IX',
                    'division'          => $row['division'] ?: 'Division of Zamboanga City',
                    'office_type'       => $row['office_type'] ?: null,
                    'address'           => $row['address'] ?: null,
                    'location'          => $row['location'] ?: null,
                    'occupancy_nature'  => $row['occupancy_nature'] ?: null,
                    'date_constructed'  => $row['date_constructed'],
                    'acquisition_date'  => $row['acquisition_date'],
                    'property_number'   => $propNo,
                    'acquisition_cost'  => $row['acquisition_cost'],
                    'estimated_useful_life' => $row['estimated_useful_life'] ?? 25,
                    'appraised_value'   => $row['appraised_value'],
                    'appraisal_date'    => $row['appraisal_date'],
                    'remarks'           => $row['remarks'] ?: null,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
                
                if ($propNo) $existingBuildingProps[$propNo] = true;
                $totalBuildings++;
            }

            // ── Insert Assets (PPE PIF / Semi-PPE PIF) ──
            if (!empty($allGroups['assets'])) {
                $pifSource   = DB::table('acquisition_sources')->where('name', 'PIF Import')->first();
                $pifSourceId = $pifSource
                    ? $pifSource->id
                    : DB::table('acquisition_sources')->insertGetId([
                        'name'        => 'PIF Import',
                        'source_type' => 'Internal',
                        'created_at'  => now(), 'updated_at' => now(),
                    ]);

                foreach ($allGroups['assets'] as $row) {
                    $propNo = trim($row['property_number'] ?? '');
                    if (!empty($propNo) && isset($existingAssetProps[$propNo])) continue;

                    // Classification
                    $cName      = trim($row['classification'] ?? '');
                    if (empty($cName)) $cName = 'Unclassified';
                    $cNameLower = strtolower($cName);

                    if (!isset($classMap[$cNameLower])) {
                        $cId = DB::table('classifications')->insertGetId([
                            'name' => $cName, 'created_at' => now(), 'updated_at' => now(),
                        ]);
                        $classMap[$cNameLower] = (object)['id' => $cId, 'name' => $cName];
                    }
                    $classId = $classMap[$cNameLower]->id;

                    // Category
                    $catName = 'Uncategorized';
                    $catNameLower = 'uncategorized';
                    $catId    = null;
                    $foundCat = false;
                    foreach ($catMap[$catNameLower] ?? [] as $c) {
                        if ((int)$c->classification_id === (int)$classId) {
                            $catId = $c->id; $foundCat = true; break;
                        }
                    }
                    if (!$foundCat) {
                        $catId = DB::table('categories')->insertGetId([
                            'name'              => $catName,
                            'classification_id' => $classId,
                            'created_at'        => now(), 'updated_at' => now(),
                        ]);
                        $catMap[$catNameLower][] = (object)['id' => $catId, 'name' => $catName, 'classification_id' => $classId];
                    }

                    // Item
                    $iName      = trim($row['article'] ?? '');
                    if (empty($iName)) $iName = 'Unspecified Item';
                    $iNameLower = strtolower($iName);

                    if (!isset($itemMap[$iNameLower])) {
                        $itemId = DB::table('items')->insertGetId([
                            'name'        => $iName,
                            'category_id' => $catId,
                            'created_at'  => now(), 'updated_at' => now(),
                        ]);
                        $itemMap[$iNameLower] = (object)['id' => $itemId, 'name' => $iName, 'category_id' => $catId];
                    } else {
                        $itemId = $itemMap[$iNameLower]->id;
                        if (empty($itemMap[$iNameLower]->category_id)) {
                            DB::table('items')->where('id', $itemId)->update(['category_id' => $catId]);
                            $itemMap[$iNameLower]->category_id = $catId;
                        }
                    }

                    $cost = $row['acquisition_cost'] ?? 0;
                    $date = $row['acquisition_date'] ?: now()->toDateString();

                    // asset_sources
                    $assetSourceId = DB::table('asset_sources')->insertGetId([
                        'item_id'               => $itemId,
                        'description'           => $row['description'] ?: null,
                        'acquisition_source_id' => $pifSourceId,
                        'mode_of_acquisition'   => 'PIF Import',
                        'asset_cost'            => $cost ?? 0,
                        'quantity'              => 1,
                        'acceptance_date'       => $date,
                        'created_at'            => now(), 'updated_at' => now(),
                    ]);

                    // asset_distributions
                    if (empty($propNo)) {
                        $propNo = 'PIF-' . now()->format('Ymd') . '-' . uniqid();
                    }

                    DB::table('asset_distributions')->insert([
                        'asset_source_id'     => $assetSourceId,
                        'region'              => $row['region'] ?: 'Region IX',
                        'division'            => $row['division'] ?: 'Division of Zamboanga City',
                        'office_school_type'  => $row['office_type'] ?: '',
                        'school_id'           => $row['school_identifier'] ?: null,
                        'office_school_name'  => $row['office_name'] ?: '',
                        'nature_of_occupancy' => $row['occupancy_nature'] ?: '',
                        'location'            => $row['location'] ?: null,
                        'property_number'     => $propNo,
                        'acquisition_cost'    => $cost ?? 0,
                        'acquisition_date'    => $date,
                        'created_at'          => now(), 'updated_at' => now(),
                    ]);

                    if ($propNo && $duplicateAction !== 'keep') $existingAssetProps[$propNo] = true;
                    $totalAssets++;
                }
            }

            DB::commit();

            $parts = [];
            if ($totalBuildings > 0) $parts[] = "{$totalBuildings} building(s)";
            if ($totalAssets > 0)    $parts[] = "{$totalAssets} asset(s)";

            DB::table('system_logs')->insert([
                'user'        => $userName,
                'activity'    => 'PIF Import: ' . implode(' and ', $parts) . ' registered',
                'module'      => 'Import',
                'action_type' => 'Create',
                'created_at'  => now(), 'updated_at' => now(),
            ]);

            session()->forget('pif_import_data');

            $msg  = 'Import complete! ';
            if ($totalBuildings > 0) $msg .= "{$totalBuildings} building(s)";
            if ($totalBuildings > 0 && $totalAssets > 0) $msg .= ' and ';
            if ($totalAssets > 0)    $msg .= "{$totalAssets} asset(s)";
            $msg .= ' registered successfully.';

            return redirect()->route('buildings.import')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('PIF Import failed: ' . $e->getMessage() . ' at line ' . $e->getLine());
            return redirect()->route('buildings.import')
                ->withErrors(['xlsx_file' => 'Import failed: ' . $e->getMessage()]);
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
                $headerValues[] = strtolower(trim(str_replace("\n", ' ', (string)$val)));
            }
        }
        $h = implode('|', $headerValues);

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
