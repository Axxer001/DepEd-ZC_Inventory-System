<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventorySetupController extends Controller
{
    /**
     * MODULE 1: MASTER REGISTRY — Register or update an item in the master list.
     *
     * This method now uses the new schema:
     * - acquisition_sources (replaces stakeholders)
     * - asset_sources (replaces sub_items)
     * - No more master_quantity on items (derived from SUM of asset_sources)
     * - No more asset_transactions
     */
    public function storeItem(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'category_name' => 'nullable|string|max:255',
            'item_name' => 'required|string|max:255',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $existingItemId = $request->existing_item_id;
        $itemName = trim($request->item_name);

        $messages = [];

        // ── Resolve Category ──
        $categoryId = $request->category_id;
        $categoryName = trim($request->category_name);

        if (!$categoryId) {
            if (!$categoryName) {
                return back()->withErrors(['category_name' => 'Please select a Main Category or type a new one.'])->withInput();
            }
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
                    'activity' => "Added new category: {$categoryName}",
                    'module' => 'Categories',
                    'action_type' => 'Create',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ── Resolve Item ──
        if ($existingItemId) {
            $existingItem = DB::table('items')->where('id', $existingItemId)->first();
            if (!$existingItem) {
                return back()->withErrors(['item_name' => 'The selected item does not exist.']);
            }
            $itemId = $existingItem->id;
            $messages[] = "Item '{$itemName}' selected for asset registration";
        } else {
            $duplicate = DB::table('items')
                ->whereRaw('LOWER(name) = ?', [strtolower($itemName)])
                ->first();

            if ($duplicate) {
                return back()->withErrors(['item_name' => "The item '{$itemName}' already exists. Please use the dropdown to select it."])->withInput();
            }

            $itemId = DB::table('items')->insertGetId([
                'name' => $itemName,
                'category_id' => $categoryId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('system_logs')->insert([
                'user' => $userName,
                'activity' => "Added new item: {$itemName}",
                'module' => 'Items',
                'action_type' => 'Create',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $messages[] = "Item '{$itemName}' registered";
        }

        if (empty($messages)) {
            $messages[] = "Item '{$itemName}' already exists — no changes made";
        }

        return back()->with('success', implode('. ', $messages) . '.');
    }

    /**
     * MODULE 2: BATCH REGISTRATION — Securely parse and insert bulk generated table rows
     */
    public function storeBatch(Request $request)
    {
        $payload = $request->validate([
            'source_of_acquisition' => 'required|string',
            'rows' => 'required|array|min:1',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';

        try {
            DB::beginTransaction();

            // 1. Resolve Global Acquisition Source
            $acqSourceName = trim($payload['source_of_acquisition']);
            $acqSource = DB::table('acquisition_sources')->whereRaw('LOWER(name) = ?', [strtolower($acqSourceName)])->first();
            if (!$acqSource) {
                $acqSourceId = DB::table('acquisition_sources')->insertGetId([
                    'name' => $acqSourceName,
                    'source_type' => 'Internal', 
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $acqSourceId = $acqSource->id;
            }

            foreach ($payload['rows'] as $row) {
                // 2. Resolve Classification
                $className = trim($row['classification'] ?? '');
                $classObj = DB::table('classifications')->whereRaw('LOWER(name) = ?', [strtolower($className)])->first();
                if (!$classObj) {
                    $classId = DB::table('classifications')->insertGetId([
                        'name' => $className,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $classId = $classObj->id;
                }

                // 3. Resolve Category
                $catName = trim($row['category'] ?? '');
                $catObj = DB::table('categories')->whereRaw('LOWER(name) = ?', [strtolower($catName)])->first();
                if (!$catObj) {
                    $catId = DB::table('categories')->insertGetId([
                        'classification_id' => $classId,
                        'name' => $catName,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $catId = $catObj->id;
                    if (!$catObj->classification_id) {
                        DB::table('categories')->where('id', $catId)->update(['classification_id' => $classId]);
                    }
                }

                // 4. Resolve Item
                $itemName = trim($row['item'] ?? '');
                $itemObj = DB::table('items')->whereRaw('LOWER(name) = ?', [strtolower($itemName)])->first();
                if (!$itemObj) {
                    $itemId = DB::table('items')->insertGetId([
                        'category_id' => $catId,
                        'name' => $itemName,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $itemId = $itemObj->id;
                }

                // 5. Insert Asset Source
                $costPerUnit = floatval($row['cost'] ?? 0);
                $qty = intval($row['qty'] ?? 1);
                
                $assetSourceId = DB::table('asset_sources')->insertGetId([
                    'item_id' => $itemId,
                    'description' => $row['description'] ?? null,
                    'unit_of_measurement' => $row['uom'] ?? null,
                    'acquisition_source_id' => $acqSourceId,
                    'mode_of_acquisition' => $row['mode'] ?? 'Unknown',
                    'source_personnel' => !empty($row['personnel']) ? $row['personnel'] : null,
                    'personnel_position' => !empty($row['position']) ? $row['position'] : null,
                    'asset_cost' => $costPerUnit,
                    'quantity' => $qty,
                    'estimated_useful_life' => (isset($row['useful-life']) && $row['useful-life'] !== '') ? intval($row['useful-life']) : null,
                    'acceptance_date' => $row['acceptance-date'] ?? now()->toDateString(),
                    'remarks' => !empty($row['remarks']) ? $row['remarks'] : 'Good Condition',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // 6. Insert Asset Distribution
                $acqCost = $costPerUnit * $qty;
                
                // Allow empty property number to be null instead of empty string for unique constraint handling
                $propertyNo = isset($row['property-no']) && trim($row['property-no']) !== '' ? trim($row['property-no']) : null;
                
                DB::table('asset_distributions')->insert([
                    'asset_source_id' => $assetSourceId,
                    'region' => $row['region'] ?? 'Region IX',
                    'division' => $row['division'] ?? 'Division of Zamboanga City',
                    'office_school_type' => $row['school-type'] ?? '',
                    'school_id' => $row['school-id'] ?? null,
                    'office_school_name' => $row['school-name'] ?? '',
                    'nature_of_occupancy' => $row['occupancy'] ?? '',
                    'location' => $row['location'] ?? null,
                    'property_number' => $propertyNo,
                    'acquisition_cost' => $acqCost,
                    'acquisition_date' => $row['acquisition-date'] ?? now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $count = count($payload['rows']);
            DB::table('system_logs')->insert([
                'user' => $userName,
                'activity' => "Registered {$count} items via Bulk Asset Registration",
                'module' => 'Assets',
                'action_type' => 'Create',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully registered {$count} items."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store one or more building rows from the registration form.
     */
    public function storeBuilding(Request $request)
    {
        $rows = $request->input('rows', []);

        if (empty($rows)) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No building data submitted.'
                ], 400);
            }
            return back()->withErrors(['rows' => 'No building data submitted.']);
        }

        $userName = auth()->user() ? auth()->user()->email : 'System';
        $inserted = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $officeName = trim($row['office_name'] ?? '');
                if (empty($officeName)) continue; // skip rows without office name

                // Resolve school FK
                $schoolId = null;
                $schoolIdentifier = trim($row['school_identifier'] ?? '');
                if (!empty($schoolIdentifier)) {
                    $school = DB::table('schools')
                        ->where('school_id', $schoolIdentifier)
                        ->first();
                    if ($school) $schoolId = $school->id;
                }

                // Parse numeric fields
                $storeys    = !empty($row['storeys']) ? (int)$row['storeys'] : null;
                $classrooms = !empty($row['classrooms']) ? (int)$row['classrooms'] : null;
                $acquisitionCost = !empty($row['acquisition_cost']) ? (float)str_replace(',', '', $row['acquisition_cost']) : null;
                $estimatedUsefulLife = !empty($row['estimated_useful_life']) ? (int)$row['estimated_useful_life'] : 25;
                $appraisedValue  = !empty($row['appraised_value']) ? (float)str_replace(',', '', $row['appraised_value']) : null;

                // Taxonomy lookup
                $classStr = trim($row['classification'] ?? 'Unclassified');
                $classRec = DB::table('building_classifications')->where('name', $classStr)->first();
                $classId = $classRec ? $classRec->id : DB::table('building_classifications')->insertGetId(['name' => $classStr, 'created_at' => now(), 'updated_at' => now()]);

                $typeStr = trim($row['article'] ?? 'Building');
                $typeRec = DB::table('building_types')->where('building_classification_id', $classId)->where('name', $typeStr)->first();
                $typeId = $typeRec ? $typeRec->id : DB::table('building_types')->insertGetId(['building_classification_id' => $classId, 'name' => $typeStr, 'created_at' => now(), 'updated_at' => now()]);

                $descStr = trim($row['description'] ?? '');
                
                $specQuery = DB::table('building_specs')->where('building_type_id', $typeId)->where('description', $descStr);
                if ($storeys !== null) $specQuery->where('storeys', $storeys); else $specQuery->whereNull('storeys');
                if ($classrooms !== null) $specQuery->where('classrooms', $classrooms); else $specQuery->whereNull('classrooms');
                $specRec = $specQuery->first();
                
                $specId = $specRec ? $specRec->id : DB::table('building_specs')->insertGetId([
                    'building_type_id' => $typeId,
                    'description' => $descStr ?: null,
                    'storeys' => $storeys,
                    'classrooms' => $classrooms,
                    'created_at' => now(), 'updated_at' => now()
                ]);

                DB::table('building_records')->insert([
                    'school_id'         => $schoolId,
                    'building_spec_id'  => $specId,
                    'region'            => trim($row['region'] ?? 'REGION IX'),
                    'division'          => trim($row['division'] ?? 'Division of Zamboanga City'),
                    'office_type'       => trim($row['office_type'] ?? '') ?: null,
                    'address'           => trim($row['address'] ?? '') ?: null,
                    'location'          => trim($row['location'] ?? '') ?: null,
                    'occupancy_nature'  => trim($row['occupancy_nature'] ?? '') ?: null,
                    'date_constructed'  => !empty($row['date_constructed']) ? $row['date_constructed'] : null,
                    'acquisition_date'  => !empty($row['acquisition_date']) ? $row['acquisition_date'] : null,
                    'property_number'   => trim($row['property_number'] ?? '') ?: null,
                    'acquisition_cost'  => $acquisitionCost,
                    'estimated_useful_life' => $estimatedUsefulLife,
                    'appraised_value'   => $appraisedValue,
                    'appraisal_date'    => !empty($row['appraisal_date']) ? $row['appraisal_date'] : null,
                    'remarks'           => trim($row['remarks'] ?? '') ?: null,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
                $inserted++;
            }

            DB::commit();

            if ($inserted > 0) {
                DB::table('system_logs')->insert([
                    'user'        => $userName,
                    'activity'    => "Building Registration: {$inserted} building(s) registered via manual entry",
                    'module'      => 'Buildings',
                    'action_type' => 'Create',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$inserted} building(s) registered successfully."
                ]);
            }

            return redirect('/inventory-setup')
                ->with('success', "{$inserted} building(s) registered successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration failed: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['rows' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    /**
     * MODULE: Inventory Management (Edit)
     * Handles batch updates to asset_sources and asset_distributions.
     */
    public function updateBatch(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.dist_id' => 'required|integer',
            'updates.*.src_id' => 'required|integer',
        ]);

        $updates = $request->input('updates');
        $userName = auth()->user() ? auth()->user()->name : 'System';
        $updateCount = 0;

        DB::beginTransaction();
        try {
            foreach ($updates as $data) {
                // Determine if we need to update asset_sources
                $srcUpdates = [];

                // Resolve item_id or acq_source if hierarchy names are provided
                $hasHierarchyChange = array_key_exists('classification', $data) || 
                                     array_key_exists('category', $data) || 
                                     array_key_exists('article', $data);
                
                $hasAcqSourceChange = array_key_exists('acq_source', $data);

                if ($hasHierarchyChange || $hasAcqSourceChange) {
                    $row = DB::table('asset_sources')
                        ->leftJoin('items', 'asset_sources.item_id', '=', 'items.id')
                        ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
                        ->leftJoin('classifications', 'categories.classification_id', '=', 'classifications.id')
                        ->leftJoin('acquisition_sources', 'asset_sources.acquisition_source_id', '=', 'acquisition_sources.id')
                        ->where('asset_sources.id', $data['src_id'])
                        ->select('classifications.name as class_name', 'categories.name as cat_name', 'items.name as item_name', 'acquisition_sources.name as acq_source_name')
                        ->first();

                    if ($row) {
                        if ($hasHierarchyChange) {
                            $className = trim($data['classification'] ?? $row->class_name);
                            $catName = trim($data['category'] ?? $row->cat_name);
                            $itemName = trim($data['article'] ?? $row->item_name);

                            // 1. Ensure Classification
                            if ($className) {
                                $classId = DB::table('classifications')->whereRaw('LOWER(name) = ?', [strtolower($className)])->value('id');
                                if (!$classId) {
                                    $classId = DB::table('classifications')->insertGetId(['name' => $className, 'created_at' => now(), 'updated_at' => now()]);
                                }
                            } else { $classId = null; }

                            // 2. Ensure Category
                            if ($catName) {
                                $catQuery = DB::table('categories')->whereRaw('LOWER(name) = ?', [strtolower($catName)]);
                                if ($classId) $catQuery->where('classification_id', $classId);
                                else $catQuery->whereNull('classification_id');
                                
                                $catId = $catQuery->value('id');
                                if (!$catId) {
                                    $catId = DB::table('categories')->insertGetId(['name' => $catName, 'classification_id' => $classId, 'created_at' => now(), 'updated_at' => now()]);
                                }
                            } else { $catId = null; }

                            // 3. Ensure Item
                            if ($itemName) {
                                $itemQuery = DB::table('items')->whereRaw('LOWER(name) = ?', [strtolower($itemName)]);
                                if ($catId) $itemQuery->where('category_id', $catId);
                                else $itemQuery->whereNull('category_id');

                                $itemId = $itemQuery->value('id');
                                if (!$itemId) {
                                    $itemId = DB::table('items')->insertGetId(['name' => $itemName, 'category_id' => $catId, 'created_at' => now(), 'updated_at' => now()]);
                                }
                                $srcUpdates['item_id'] = $itemId;
                            }
                        }

                        if ($hasAcqSourceChange) {
                            $acqSourceName = trim($data['acq_source'] ?? $row->acq_source_name);
                            if ($acqSourceName) {
                                $acqSourceId = DB::table('acquisition_sources')->whereRaw('LOWER(name) = ?', [strtolower($acqSourceName)])->value('id');
                                if (!$acqSourceId) {
                                    $acqSourceId = DB::table('acquisition_sources')->insertGetId([
                                        'name' => $acqSourceName,
                                        'source_type' => 'Internal',
                                        'created_at' => now(),
                                        'updated_at' => now()
                                    ]);
                                }
                                $srcUpdates['acquisition_source_id'] = $acqSourceId;
                            }
                        }
                    }
                }

                if (array_key_exists('description', $data)) $srcUpdates['description'] = $data['description'];
                if (array_key_exists('uom', $data)) $srcUpdates['unit_of_measurement'] = $data['uom'];
                if (array_key_exists('cost', $data)) $srcUpdates['asset_cost'] = floatval($data['cost']);
                if (array_key_exists('qty', $data)) $srcUpdates['quantity'] = intval($data['qty']);
                if (array_key_exists('useful_life', $data)) $srcUpdates['estimated_useful_life'] = intval($data['useful_life']);
                if (array_key_exists('acceptance_date', $data)) $srcUpdates['acceptance_date'] = $data['acceptance_date'];
                if (array_key_exists('mode', $data)) $srcUpdates['mode_of_acquisition'] = $data['mode'];
                if (array_key_exists('personnel', $data)) $srcUpdates['source_personnel'] = $data['personnel'];
                if (array_key_exists('position', $data)) $srcUpdates['personnel_position'] = $data['position'];
                if (array_key_exists('remarks', $data)) $srcUpdates['remarks'] = $data['remarks'];

                if (!empty($srcUpdates)) {
                    DB::table('asset_sources')->where('id', $data['src_id'])->update($srcUpdates);
                }

                // Determine if we need to update asset_distributions
                $distUpdates = [];
                if (array_key_exists('occupancy', $data)) $distUpdates['nature_of_occupancy'] = $data['occupancy'];
                if (array_key_exists('location', $data)) $distUpdates['location'] = $data['location'];
                if (array_key_exists('property_no', $data)) $distUpdates['property_number'] = $data['property_no'];
                if (array_key_exists('school_type', $data)) $distUpdates['office_school_type'] = $data['school_type'];
                if (array_key_exists('school_id', $data)) $distUpdates['school_id'] = $data['school_id'];
                if (array_key_exists('office_school_name', $data)) $distUpdates['office_school_name'] = $data['office_school_name'];
                if (array_key_exists('acquisition_date', $data)) $distUpdates['acquisition_date'] = $data['acquisition_date'];

                if (!empty($distUpdates)) {
                    DB::table('asset_distributions')->where('id', $data['dist_id'])->update($distUpdates);
                }

                $updateCount++;
            }

            DB::table('system_logs')->insert([
                'user' => $userName,
                'activity' => "Bulk Edit: Updated {$updateCount} asset records.",
                'module' => 'Inventory Management',
                'action_type' => 'Update',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updateCount} records."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET building filter options for the Infrastructure Editor
     */
    public function getBuildingEditFilters(Request $request)
    {
        $baseQuery = DB::table('building_records')
            ->leftJoin('schools', 'building_records.school_id', '=', 'schools.id')
            ->leftJoin('building_specs', 'building_records.building_spec_id', '=', 'building_specs.id')
            ->leftJoin('building_types', 'building_specs.building_type_id', '=', 'building_types.id')
            ->leftJoin('building_classifications', 'building_types.building_classification_id', '=', 'building_classifications.id');

        $classifications = (clone $baseQuery)->whereNotNull('building_classifications.name')->distinct()->orderBy('building_classifications.name')->pluck('building_classifications.name');
        $office_types    = (clone $baseQuery)->whereNotNull('building_records.office_type')->distinct()->orderBy('building_records.office_type')->pluck('building_records.office_type');
        $articles        = (clone $baseQuery)->whereNotNull('building_types.name')->distinct()->orderBy('building_types.name')->pluck('building_types.name');
        $schools         = (clone $baseQuery)->whereNotNull('schools.name')->distinct()->orderBy('schools.name')->pluck('schools.name');
        $occupancies     = (clone $baseQuery)->whereNotNull('building_records.occupancy_nature')->distinct()->orderBy('building_records.occupancy_nature')->pluck('building_records.occupancy_nature');

        return response()->json(compact('classifications', 'office_types', 'articles', 'schools', 'occupancies'));
    }

    /**
     * POST building editor preview rows
     */
    public function getBuildingEditPreview(Request $request)
    {
        $filters = $request->input('filters', []);

        $q = DB::table('building_records')
            ->leftJoin('schools', 'building_records.school_id', '=', 'schools.id')
            ->leftJoin('building_specs', 'building_records.building_spec_id', '=', 'building_specs.id')
            ->leftJoin('building_types', 'building_specs.building_type_id', '=', 'building_types.id')
            ->leftJoin('building_classifications', 'building_types.building_classification_id', '=', 'building_classifications.id');

        if (!empty($filters['classification'])) $q->where('building_classifications.name', $filters['classification']);
        if (!empty($filters['office_type']))    $q->where('building_records.office_type', $filters['office_type']);
        if (!empty($filters['article']))        $q->where('building_types.name', $filters['article']);
        if (!empty($filters['school']))         $q->where('schools.name', $filters['school']);
        if (!empty($filters['occupancy']))      $q->where('building_records.occupancy_nature', $filters['occupancy']);
        if (!empty($filters['date']))           $q->whereDate('building_records.date_constructed', $filters['date']);

        if (!empty($filters['emptyCol'])) {
            $colMap = [
                'classification'   => 'building_classifications.name',
                'article'          => 'building_types.name',
                'description'      => 'building_specs.description',
                'office_name'      => 'schools.name',
                'property_number'  => 'building_records.property_number',
                'acquisition_cost' => 'building_records.acquisition_cost',
                'date_constructed' => 'building_records.date_constructed',
            ];
            if (isset($colMap[$filters['emptyCol']])) {
                $q->where(function($sub) use ($colMap, $filters) {
                    $sub->whereNull($colMap[$filters['emptyCol']])->orWhere($colMap[$filters['emptyCol']], '');
                });
            }
        }

        if (!empty($filters['sortCost'])) {
            $q->orderBy('building_records.acquisition_cost', $filters['sortCost'] === 'low_to_high' ? 'asc' : 'desc');
        } else {
            $q->orderBy('building_records.id', 'asc');
        }

        $rows = $q->select([
            'building_records.id', 'building_records.school_id', 'building_records.region', 'building_records.division', 'building_records.office_type', 'schools.school_id as school_identifier',
            'schools.name as office_name', 'building_records.address', 'building_specs.storeys', 'building_specs.classrooms', 'building_types.name as article', 'building_specs.description',
            'building_classifications.name as classification', 'building_records.occupancy_nature', 'building_records.location', 'building_records.date_constructed',
            'building_records.acquisition_date', 'building_records.property_number', 'building_records.acquisition_cost',
            'building_records.estimated_useful_life', 'building_records.remarks'
        ])->get();

        return response()->json(['rows' => $rows]);
    }

    /**
     * POST batch update buildings
     */
    public function updateBuildingBatch(Request $request)
    {
        $updates = $request->input('updates', []);
        if (empty($updates)) {
            return response()->json(['success' => false, 'message' => 'No updates provided.'], 422);
        }

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($updates as $update) {
                $id = $update['id'] ?? null;
                if (!$id) continue;
                
                $record = DB::table('building_records')->where('id', $id)->first();
                if (!$record) continue;

                $specId = $record->building_spec_id;
                $needsSpecUpdate = isset($update['classification']) || isset($update['article']) || isset($update['description']) || array_key_exists('storeys', $update) || array_key_exists('classrooms', $update);

                if ($needsSpecUpdate) {
                    $oldSpec = DB::table('building_specs')->where('id', $specId)->first();
                    $oldType = $oldSpec ? DB::table('building_types')->where('id', $oldSpec->building_type_id)->first() : null;
                    $oldClass = $oldType ? DB::table('building_classifications')->where('id', $oldType->building_classification_id)->first() : null;

                    $classStr = trim($update['classification'] ?? ($oldClass->name ?? 'Unclassified'));
                    $classRec = DB::table('building_classifications')->where('name', $classStr)->first();
                    $classId = $classRec ? $classRec->id : DB::table('building_classifications')->insertGetId(['name' => $classStr, 'created_at' => now(), 'updated_at' => now()]);

                    $typeStr = trim($update['article'] ?? ($oldType->name ?? 'Building'));
                    $typeRec = DB::table('building_types')->where('building_classification_id', $classId)->where('name', $typeStr)->first();
                    $typeId = $typeRec ? $typeRec->id : DB::table('building_types')->insertGetId(['building_classification_id' => $classId, 'name' => $typeStr, 'created_at' => now(), 'updated_at' => now()]);

                    $descStr = trim($update['description'] ?? ($oldSpec->description ?? ''));
                    $st = array_key_exists('storeys', $update) ? $update['storeys'] : ($oldSpec->storeys ?? null);
                    $cr = array_key_exists('classrooms', $update) ? $update['classrooms'] : ($oldSpec->classrooms ?? null);

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
                }

                $recordData = [];
                if ($needsSpecUpdate) {
                    $recordData['building_spec_id'] = $specId;
                }

                $directCols = ['office_type', 'school_id', 'address', 'region', 'division', 'location', 'occupancy_nature', 'date_constructed', 'acquisition_date', 'property_number', 'acquisition_cost', 'estimated_useful_life', 'remarks', 'appraised_value', 'appraisal_date'];
                
                foreach ($directCols as $col) {
                    if (array_key_exists($col, $update)) {
                        $recordData[$col] = $update[$col];
                    }
                }

                if (!empty($recordData)) {
                    DB::table('building_records')->where('id', $id)->update($recordData);
                }
                $count++;
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => "Updated {$count} building(s) successfully."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

}