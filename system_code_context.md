# System Code Context

This document contains the source code for the controllers and views responsible for Item/Building Management, Imports, and Bulk Edits.

## File: `app/Http/Controllers/InventorySetupController.php`

```php
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
        $existingItemId = $request->input('existing_item_id');
        $itemName = trim($request->input('item_name'));

        $messages = [];

        // ── Resolve Category ──
        $categoryId = $request->input('category_id');
        $categoryName = trim($request->input('category_name'));

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

        $gemini = new \App\Services\GeminiService();
        $rows = $gemini->sanitizeRows($payload['rows'], 'manual_batch');

        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $userName = $user ? $user->name : 'System';
        
        try {
            DB::beginTransaction();

            $acqSourceName = trim($payload['source_of_acquisition']);
            $acqSourceId = DB::table('acquisition_sources')->updateOrInsert(
                ['name' => $acqSourceName],
                ['source_type' => 'Internal', 'updated_at' => now()]
            );
            $acqSourceId = DB::table('acquisition_sources')->where('name', $acqSourceName)->value('id');

            // Pre-load memory-cached lookup maps
            $caches = [
                'class' => array_change_key_case(DB::table('classifications')->pluck('id', 'name')->toArray(), CASE_LOWER),
                'cat'   => array_change_key_case(DB::table('categories')->pluck('id', 'name')->toArray(), CASE_LOWER),
                'item'  => array_change_key_case(DB::table('items')->pluck('id', 'name')->toArray(), CASE_LOWER),
                'mode'  => array_change_key_case(DB::table('procurement_modes')->pluck('id', 'name')->toArray(), CASE_LOWER),
            ];
            
            foreach ($rows as $row) {
                // 1. Resolve Hierarchy (Classification -> Category -> Item)
                $className = trim($row['classification'] ?? 'Unclassified');
                $classId = $caches['class'][strtolower($className)] ??= DB::table('classifications')->insertGetId(['name' => $className, 'created_at' => now()]);

                $catName = trim($row['category'] ?? 'General');
                $catId = $caches['cat'][strtolower($catName)] ??= DB::table('categories')->insertGetId([
                    'classification_id' => $classId, 
                    'name' => $catName, 
                    'created_at' => now()
                ]);

                $itemName = trim($row['item'] ?? 'Unknown Item');
                $itemId = $caches['item'][strtolower($itemName)] ??= DB::table('items')->insertGetId([
                    'category_id' => $catId, 
                    'name' => $itemName, 
                    'created_at' => now()
                ]);

                // 2. Resolve Procurement Mode
                $modeName = trim($row['mode'] ?? 'Direct Purchase');
                $modeId = $caches['mode'][strtolower($modeName)] ??= DB::table('procurement_modes')->insertGetId(['name' => $modeName, 'created_at' => now()]);

                // 3. Resolve Contact & Custodian (Surgical lookups)
                $contactId = null;
                if (!empty($row['personnel'])) {
                    $contactId = DB::table('acquisition_contacts')->updateOrInsert(
                        ['acquisition_source_id' => $acqSourceId, 'name' => $row['personnel']],
                        ['position' => $row['position'] ?? null, 'updated_at' => now()]
                    );
                    $contactId = DB::table('acquisition_contacts')->where(['acquisition_source_id' => $acqSourceId, 'name' => $row['personnel']])->value('id');
                }

                $custodianId = null;
                if (!empty($row['custodian-last'])) {
                    // Resolve office_id from office type string if provided
                    $custodianOfficeId = null;
                    if (!empty($row['school-type']) && stripos($row['school-type'], 'division') !== false) {
                        $custodianOfficeId = DB::table('offices')->where('name', 'like', '%Division%')->value('id');
                    }
                    DB::table('custodians')->updateOrInsert(
                        ['last_name' => $row['custodian-last'], 'first_name' => $row['custodian-first'] ?? ''],
                        [
                            'middle_name' => $row['custodian-middle'] ?? null,
                            'position'    => $row['custodian-pos'] ?? null,
                            'school_id'   => $row['school-id'] ?? null,
                            'office_id'   => $custodianOfficeId,
                            'updated_at'  => now(),
                        ]
                    );
                    $custodianId = DB::table('custodians')->where(['last_name' => $row['custodian-last'], 'first_name' => $row['custodian-first'] ?? ''])->value('id');
                }

                // 4. Final Insertions
                $assetSourceId = DB::table('asset_sources')->insertGetId([
                    'item_id' => $itemId,
                    'description' => $row['description'] ?? null,
                    'unit_of_measurement' => $row['uom'] ?? 'Unit',
                    'acquisition_source_id' => $acqSourceId,
                    'procurement_mode_id' => $modeId,
                    'acquisition_contact_id' => $contactId,
                    'asset_cost' => floatval($row['cost'] ?? 0),
                    'quantity' => intval($row['qty'] ?? 1),
                    'estimated_useful_life' => intval($row['useful-life'] ?? 0),
                    'acceptance_date' => $row['acceptance-date'] ?? now()->toDateString(),
                    'remarks'         => null,
                    'created_at'      => now(),
                ]);

                // Map legacy condition strings to valid ENUM values
                $conditionRaw = trim($row['condition'] ?? '');
                $conditionMap = [
                    'good condition' => 'Serviceable',
                    'serviceable'    => 'Serviceable',
                    'minor repair'   => 'Minor Repair',
                    'major repair'   => 'Major Repair',
                    'condemned'      => 'Condemned',
                    'not useable'    => 'Condemned',
                    'needs repair'   => 'Minor Repair',
                ];
                $condition = $conditionMap[strtolower($conditionRaw)] ?? 'Serviceable';

                DB::table('asset_assignments')->insert([
                    'asset_source_id'    => $assetSourceId,
                    'custodian_id'       => $custodianId,
                    'condition'          => $condition,
                    'office_school_type' => $row['school-type'] ?? null,
                    'location'           => $row['location'] ?? null,
                    'property_number'    => $row['property-no'] ?? null,
                    'acquisition_cost'   => floatval($row['cost'] ?? 0) * intval($row['qty'] ?? 1),
                    'acquisition_date'   => $row['acquisition-date'] ?? now()->toDateString(),
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            DB::table('system_logs')->insert([
                'user' => $userName,
                'activity' => "Batch Registration: " . count($rows) . " assets.",
                'module' => 'Assets',
                'action_type' => 'Create',
                'created_at' => now()
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => "Successfully registered " . count($rows) . " items."]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[Inventory] storeBatch failure: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
     * Handles batch updates to asset_sources and asset_assignments.
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
                if (array_key_exists('brand', $data)) $srcUpdates['brand'] = $data['brand'];
                if (array_key_exists('model', $data)) $srcUpdates['model'] = $data['model'];
                if (array_key_exists('serial_no', $data)) $srcUpdates['serial_number'] = $data['serial_no'];
                if (array_key_exists('uom', $data)) $srcUpdates['unit_of_measurement'] = $data['uom'];
                if (array_key_exists('cost', $data)) $srcUpdates['asset_cost'] = floatval($data['cost']);
                if (array_key_exists('qty', $data)) $srcUpdates['quantity'] = intval($data['qty']);
                if (array_key_exists('useful_life', $data)) $srcUpdates['estimated_useful_life'] = intval($data['useful_life']);
                if (array_key_exists('acceptance_date', $data)) $srcUpdates['acceptance_date'] = $data['acceptance_date'];
                
                // Process Mode
                if (array_key_exists('mode', $data)) {
                    $modeName = trim($data['mode']);
                    if ($modeName) {
                        $modeId = DB::table('procurement_modes')->whereRaw('LOWER(name) = ?', [strtolower($modeName)])->value('id');
                        if (!$modeId) {
                            $modeId = DB::table('procurement_modes')->insertGetId(['name' => $modeName, 'created_at' => now(), 'updated_at' => now()]);
                        }
                        $srcUpdates['procurement_mode_id'] = $modeId;
                    } else {
                        $srcUpdates['procurement_mode_id'] = null;
                    }
                }

                // Process Contact
                if (array_key_exists('personnel', $data) || array_key_exists('position', $data)) {
                    $personnelName = trim($data['personnel'] ?? $row->source_personnel ?? '');
                    $personnelPos = trim($data['position'] ?? $row->personnel_position ?? '');
                    $currentAcqSource = $srcUpdates['acquisition_source_id'] ?? $row->acquisition_source_id;

                    if ($personnelName !== '' || $personnelPos !== '') {
                        $contact = DB::table('acquisition_contacts')
                            ->where('acquisition_source_id', $currentAcqSource)
                            ->where('name', $personnelName)
                            ->where('position', $personnelPos)
                            ->first();

                        if (!$contact) {
                            $contactId = DB::table('acquisition_contacts')->insertGetId([
                                'acquisition_source_id' => $currentAcqSource,
                                'name' => $personnelName ?: null,
                                'position' => $personnelPos ?: null,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        } else {
                            $contactId = $contact->id;
                        }
                        $srcUpdates['acquisition_contact_id'] = $contactId;
                    } else {
                        $srcUpdates['acquisition_contact_id'] = null;
                    }
                }

                if (array_key_exists('remarks', $data)) $srcUpdates['remarks'] = $data['remarks'];

                if (!empty($srcUpdates)) {
                    DB::table('asset_sources')->where('id', $data['src_id'])->update($srcUpdates);
                }

                // Determine if we need to update asset_assignments
                $distUpdates = [];
                // nature_of_occupancy and school_id have been removed from asset_assignments.
                // school_id is now on custodians; handle it there via custodian update below.
                if (array_key_exists('location', $data)) $distUpdates['location'] = $data['location'];
                if (array_key_exists('property_no', $data)) $distUpdates['property_number'] = $data['property_no'];
                if (array_key_exists('school_type', $data)) $distUpdates['office_school_type'] = $data['school_type'];
                if (array_key_exists('acquisition_date', $data)) $distUpdates['acquisition_date'] = $data['acquisition_date'];
                if (array_key_exists('condition', $data)) {
                    $conditionMap = [
                        'good condition' => 'Serviceable', 'serviceable' => 'Serviceable',
                        'minor repair' => 'Minor Repair', 'major repair' => 'Major Repair',
                        'condemned' => 'Condemned', 'not useable' => 'Condemned', 'needs repair' => 'Minor Repair',
                    ];
                    $distUpdates['condition'] = $conditionMap[strtolower(trim($data['condition']))] ?? 'Serviceable';
                }

                // Process Custodian
                if (
                    array_key_exists('custodian_first_name', $data) || 
                    array_key_exists('custodian_middle_name', $data) || 
                    array_key_exists('custodian_last_name', $data) || 
                    array_key_exists('custodian_position', $data) || 
                    array_key_exists('custodian_contact_number', $data)
                ) {
                    $rowDist = DB::table('asset_assignments')
                        ->leftJoin('custodians', 'asset_assignments.custodian_id', '=', 'custodians.id')
                        ->where('asset_assignments.id', $data['dist_id'])
                        ->select('custodians.first_name', 'custodians.middle_name', 'custodians.last_name', 'custodians.position', 'custodians.contact_number')
                        ->first();

                    $custFirst = trim($data['custodian_first_name'] ?? $rowDist->first_name ?? '');
                    $custMiddle = trim($data['custodian_middle_name'] ?? $rowDist->middle_name ?? '');
                    $custLast = trim($data['custodian_last_name'] ?? $rowDist->last_name ?? '');
                    $custPos = trim($data['custodian_position'] ?? $rowDist->position ?? '');
                    $custContact = trim($data['custodian_contact_number'] ?? $rowDist->contact_number ?? '');

                    if ($custFirst !== '' || $custLast !== '') {
                        $custQuery = DB::table('custodians')
                            ->where('first_name', $custFirst)
                            ->where('last_name', $custLast);
                            
                        if ($custMiddle !== '') {
                            $custQuery->where('middle_name', $custMiddle);
                        } else {
                            $custQuery->where(function($q) {
                                $q->whereNull('middle_name')->orWhere('middle_name', '');
                            });
                        }
                        
                        $custodian = $custQuery->first();
                        
                        if (!$custodian) {
                            $custodianId = DB::table('custodians')->insertGetId([
                                'first_name' => $custFirst ?: null,
                                'middle_name' => $custMiddle ?: null,
                                'last_name' => $custLast ?: null,
                                'position' => $custPos ?: null,
                                'contact_number' => $custContact ?: null,
                                'status' => 'Active',
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        } else {
                            $custodianId = $custodian->id;
                        }
                        $distUpdates['custodian_id'] = $custodianId;
                    } else {
                        $distUpdates['custodian_id'] = null;
                    }
                }

                if (!empty($distUpdates)) {
                    DB::table('asset_assignments')->where('id', $data['dist_id'])->update($distUpdates);
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
```

## File: `app/Http/Controllers/ImportController.php`

```php
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

                // ── Resolve Procurement Mode ──
                $modeName = 'CSV Import';
                $modeId = DB::table('procurement_modes')->where('name', $modeName)->value('id');
                if (!$modeId) {
                    $modeId = DB::table('procurement_modes')->insertGetId([
                        'name' => $modeName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // ── Insert Asset Source (replaces sub_items) ──
                DB::table('asset_sources')->insert([
                    'item_id' => $itemId,
                    'description' => !empty($description) ? $description : null,
                    'acquisition_source_id' => $acquisitionSourceId,
                    'procurement_mode_id' => $modeId,
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
```

## File: `app/Http/Controllers/BuildingImportController.php`

```php
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
```

## File: `app/Http/Controllers/AssetController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    public function index()
    {
        $inventory = $this->buildInventoryData();
        return view('assets.view-assets', compact('inventory'));
    }

    /**
     * Build inventory hierarchy from the new schema:
     * classifications -> categories -> items -> asset_sources (descriptions)
     * with assignment data from asset_assignments
     */
    private function buildInventoryData()
    {
        $inventory = [];

        $defaultIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 01-13.5 18v-2.25z" /></svg>';

        // 1. Fetch categories with total sourced quantity
        $allCategories = DB::table('categories')
            ->leftJoin('items', 'categories.id', '=', 'items.category_id')
            ->leftJoin('asset_sources', 'items.id', '=', 'asset_sources.item_id')
            ->select('categories.id', 'categories.name', DB::raw('COALESCE(SUM(asset_sources.quantity), 0) as total_assets'))
            ->groupBy('categories.id', 'categories.name')
            ->get();

        foreach ($allCategories as $cat) {
            $inventory[$cat->name] = [
                'icon' => $defaultIcon,
                'total_assets' => (int) $cat->total_assets,
                'items' => []
            ];
        }

        // 2. Fetch items with sourced and distributed quantities
        $allItems = DB::table('items')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin(DB::raw('(SELECT item_id, SUM(quantity) as sourced_qty FROM asset_sources GROUP BY item_id) as src'), 'items.id', '=', 'src.item_id')
            ->leftJoin(DB::raw('(SELECT asrc.item_id, COUNT(ad.id) as distributed_qty FROM asset_assignments ad JOIN asset_sources asrc ON ad.asset_source_id = asrc.id WHERE ad.location != "AMU Warehouse" OR ad.location IS NULL GROUP BY asrc.item_id) as dist'), 'items.id', '=', 'dist.item_id')
            ->select(
                'items.id',
                'items.name as item_name',
                'categories.name as category_name',
                DB::raw('COALESCE(src.sourced_qty, 0) as sourced_quantity'),
                DB::raw('COALESCE(dist.distributed_qty, 0) as distributed_quantity')
            )
            ->get();

        foreach ($allItems as $item) {
            $catName = $item->category_name;
            $itemName = $item->item_name;

            if (isset($inventory[$catName]) && !isset($inventory[$catName]['items'][$itemName])) {
                $inventory[$catName]['items'][$itemName] = [
                    'master_quantity' => (int) $item->sourced_quantity,
                    'distributed_assets' => (int) $item->distributed_quantity,
                    'in_warehouse' => max(0, (int) $item->sourced_quantity - (int) $item->distributed_quantity),
                    'sub_items' => []
                ];
            }
        }

        // 3. Fetch asset_sources as "sub-items" (descriptions grouped by item)
        $allAssetSources = DB::table('asset_sources')
            ->join('items', 'asset_sources.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select('asset_sources.description', 'items.name as item_name', 'categories.name as category_name')
            ->get();

        foreach ($allAssetSources as $src) {
            $catName = $src->category_name;
            $itemName = $src->item_name;
            $subName = $src->description ?: $itemName;

            if (isset($inventory[$catName]['items'][$itemName])) {
                $inventory[$catName]['items'][$itemName]['sub_items'][$subName] = [];
            }
        }

        // 4. Fetch distributions grouped by asset source and school
        $records = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('custodians as c', 'ad.custodian_id', '=', 'c.id')
            ->leftJoin('offices', 'c.office_id', '=', 'offices.id')
            ->leftJoin('schools', 'c.school_id', '=', 'schools.school_id')
            ->where(function($q) {
                $q->where('ad.location', '!=', 'AMU Warehouse')
                  ->orWhereNull('ad.location');
            })
            ->select(
                DB::raw('COALESCE(schools.name, offices.name, ad.location) as school_name'),
                'categories.name as category_name',
                'items.name as item_name',
                DB::raw('COALESCE(asrc.description, items.name) as sub_item_name'),
                DB::raw('1 as quantity')
            )
            ->get();

        foreach ($records as $row) {
            $cat = $row->category_name;
            $item = $row->item_name;
            $sub = $row->sub_item_name ?? 'General / Default';

            if (!isset($inventory[$cat]['items'][$item]['sub_items'][$sub])) {
                $inventory[$cat]['items'][$item]['sub_items'][$sub] = [];
            }

            $existingSchoolIndex = null;
            foreach ($inventory[$cat]['items'][$item]['sub_items'][$sub] as $index => $schoolEntry) {
                if ($schoolEntry['name'] === $row->school_name) {
                    $existingSchoolIndex = $index;
                    break;
                }
            }

            if ($existingSchoolIndex !== null) {
                $inventory[$cat]['items'][$item]['sub_items'][$sub][$existingSchoolIndex]['qty'] += $row->quantity;
            } else {
                $inventory[$cat]['items'][$item]['sub_items'][$sub][] = [
                    'name' => $row->school_name,
                    'qty' => $row->quantity,
                    'status' => 'Serviceable'
                ];
            }
        }

        return $inventory;
    }

    public function getCategoriesBySchool($schoolId)
    {
        $mockCategories = [
            '1' => ['DCP Package', 'Furniture'],
            '2' => ['Science Kit', 'DCP Package'],
            '3' => ['Furniture', 'Science Kit', 'Office Supplies']
        ];
        $categories = $mockCategories[$schoolId] ?? ['General Inventory'];
        return response()->json($categories);
    }

    public function viewAll(Request $request)
    {
        $categories = DB::table('categories')->orderBy('name')->get();
        $quadrants = DB::table('quadrants')->orderBy('name')->get();
        $classifications = DB::table('classifications')->orderBy('name')->get();

        // Data for Asset Source Tab
        $assetSources = DB::table('asset_sources as asrc')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('classifications', 'categories.classification_id', '=', 'classifications.id')
            ->join('acquisition_sources', 'asrc.acquisition_source_id', '=', 'acquisition_sources.id')
            ->select(
                'asrc.*',
                'items.name as item_name',
                'categories.name as category_name',
                'classifications.name as classification_name',
                'acquisition_sources.name as acquisition_source_name'
            )
            ->orderBy('asrc.created_at', 'desc')
            ->paginate(50, ['*'], 'source_page');

        // Data for Asset Assignment Tab
        $assetDistributions = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->leftJoin('custodians as c', 'ad.custodian_id', '=', 'c.id')
            ->leftJoin('offices', 'c.office_id', '=', 'offices.id')
            ->leftJoin('schools', 'c.school_id', '=', 'schools.school_id')
            ->leftJoin('districts', 'schools.district_id', '=', 'districts.id')
            ->leftJoin('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
            ->select(
                'ad.*',
                'items.name as item_name',
                'asrc.description as asset_description',
                'schools.name as office_school_name',
                'districts.name as district_name',
                'quadrants.name as quadrant_name'
            )
            ->orderBy('ad.created_at', 'desc')
            ->paginate(50, ['*'], 'dist_page');

        $inventoryJson   = json_encode($this->buildInventoryData());
        $categoriesJson  = json_encode($categories->values());
        $quadrantsJson   = json_encode($quadrants->values());

        return view('assets.view-all', compact(
            'assetSources', 'assetDistributions',
            'categories', 'quadrants', 'classifications',
            'inventoryJson', 'categoriesJson', 'quadrantsJson'
        ));
    }

    public function explorer()
    {
        $inventory = $this->buildInventoryData();
        return view('assets.asset-explorer', compact('inventory'));
    }

    public function history()
    {
        // Show assignment history from asset_assignments
        $records = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('custodians as c', 'ad.custodian_id', '=', 'c.id')
            ->leftJoin('offices', 'c.office_id', '=', 'offices.id')
            ->leftJoin('schools', 'c.school_id', '=', 'schools.school_id')
            ->select(
                'ad.id',
                'items.name as item_name',
                DB::raw('COALESCE(asrc.description, "General") as sub_item_name'),
                'categories.name as category',
                DB::raw('COALESCE(schools.name, offices.name, ad.location) as school'),
                DB::raw("'Assigned' as district"),
                DB::raw('1 as qty'),
                'ad.created_at as distributed_at'
            )
            ->orderByDesc('ad.created_at')
            ->get();

        $items = $records->map(function ($r) {
            return [
                'id' => $r->id,
                'item_name' => $r->item_name,
                'sub_item_name' => $r->sub_item_name ?? 'General',
                'category' => $r->category,
                'school' => $r->school,
                'district' => $r->district,
                'qty' => (int) $r->qty,
                'distributed_at' => $r->distributed_at,
            ];
        });

        return view('assets.asset-history', [
            'recordsJson' => json_encode($items->values()),
        ]);
    }

    public function lifecycle(Request $request)
    {
        $assets = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->join('acquisition_sources', 'asrc.acquisition_source_id', '=', 'acquisition_sources.id')
            ->leftJoin('custodians as c', 'ad.custodian_id', '=', 'c.id')
            ->leftJoin('offices', 'c.office_id', '=', 'offices.id')
            ->leftJoin('schools', 'c.school_id', '=', 'schools.school_id')
            ->leftJoin('procurement_modes as pm', 'asrc.procurement_mode_id', '=', 'pm.id')
            ->select(
                'ad.id',
                'ad.property_number',
                'ad.location',
                'ad.condition',
                'ad.acquisition_date',
                'asrc.acceptance_date',
                DB::raw('COALESCE(asrc.description, items.name) as description'),
                'asrc.asset_cost',
                'asrc.quantity',
                'pm.name as mode_of_acquisition',
                'acquisition_sources.name as source_name',
                'items.name as item_name',
                'categories.name as category_name',
                DB::raw('COALESCE(schools.name, offices.name, ad.location) as school_name')
            )
            ->orderByDesc('ad.created_at')
            ->get();
        
        return view('assets.asset-lifecycle', compact('assets'));
    }


    public function profile($id)
    {
        $asset = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->join('classifications', 'categories.classification_id', '=', 'classifications.id')
            ->join('acquisition_sources', 'asrc.acquisition_source_id', '=', 'acquisition_sources.id')
            ->leftJoin('custodians', 'ad.custodian_id', '=', 'custodians.id')
            ->leftJoin('offices', 'custodians.office_id', '=', 'offices.id')
            ->leftJoin('schools', 'custodians.school_id', '=', 'schools.school_id')
            ->leftJoin('procurement_modes as pm', 'asrc.procurement_mode_id', '=', 'pm.id')
            ->select(
                'ad.id',
                'ad.property_number',
                'ad.photo_path',
                'custodians.office_id',
                'ad.condition',
                DB::raw("NULL as nature_of_occupancy"),
                'ad.location',
                'ad.acquisition_date',
                'ad.custodian_id',
                DB::raw("'Region IX' as region"),
                DB::raw("'Division of Zamboanga City' as division"),
                DB::raw('COALESCE(schools.name, offices.name, ad.location) as office_school_name'),
                'offices.name as office_name',
                'schools.name as school_name',
                'asrc.id as asset_source_id',
                'asrc.acceptance_date',
                'asrc.description',
                'asrc.asset_cost',
                'asrc.quantity',
                'pm.name as mode_of_acquisition',
                'acquisition_sources.name as source_name',
                'acquisition_sources.id as acquisition_source_id',
                'items.name as item_name',
                'items.id as item_id',
                'categories.name as category_name',
                'categories.id as category_id',
                'classifications.name as classification_name',
                'classifications.id as classification_id',
                'custodians.first_name as custodian_first',
                'custodians.middle_name as custodian_middle',
                'custodians.last_name as custodian_last',
                'custodians.position as custodian_position',
                'custodians.contact_number as custodian_contact'
            )
            ->where('ad.id', $id)
            ->first();

        if (!$asset) {
            abort(404, 'Asset not found');
        }

        $classifications = DB::table('classifications')->orderBy('name')->get();
        $categories = DB::table('categories')->orderBy('name')->get();
        $items = DB::table('items')->orderBy('name')->get();
        $acquisitionSources = DB::table('acquisition_sources')->orderBy('name')->get();
        $custodians = DB::table('custodians')->orderBy('first_name')->get()->map(function($c) {
            $c->full_name = trim($c->first_name . ' ' . $c->middle_name . ' ' . $c->last_name);
            return $c;
        });
        $schools = DB::table('schools')->orderBy('name')->get();
        $offices = DB::table('offices')->orderBy('name')->get();

        // Generate timeline data
        $timeline = [
            [
                'date' => $asset->acceptance_date ?? 'N/A',
                'type' => 'Procurement',
                'user' => 'System Admin',
                'description' => 'Asset officially procured and registered into the database from ' . $asset->source_name
            ]
        ];
        
        // Fetch transfer history with office/school names
        $transfers = DB::table('asset_transfers')
            ->leftJoin('users', 'asset_transfers.authorized_by', '=', 'users.id')
            ->leftJoin('custodians as to_custodian', 'asset_transfers.to_custodian_id', '=', 'to_custodian.id')
            ->leftJoin('offices as from_off', 'asset_transfers.from_office_id', '=', 'from_off.id')
            ->leftJoin('schools as from_sch', 'from_off.school_id', '=', 'from_sch.id')
            ->leftJoin('offices as to_off', 'asset_transfers.to_office_id', '=', 'to_off.id')
            ->leftJoin('schools as to_sch', 'to_off.school_id', '=', 'to_sch.id')
            ->where('asset_assignment_id', $id)
            ->select(
                'asset_transfers.*',
                'users.name as user_name',
                'to_custodian.first_name', 'to_custodian.last_name',
                DB::raw('COALESCE(from_sch.name, from_off.name) as from_school_name'),
                DB::raw('COALESCE(to_sch.name, to_off.name) as to_school_name')
            )
            ->orderBy('asset_transfers.created_at', 'asc')
            ->get();

        if ($transfers->isEmpty() && !empty($asset->office_school_name) && $asset->office_school_name !== 'AMU Warehouse') {
            $timeline[] = [
                'date' => $asset->acquisition_date ?? 'N/A',
                'type' => 'Transfer',
                'user' => 'Property Officer',
                'description' => 'Deployed and assigned to ' . $asset->office_school_name
            ];
        } else {
            foreach ($transfers as $t) {
                $fromName = $t->from_school_name ?? 'AMU Warehouse';
                $toName = $t->to_school_name ?? 'AMU Warehouse';

                if ($t->transfer_type === 'Return') {
                    $desc = 'Returned from ' . $fromName . ' to AMU / Warehouse.';
                    if ($t->remarks) $desc .= ' Reason: ' . $t->remarks;
                } else {
                    $desc = 'Transferred from ' . $fromName . ' to ' . $toName;
                    $custName = trim(($t->first_name ?? '') . ' ' . ($t->last_name ?? ''));
                    if ($custName) $desc .= ' (Custodian: ' . $custName . ')';
                    if ($t->transfer_type === 'Temporary Borrow' && $t->return_date) {
                        $desc .= '. Borrowed until: ' . \Carbon\Carbon::parse($t->return_date)->format('F d, Y');
                    }
                }

                $timeline[] = [
                    'date' => $t->transfer_date ? \Carbon\Carbon::parse($t->transfer_date)->format('Y-m-d') : 'N/A',
                    'type' => in_array($t->transfer_type, ['Temporary Borrow', 'Return']) ? $t->transfer_type : 'Transfer',
                    'user' => $t->user_name ?? 'Property Officer',
                    'description' => $desc
                ];
            }
        }

        $documents = DB::table('asset_documents')->where('asset_distribution_id', $id)->orderByDesc('created_at')->get();

        return view('assets.profile', compact('asset', 'timeline', 'documents', 'classifications', 'categories', 'items', 'acquisitionSources', 'custodians', 'schools', 'offices'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->approved) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'classification_id' => 'required|string',
            'category_id' => 'required|string',
            'item_id' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string|max:1000',
            'property_number' => 'nullable|string|max:255',
            'asset_cost' => 'required|numeric|min:0',
            'acquisition_source_id' => 'required|exists:acquisition_sources,id',
            'mode_of_acquisition' => 'required|string|max:255',

            'custodian_id' => 'nullable|string',
            'custodian_position' => 'nullable|string|max:255',
            'custodian_contact' => 'nullable|string|max:255',
        ]);

        $asset = DB::table('asset_assignments')->where('id', $id)->first();
        if (!$asset) {
            return back()->with('error', 'Asset not found');
        }

        DB::transaction(function () use ($id, $asset, $validated, $request) {

            // 1. Resolve Classification
            $classInput = $validated['classification_id'];
            $className = is_numeric($classInput) 
                ? DB::table('classifications')->where('id', $classInput)->value('name') 
                : strtoupper(trim($classInput));

            if (!$className) $className = 'UNCATEGORIZED';

            $classification = DB::table('classifications')->where('name', $className)->first();
            $finalClassId = $classification ? $classification->id : DB::table('classifications')->insertGetId([
                'name' => $className,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Resolve Category
            $catInput = $validated['category_id'];
            $catName = is_numeric($catInput) 
                ? DB::table('categories')->where('id', $catInput)->value('name') 
                : strtoupper(trim($catInput));

            if (!$catName) $catName = 'UNCATEGORIZED';

            $category = DB::table('categories')
                ->where('name', $catName)
                ->where('classification_id', $finalClassId)
                ->first();
                
            $finalCatId = $category ? $category->id : DB::table('categories')->insertGetId([
                'classification_id' => $finalClassId,
                'name' => $catName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Resolve Item
            $itemInput = $validated['item_id'];
            $itemName = is_numeric($itemInput) 
                ? DB::table('items')->where('id', $itemInput)->value('name') 
                : strtoupper(trim($itemInput));

            if (!$itemName) $itemName = 'UNKNOWN ITEM';

            $item = DB::table('items')
                ->where('name', $itemName)
                ->where('category_id', $finalCatId)
                ->first();
                
            $finalItemId = $item ? $item->id : DB::table('items')->insertGetId([
                'category_id' => $finalCatId,
                'name' => $itemName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Resolve Custodian
            $custodianInput = $request->input('custodian_id');
            $finalCustodianId = $asset->custodian_id;

            if ($custodianInput) {
                if (is_numeric($custodianInput) && DB::table('custodians')->where('id', $custodianInput)->exists()) {
                    $finalCustodianId = $custodianInput;
                    DB::table('custodians')->where('id', $finalCustodianId)->update([
                        'position' => $request->input('custodian_position'),
                        'contact_number' => $request->input('custodian_contact'),
                        'updated_at' => now(),
                    ]);
                } else {
                    $parts = explode(' ', trim($custodianInput));
                    $firstName = $parts[0];
                    $lastName = count($parts) > 1 ? array_pop($parts) : '';
                    $middleName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';

                    $existing = DB::table('custodians')
                        ->where('first_name', $firstName)
                        ->where('last_name', $lastName)
                        ->first();

                    if ($existing) {
                        $finalCustodianId = $existing->id;
                        DB::table('custodians')->where('id', $finalCustodianId)->update([
                            'position' => $request->input('custodian_position'),
                            'contact_number' => $request->input('custodian_contact'),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $finalCustodianId = DB::table('custodians')->insertGetId([
                            'first_name' => $firstName,
                            'middle_name' => $middleName,
                            'last_name' => $lastName,
                            'position' => $request->input('custodian_position'),
                            'contact_number' => $request->input('custodian_contact'),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // 5. Update Asset Assignment
            $distUpdate = ['updated_at' => now()];
            if (array_key_exists('property_number', $validated)) $distUpdate['property_number'] = $validated['property_number'];
            if ($finalCustodianId) $distUpdate['custodian_id'] = $finalCustodianId;

            DB::table('asset_assignments')->where('id', $id)->update($distUpdate);

            // 6. Update Asset Source (Description, Item link, etc.)
            $modeName = trim($validated['mode_of_acquisition']);
            $modeId = DB::table('procurement_modes')->whereRaw('LOWER(name) = ?', [strtolower($modeName)])->value('id');
            if (!$modeId) {
                $modeId = DB::table('procurement_modes')->insertGetId([
                    'name' => $modeName,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::table('asset_sources')->where('id', $asset->asset_source_id)->update([
                'item_id' => $finalItemId,
                'description' => $validated['description'],
                'quantity' => $validated['quantity'],
                'asset_cost' => $validated['asset_cost'],
                'acquisition_source_id' => $validated['acquisition_source_id'],
                'procurement_mode_id' => $modeId,
                'updated_at' => now(),
            ]);
            
            /** @var \App\Models\User|null $user */
            $user = \Illuminate\Support\Facades\Auth::user();
            
            // Log the change
            DB::table('system_logs')->insert([
                'user' => $user ? $user->name : 'System',
                'action_type' => 'UPDATE',
                'module' => 'Assets',
                'activity' => 'Updated specifications for asset ID ' . $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Asset specifications updated successfully!');
    }

    public function transfer(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->approved) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'office_school_type' => 'nullable|string|max:255',
            'school_id' => 'nullable|string|max:255',
            'office_school_name' => 'nullable|string|max:255',
            'nature_of_occupancy' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'custodian_first' => 'nullable|string|max:255',
            'custodian_middle' => 'nullable|string|max:255',
            'custodian_last' => 'nullable|string|max:255',
            'custodian_position' => 'nullable|string|max:255',
            'custodian_contact' => 'nullable|string|max:255',
            'transfer_date' => 'nullable|date',
            'transfer_type' => 'nullable|string|max:255',
            'condition' => 'required|string|max:255',
            'return_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $asset = DB::table('asset_assignments')->where('id', $id)->first();
        if (!$asset) {
            return back()->with('error', 'Asset not found');
        }

        DB::transaction(function () use ($id, $asset, $validated, $request) {
            $finalCustodianId = null;

            // Handle Custodian Find or Create
            $firstName = trim($validated['custodian_first'] ?? '');
            $lastName = trim($validated['custodian_last'] ?? '');
            $middleName = trim($validated['custodian_middle'] ?? '');

            if (!empty($firstName) || !empty($lastName)) {
                $existing = DB::table('custodians')
                    ->where('first_name', $firstName)
                    ->where('last_name', $lastName)
                    ->first();

                if ($existing) {
                    $finalCustodianId = $existing->id;
                    DB::table('custodians')->where('id', $finalCustodianId)->update([
                        'position' => $validated['custodian_position'] ?? null,
                        'contact_number' => $validated['custodian_contact'] ?? null,
                        'updated_at' => now(),
                    ]);
                } else {
                    $finalCustodianId = DB::table('custodians')->insertGetId([
                        'first_name' => $firstName,
                        'middle_name' => $middleName,
                        'last_name' => $lastName,
                        'position' => $validated['custodian_position'] ?? null,
                        'contact_number' => $validated['custodian_contact'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Resolve current office_id from the custodian
            $currentOfficeId = null;
            if ($asset->custodian_id) {
                $currentCustodian = DB::table('custodians')->where('id', $asset->custodian_id)->first();
                if ($currentCustodian) {
                    $currentOfficeId = $currentCustodian->office_id;
                }
            }

            // Resolve target office_id
            $officeId = null;
            $schoolIdStr = $request->input('school_id');
            $officeSchoolName = $request->input('office_school_name');
            
            if ($schoolIdStr) {
                $school = DB::table('schools')->where('school_id', $schoolIdStr)->first();
                if ($school) {
                    $office = DB::table('offices')->where('school_id', $school->id)->first();
                    $officeId = $office ? $office->id : null;
                }
            } elseif ($officeSchoolName) {
                $school = DB::table('schools')->where('name', $officeSchoolName)->first();
                if ($school) {
                    $office = DB::table('offices')->where('school_id', $school->id)->first();
                    $officeId = $office ? $office->id : null;
                } else {
                    $office = DB::table('offices')->where('name', $officeSchoolName)->first();
                    $officeId = $office ? $office->id : null;
                }
            }

            $targetCustodianId = $finalCustodianId ?: $asset->custodian_id;
            if ($targetCustodianId) {
                DB::table('custodians')->where('id', $targetCustodianId)->update([
                    'office_id' => $officeId,
                    'school_id' => $schoolIdStr ?: null,
                    'updated_at' => now(),
                ]);
            }

            // Update Asset Assignment (nature_of_occupancy, school_id, office_id dropped)
            DB::table('asset_assignments')->where('id', $id)->update([
                'office_school_type' => $request->input('office_school_type') ?? '',
                'location' => $request->input('location') ?? '',
                'custodian_id' => $targetCustodianId,
                'condition' => $request->input('condition'),
                'updated_at' => now(),
            ]);

            // Log Transfer
            DB::table('asset_transfers')->insert([
                'asset_assignment_id' => $id,
                'from_office_id' => $currentOfficeId,
                'to_office_id' => $officeId,
                'from_custodian_id' => $asset->custodian_id,
                'to_custodian_id' => $targetCustodianId,
                'transfer_date' => $request->input('transfer_date', now()),
                'return_date' => $request->input('return_date'),
                'transfer_type' => $request->input('transfer_type', 'Permanent Reassignment'),
                'remarks' => $request->input('remarks'),
                'authorized_by' => Auth::id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Asset successfully transferred!');
    }

    public function returnAmu(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->approved) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'return_date' => 'required|date',
            'condition' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $asset = DB::table('asset_assignments')->where('id', $id)->first();
        if (!$asset) {
            return back()->with('error', 'Asset not found');
        }

        DB::transaction(function () use ($id, $asset, $validated) {
            // Resolve current office_id from the custodian
            $currentOfficeId = null;
            if ($asset->custodian_id) {
                $currentCustodian = DB::table('custodians')->where('id', $asset->custodian_id)->first();
                if ($currentCustodian) {
                    $currentOfficeId = $currentCustodian->office_id;
                }
            }

            // Log the return
            DB::table('asset_transfers')->insert([
                'asset_assignment_id' => $id,
                'from_office_id' => $currentOfficeId,
                'to_office_id' => null,
                'from_custodian_id' => $asset->custodian_id,
                'to_custodian_id' => null,
                'transfer_date' => $validated['return_date'],
                'transfer_type' => 'Return',
                'remarks' => $validated['remarks'],
                'authorized_by' => Auth::id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // We do NOT delete the assignment, we instead nullify fields so the asset retains its history 
            // but is no longer distributed (location = AMU Warehouse).
            DB::table('asset_assignments')->where('id', $id)->update([
                'custodian_id' => null,
                'office_school_type' => '',
                'location' => 'AMU Warehouse',
                'condition' => $validated['condition'],
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('assets.view_all')->with('success', 'Asset successfully returned to AMU / Warehouse!');
    }

    public function getSchoolAssets($id)
    {
        // Find the school and get its school_id string
        $school = DB::table('schools')->where('id', $id)->first();
        if (!$school) {
            return response()->json(['success' => false, 'assets' => []]);
        }

        $records = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('custodians as c', 'ad.custodian_id', '=', 'c.id')
            ->where('c.school_id', $school->school_id)
            ->select(
                'categories.name as category_name',
                'items.name as item_name',
                DB::raw('COALESCE(asrc.description, items.name) as sub_item_name'),
                DB::raw('1 as quantity')
            )
            ->orderBy('categories.name')
            ->orderBy('items.name')
            ->get();

        $assets = $records->map(function ($row) {
            return [
                'category' => $row->category_name,
                'item'     => $row->item_name,
                'sub_item' => $row->sub_item_name ?? 'General / Default',
                'quantity' => (int) $row->quantity
            ];
        });

        return response()->json(['success' => true, 'assets' => $assets]);
    }

    public function uploadPhoto(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->approved) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'photo' => 'required|image|max:5120',
        ]);

        $asset = DB::table('asset_assignments')->where('id', $id)->first();
        if (!$asset) {
            return back()->with('error', 'Asset not found');
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('assets', 'public');
            DB::table('asset_assignments')->where('id', $id)->update(['photo_path' => $path]);
            return back()->with('success', 'Photo updated successfully!');
        }

        return back()->with('error', 'No photo uploaded.');
    }

    public function removePhoto($id)
    {
        if (!Auth::check() || !Auth::user()->approved) {
            abort(403, 'Unauthorized action.');
        }

        $asset = DB::table('asset_assignments')->where('id', $id)->first();
        if ($asset && $asset->photo_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($asset->photo_path);
            DB::table('asset_assignments')->where('id', $id)->update(['photo_path' => null]);
            return back()->with('success', 'Photo removed successfully!');
        }
        return back()->with('error', 'No photo to remove.');
    }

    public function uploadDocument(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->approved) {
            abort(403, 'Unauthorized action.');
        }

        $file = $request->file('document') ?? $request->file('document_camera');

        if (!$file) {
            return back()->with('error', 'No document uploaded.');
        }

        $request->validate([
            'document' => 'nullable|file|max:10240',
            'document_camera' => 'nullable|file|max:10240',
        ]);

        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $path = $file->store('documents', 'public');

        DB::table('asset_documents')->insert([
            'asset_distribution_id' => $id,
            'file_name' => $fileName,
            'file_path' => $path,
            'file_size' => $fileSize,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Document uploaded successfully!');
    }

    public function removeDocument($docId)
    {
        if (!Auth::check() || !Auth::user()->approved) {
            abort(403, 'Unauthorized action.');
        }

        $doc = DB::table('asset_documents')->where('id', $docId)->first();
        if ($doc) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->file_path);
            DB::table('asset_documents')->where('id', $docId)->delete();
            return back()->with('success', 'Document removed successfully!');
        }
        return back()->with('error', 'Document not found.');
    }
}
```

## File: `app/Http/Controllers/ReportDownloadController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReportDownloadController extends Controller
{
    private function buildQuery(Request $request)
    {
        $type = $request->input('report_type');
        $filters = $request->input('filters', []);
        
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?: [];
        }

        $tab = $filters['tab'] ?? 'distribution';

        if ($tab === 'source') {
            $query = DB::table('asset_sources')
                ->leftJoin('items', 'asset_sources.item_id', '=', 'items.id')
                ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
                ->leftJoin('classifications', 'categories.classification_id', '=', 'classifications.id')
                ->leftJoin('acquisition_sources', 'asset_sources.acquisition_source_id', '=', 'acquisition_sources.id')
                ->leftJoin('procurement_modes as pm', 'asset_sources.procurement_mode_id', '=', 'pm.id')
                ->leftJoin('acquisition_contacts as ac', 'asset_sources.acquisition_contact_id', '=', 'ac.id')
                ->select(
                    'asset_sources.*',
                    'items.name as article',
                    'categories.name as category',
                    'classifications.name as classification',
                    'acquisition_sources.name as acq_source',
                    'pm.name as mode_of_acquisition',
                    'ac.name as source_personnel',
                    'ac.position as personnel_position',
                    DB::raw('(asset_sources.asset_cost * asset_sources.quantity) as acquisition_cost'),
                    DB::raw("'Region IX' as region"),
                    DB::raw("'Division of Zamboanga City' as division"),
                    DB::raw("NULL as office_school_type"),
                    DB::raw("NULL as school_id"),
                    DB::raw("NULL as location"),
                    DB::raw("NULL as nature_of_occupancy"),
                    DB::raw("NULL as location"),
                    DB::raw("NULL as property_number"),
                    DB::raw("NULL as acquisition_date")
                );
        } else {
            $query = DB::table('asset_assignments')
                ->leftJoin('asset_sources', 'asset_assignments.asset_source_id', '=', 'asset_sources.id')
                ->leftJoin('items', 'asset_sources.item_id', '=', 'items.id')
                ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
                ->leftJoin('classifications', 'categories.classification_id', '=', 'classifications.id')
                ->leftJoin('acquisition_sources', 'asset_sources.acquisition_source_id', '=', 'acquisition_sources.id')
                ->leftJoin('procurement_modes as pm', 'asset_sources.procurement_mode_id', '=', 'pm.id')
                ->leftJoin('acquisition_contacts as ac', 'asset_sources.acquisition_contact_id', '=', 'ac.id')
                ->leftJoin('custodians as cus', 'asset_assignments.custodian_id', '=', 'cus.id')
                ->leftJoin('offices', 'cus.office_id', '=', 'offices.id')
                ->leftJoin('schools', 'cus.school_id', '=', 'schools.school_id')
                ->select(
                    'asset_assignments.*',
                    DB::raw("'Region IX' as region"),
                    DB::raw("'Division of Zamboanga City' as division"),
                    'cus.school_id as school_id',
                    DB::raw('COALESCE(schools.name, offices.name, asset_assignments.location) as location'),
                    DB::raw('COALESCE(schools.name, offices.name, asset_assignments.location) as office_school_name'),
                    DB::raw('NULL as nature_of_occupancy'),
                    'asset_sources.description',
                    'asset_sources.unit_of_measurement',
                    'asset_sources.asset_cost',
                    'asset_sources.quantity',
                    'asset_sources.quantity as source_qty',
                    'asset_sources.estimated_useful_life',
                    'pm.name as mode_of_acquisition',
                    'ac.name as source_personnel',
                    'ac.position as personnel_position',
                    'asset_sources.acceptance_date',
                    'asset_sources.remarks',
                    'items.name as article',
                    'categories.name as category',
                    'classifications.name as classification',
                    'acquisition_sources.name as acq_source',
                    'cus.first_name as custodian_first_name',
                    'cus.middle_name as custodian_middle_name',
                    'cus.last_name as custodian_last_name',
                    'cus.position as custodian_position',
                    'cus.contact_number as custodian_contact_number'
                );
        }

        if ($type === 'RPCPPE') {
            $col = ($tab === 'source') ? 'asset_sources.asset_cost' : 'asset_assignments.acquisition_cost';
            $query->where($col, '>=', 50000);
        } elseif ($type === 'RPCSP') {
            $col = ($tab === 'source') ? 'asset_sources.asset_cost' : 'asset_assignments.acquisition_cost';
            $query->where($col, '<', 50000);
        }

        if (!empty($filters['classification'])) {
            $query->where('classifications.name', $filters['classification']);
        }
        if (!empty($filters['category'])) {
            $query->where('categories.name', $filters['category']);
        }
        if (!empty($filters['article'])) {
            $query->where('items.name', $filters['article']);
        }
        if (!empty($filters['schoolName']) && $tab === 'distribution') {
            $query->where('asset_assignments.location', $filters['schoolName']);
        }
        if (!empty($filters['source'])) {
            $query->where('acquisition_sources.name', $filters['source']);
        }
        if (!empty($filters['mode'])) {
            $query->where('pm.name', $filters['mode']);
        }
        if (!empty($filters['dateAcquired'])) {
            $query->whereDate('asset_sources.acceptance_date', $filters['dateAcquired']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search, $tab) {
                $q->where('asset_sources.description', 'LIKE', "%$search%")
                  ->orWhere('items.name', 'LIKE', "%$search%");
                if ($tab === 'distribution') {
                    $q->orWhere('asset_assignments.property_number', 'LIKE', "%$search%");
                }
            });
        }

        // Sorting by Cost
        $sortCost = $filters['sortCost'] ?? null;
        if ($sortCost === 'low_to_high') {
            $col = ($tab === 'source') ? 'asset_sources.asset_cost' : 'asset_assignments.acquisition_cost';
            $query->orderBy($col, 'asc');
        } elseif ($sortCost === 'high_to_low') {
            $col = ($tab === 'source') ? 'asset_sources.asset_cost' : 'asset_assignments.acquisition_cost';
            $query->orderBy($col, 'desc');
        } else {
            $query->orderBy($tab === 'source' ? 'asset_sources.id' : 'asset_assignments.id', 'asc');
        }

        // Data Integrity: Empty Column check
        if (!empty($filters['emptyCol'])) {
            $eCol = $filters['emptyCol'];
            $dbCol = null;
            
            if ($eCol === 'article') $dbCol = 'items.name';
            elseif ($eCol === 'category') $dbCol = 'categories.name';
            elseif ($eCol === 'classification') $dbCol = 'classifications.name';
            elseif ($eCol === 'description') $dbCol = 'asset_sources.description';
            elseif ($eCol === 'unit_of_measurement') $dbCol = 'asset_sources.unit_of_measurement';
            elseif ($eCol === 'acq_source') $dbCol = 'acquisition_sources.name';
            elseif ($eCol === 'mode_of_acquisition') $dbCol = 'pm.name';
            elseif ($eCol === 'acceptance_date') $dbCol = 'asset_sources.acceptance_date';
            
            // Distribution-specific columns
            if ($tab === 'distribution') {
                if ($eCol === 'property_number') $dbCol = 'asset_assignments.property_number';
                elseif ($eCol === 'school_id') $dbCol = 'cus.school_id';
                elseif ($eCol === 'school_name') $dbCol = 'asset_assignments.location';
                elseif ($eCol === 'occupancy') $dbCol = DB::raw('NULL');
                elseif ($eCol === 'location') $dbCol = 'asset_assignments.location';
                elseif ($eCol === 'acquisition_date') $dbCol = 'asset_assignments.acquisition_date';
            }
            
            if ($dbCol) {
                $query->where(function($q) use ($dbCol, $eCol) {
                    $q->whereNull($dbCol)
                      ->orWhere($dbCol, '')
                      ->orWhere($dbCol, '0')
                      ->orWhere($dbCol, 'unclassified')
                      ->orWhere($dbCol, 'uncategorized')
                      ->orWhere($dbCol, 'Unclassified')
                      ->orWhere($dbCol, 'Uncategorized');
                });
            }
        }

        return $query;
    }

    public function getPreview(Request $request)
    {
        $query = $this->buildQuery($request);
        $rows = $query->limit(500)->get();
        return response()->json(['rows' => $rows]);
    }

    public function getAssetSuggestions(Request $request)
    {
        $search = $request->input('q');
        $tab = $request->input('tab', 'distribution');
        
        if (empty($search)) return response()->json([]);

        if ($tab === 'source') {
            $results = DB::table('asset_sources')
                ->leftJoin('items', 'asset_sources.item_id', '=', 'items.id')
                ->where('asset_sources.description', 'LIKE', "%$search%")
                ->orWhere('items.name', 'LIKE', "%$search%")
                ->select(DB::raw('COALESCE(asset_sources.description, items.name) as suggestion'))
                ->distinct()
                ->limit(10)
                ->pluck('suggestion');
        } else {
            $results = DB::table('asset_assignments')
                ->leftJoin('asset_sources', 'asset_assignments.asset_source_id', '=', 'asset_sources.id')
                ->leftJoin('items', 'asset_sources.item_id', '=', 'items.id')
                ->where('asset_assignments.property_number', 'LIKE', "%$search%")
                ->orWhere('asset_sources.description', 'LIKE', "%$search%")
                ->orWhere('items.name', 'LIKE', "%$search%")
                ->select(DB::raw('COALESCE(asset_assignments.property_number, asset_sources.description, items.name) as suggestion'))
                ->distinct()
                ->limit(10)
                ->pluck('suggestion');
        }

        return response()->json($results);
    }

    public function getEditPreview(Request $request)
    {
        $query = $this->buildQuery($request);
        // Explicitly select the IDs needed for updating, overriding any conflicts
        $query->addSelect(
            'asset_assignments.id as dist_id',
            'asset_sources.id as src_id',
            'items.id as item_id',
            'acquisition_sources.id as acq_source_id'
        );
        $rows = $query->limit(500)->get();
        return response()->json(['rows' => $rows]);
    }

    public function getFilterOptions(Request $request)
    {
        $type = $request->input('report_type');

        $baseQuery = DB::table('asset_sources')
            ->leftJoin('asset_assignments', 'asset_sources.id', '=', 'asset_assignments.asset_source_id')
            ->leftJoin('items', 'asset_sources.item_id', '=', 'items.id')
            ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('classifications', 'categories.classification_id', '=', 'classifications.id')
            ->leftJoin('acquisition_sources', 'asset_sources.acquisition_source_id', '=', 'acquisition_sources.id')
            ->leftJoin('procurement_modes as pm', 'asset_sources.procurement_mode_id', '=', 'pm.id');

        if ($type === 'RPCPPE') {
            $baseQuery->where('asset_sources.asset_cost', '>=', 50000);
        } elseif ($type === 'RPCSP') {
            $baseQuery->where('asset_sources.asset_cost', '<', 50000);
        }

        $classifications = (clone $baseQuery)->whereNotNull('classifications.name')->pluck('classifications.name')->unique()->sort()->values();
        $categories = (clone $baseQuery)->whereNotNull('categories.name')->pluck('categories.name')->unique()->sort()->values();
        $items = (clone $baseQuery)->whereNotNull('items.name')->pluck('items.name')->unique()->sort()->values();
        $schools = (clone $baseQuery)->whereNotNull('asset_assignments.location')->where('asset_assignments.location', '!=', '')->pluck('asset_assignments.location')->unique()->sort()->values();
        $sources = (clone $baseQuery)->whereNotNull('acquisition_sources.name')->pluck('acquisition_sources.name')->unique()->sort()->values();
        $modes = (clone $baseQuery)->whereNotNull('pm.name')->pluck('pm.name')->unique()->sort()->values();

        return response()->json([
            'classifications' => $classifications,
            'categories' => $categories,
            'items' => $items,
            'schools' => $schools,
            'sources' => $sources,
            'modes' => $modes
        ]);
    }

    public function download(Request $request)
    {
        $type = $request->input('report_type');
        if (!in_array($type, ['RPCPPE', 'RPCSP', 'PIF'])) {
            return back()->withErrors('Invalid report type.');
        }

        $query = $this->buildQuery($request);
        $rows = $query->get();

        $templatePath = base_path('../' . $type . '.xlsx');
        if (!file_exists($templatePath)) {
            return back()->withErrors("Template file {$type}.xlsx not found.");
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Dynamic Classification/Category Header for RPCPPE/RPCSP
        $filters = $request->input('filters', []);
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?: [];
        }
        $classification = $filters['classification'] ?? null;
        $category = $filters['category'] ?? null;
        
        $reportTitle = $category ?: $classification;

        if ($reportTitle && ($type === 'RPCPPE' || $type === 'RPCSP')) {
            $sheet->setCellValue('B4', $reportTitle);
            $sheet->getStyle('B4')->getFont()
                ->setBold(true)
                ->setItalic(true)
                ->setUnderline(\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE)
                ->getColor()->setRGB('FF0000');
        }

        if ($type === 'PIF') {
            // Update Agency Name and Address for PIF
            $sheet->setCellValue('A2', 'Department of Education - Division of Zamboanga City');
            $sheet->setCellValue('A3', 'Baliwasan Chico Road, Zamboanga City');
            // Update "As of" Date dynamically to current creation date
            $sheet->setCellValue('A5', 'As of ' . date('F d, Y'));
        }

        $startRow = 11;
        $signatureRow = null;
        if ($type === 'RPCPPE') {
            $startRow = 15;
            $signatureRow = 22;
        } elseif ($type === 'RPCSP') {
            $startRow = 15;
            $signatureRow = 44;
        }
        
        $currentRow = $startRow;

        foreach ($rows as $row) {
            if ($type === 'RPCPPE' || $type === 'RPCSP') {
                if ($currentRow >= $signatureRow) {
                    $sheet->insertNewRowBefore($currentRow, 1);
                    $signatureRow++;
                }
                
                // Force duplication of row 15 style to EVERY row (including pre-existing 16-21) to fix template inconsistencies
                if ($currentRow > $startRow) {
                    $baseRow = 15;
                    for ($col = 'A'; $col <= 'N'; $col++) {
                        $style = $sheet->getStyle($col . $baseRow);
                        $sheet->duplicateStyle($style, $col . $currentRow);
                    }
                    $baseHeight = $sheet->getRowDimension($baseRow)->getRowHeight();
                    if ($baseHeight != -1) {
                        $sheet->getRowDimension($currentRow)->setRowHeight($baseHeight);
                    }
                }

                $sheet->setCellValue('B' . $currentRow, $row->article);
                $sheet->setCellValue('C' . $currentRow, $row->description);
                $sheet->setCellValue('D' . $currentRow, $row->property_number);
                $sheet->setCellValue('E' . $currentRow, $row->unit_of_measurement);
                $sheet->setCellValue('F' . $currentRow, $row->asset_cost);
                $sheet->setCellValue('G' . $currentRow, $row->quantity);
                $sheet->setCellValue('H' . $currentRow, $row->quantity);
                $sheet->setCellValue('I' . $currentRow, ''); // Shortage/Overage
                $sheet->setCellValue('J' . $currentRow, ''); // Shortage/Overage
                $sheet->setCellValue('K' . $currentRow, $row->remarks);
            } else {
                // Asset PIF Mapping (24 columns)
                if ($type === 'PIF' && $currentRow > $startRow) {
                    $baseRow = 11;
                    for ($col = 'A'; $col <= 'X'; $col++) {
                        $style = $sheet->getStyle($col . $baseRow);
                        $sheet->duplicateStyle($style, $col . $currentRow);
                    }
                    $baseHeight = $sheet->getRowDimension($baseRow)->getRowHeight();
                    if ($baseHeight != -1) {
                        $sheet->getRowDimension($currentRow)->setRowHeight($baseHeight);
                    }
                }
                
                $sheet->setCellValue('A' . $currentRow, $row->region);
                $sheet->setCellValue('B' . $currentRow, $row->division);
                $sheet->setCellValue('C' . $currentRow, $row->office_school_type);
                $sheet->setCellValue('D' . $currentRow, $row->school_id);
                $sheet->setCellValue('E' . $currentRow, $row->location);
                $sheet->setCellValue('F' . $currentRow, $row->classification);
                $sheet->setCellValue('G' . $currentRow, $row->category);
                $sheet->setCellValue('H' . $currentRow, $row->article);
                $sheet->setCellValue('I' . $currentRow, $row->description);
                $sheet->setCellValue('J' . $currentRow, $row->unit_of_measurement);
                $sheet->setCellValue('K' . $currentRow, $row->asset_cost);
                $sheet->setCellValue('L' . $currentRow, $row->quantity);
                $sheet->setCellValue('M' . $currentRow, $row->estimated_useful_life);
                $sheet->setCellValue('N' . $currentRow, $row->property_number);
                $sheet->setCellValue('O' . $currentRow, $row->nature_of_occupancy);
                $sheet->setCellValue('P' . $currentRow, $row->location);
                $sheet->setCellValue('Q' . $currentRow, $row->acq_source);
                $sheet->setCellValue('R' . $currentRow, $row->mode_of_acquisition);
                $sheet->setCellValue('S' . $currentRow, $row->source_personnel);
                $sheet->setCellValue('T' . $currentRow, $row->personnel_position);
                $sheet->setCellValue('U' . $currentRow, $row->acquisition_cost); // Total Acquisition Cost
                $sheet->setCellValue('V' . $currentRow, $row->acceptance_date);
                $sheet->setCellValue('W' . $currentRow, $row->acquisition_date);
                $sheet->setCellValue('X' . $currentRow, $row->remarks);
            }
            
            $currentRow++;
        }

        // Delete excess rows between the last data row and the signature row
        if (($type === 'RPCPPE' || $type === 'RPCSP') && $signatureRow !== null) {
            if ($currentRow < $signatureRow) {
                $countToDelete = $signatureRow - $currentRow;
                $sheet->removeRow($currentRow, $countToDelete);
                $signatureRow = $currentRow; // Update it as we've shifted it up
            }
        }

        // Ensure at least 1 blank row below the last asset row before signatories
        if (($type === 'RPCPPE' || $type === 'RPCSP') && $signatureRow !== null) {
            if ($currentRow >= $signatureRow) {
                $sheet->insertNewRowBefore($signatureRow, 1);
                // The new row is blank, but we might want to clear its height or keep it standard
                $sheet->getRowDimension($signatureRow)->setRowHeight(20); 
                $signatureRow++;
            }
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $fileName = $type . '_Report_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function buildBuildingsQuery(Request $request)
    {
        $filters = $request->input('filters', []);
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?: [];
        }

        $query = DB::table('building_records')
            ->leftJoin('schools', 'building_records.school_id', '=', 'schools.id')
            ->leftJoin('building_specs', 'building_records.building_spec_id', '=', 'building_specs.id')
            ->leftJoin('building_types', 'building_specs.building_type_id', '=', 'building_types.id')
            ->leftJoin('building_classifications', 'building_types.building_classification_id', '=', 'building_classifications.id')
            ->select(
                'building_records.*',
                'schools.school_id as school_identifier',
                'schools.name as office_name',
                'building_specs.storeys',
                'building_specs.classrooms',
                'building_specs.description',
                'building_types.name as article',
                'building_classifications.name as classification'
            );

        if (!empty($filters['classification'])) {
            $classifications = is_array($filters['classification']) ? $filters['classification'] : [$filters['classification']];
            $query->whereIn('building_classifications.name', $classifications);
        }
        if (!empty($filters['officeType'])) {
            $types = is_array($filters['officeType']) ? $filters['officeType'] : [$filters['officeType']];
            $query->whereIn('building_records.office_type', $types);
        }
        if (!empty($filters['schoolName'])) {
            $query->where(function($q) use ($filters) {
                $q->where('schools.name', 'LIKE', '%' . $filters['schoolName'] . '%')
                  ->orWhere('building_records.property_number', 'LIKE', '%' . $filters['schoolName'] . '%');
            });
        }
        if (!empty($filters['location'])) {
            $locs = is_array($filters['location']) ? $filters['location'] : [$filters['location']];
            $query->whereIn('building_records.location', $locs);
        }
        if (!empty($filters['dateAcquired'])) {
            $query->whereDate('building_records.acquisition_date', $filters['dateAcquired']);
        }

        // Data Integrity: Empty Column check
        if (!empty($filters['emptyCol'])) {
            $eCol = $filters['emptyCol'];
            $dbCol = null;
            
            // Map frontend key to DB column
            $mapping = [
                'region' => 'region',
                'division' => 'division',
                'office_type' => 'office_type',
                'school_identifier' => 'school_identifier',
                'office_name' => 'office_name',
                'address' => 'address',
                'storeys' => 'building_specs.storeys',
                'classrooms' => 'building_specs.classrooms',
                'article' => 'building_types.name',
                'description' => 'building_specs.description',
                'classification' => 'building_classifications.name',
                'occupancy_nature' => 'occupancy_nature',
                'location' => 'location',
                'date_constructed' => 'date_constructed',
                'acquisition_date' => 'acquisition_date',
                'property_number' => 'property_number',
                'acquisition_cost' => 'acquisition_cost',
                'estimated_useful_life' => 'estimated_useful_life',
                'appraised_value' => 'appraised_value',
                'appraisal_date' => 'appraisal_date',
            ];

            $dbCol = $mapping[$eCol] ?? null;
            
            if ($dbCol) {
                $query->where(function($q) use ($dbCol) {
                    $q->whereNull($dbCol)
                      ->orWhere($dbCol, '')
                      ->orWhere($dbCol, '0')
                      ->orWhere($dbCol, '0.00')
                      ->orWhere($dbCol, 'unclassified')
                      ->orWhere($dbCol, 'uncategorized')
                      ->orWhere($dbCol, 'Unclassified')
                      ->orWhere($dbCol, 'Uncategorized');
                });
            }
        }

        // Sorting by Cost
        $sortCost = $filters['sortCost'] ?? null;
        if ($sortCost === 'low_to_high') {
            $query->orderBy('acquisition_cost', 'asc');
        } elseif ($sortCost === 'high_to_low') {
            $query->orderBy('acquisition_cost', 'desc');
        } else {
            $query->orderBy('id', 'asc');
        }

        return $query;
    }

    public function getBuildingsPreview(Request $request)
    {
        $query = $this->buildBuildingsQuery($request);
        $rows = $query->limit(500)->get();
        return response()->json(['rows' => $rows]);
    }

    public function getBuildingsFilterOptions(Request $request)
    {
        $baseQuery = DB::table('building_records')
            ->leftJoin('schools', 'building_records.school_id', '=', 'schools.id')
            ->leftJoin('building_specs', 'building_records.building_spec_id', '=', 'building_specs.id')
            ->leftJoin('building_types', 'building_specs.building_type_id', '=', 'building_types.id')
            ->leftJoin('building_classifications', 'building_types.building_classification_id', '=', 'building_classifications.id');

        $classifications = (clone $baseQuery)->whereNotNull('building_classifications.name')->where('building_classifications.name', '!=', '')->pluck('building_classifications.name')->unique()->sort()->values();
        $office_types    = (clone $baseQuery)->whereNotNull('building_records.office_type')->where('building_records.office_type', '!=', '')->pluck('building_records.office_type')->unique()->sort()->values();
        $schools         = (clone $baseQuery)->whereNotNull('schools.name')->where('schools.name', '!=', '')->pluck('schools.name')->unique()->sort()->values();
        $articles        = (clone $baseQuery)->whereNotNull('building_types.name')->where('building_types.name', '!=', '')->pluck('building_types.name')->unique()->sort()->values();
        $occupancies     = (clone $baseQuery)->whereNotNull('building_records.occupancy_nature')->where('building_records.occupancy_nature', '!=', '')->pluck('building_records.occupancy_nature')->unique()->sort()->values();
        $locations       = (clone $baseQuery)->whereNotNull('building_records.location')->where('building_records.location', '!=', '')->pluck('building_records.location')->unique()->sort()->values();

        return response()->json([
            'classifications' => $classifications,
            'office_types'    => $office_types,
            'schools'         => $schools,
            'articles'        => $articles,
            'occupancies'     => $occupancies,
            'locations'       => $locations,
            // legacy keys kept for backward compat
            'officeTypes'     => $office_types,
        ]);
    }


    public function getSchoolsPreview(Request $request)
    {
        $filters = $request->input('filters', []);
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?: [];
        }

        $query = DB::table('schools')
            ->leftJoin('districts', 'schools.district_id', '=', 'districts.id')
            ->leftJoin('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
            ->select(
                'schools.*',
                'districts.name as district_name',
                'quadrants.name as quadrant_name'
            )
            ->addSelect([
                'total_bldg_cost' => DB::table('building_records')
                    ->whereColumn('school_id', 'schools.id')
                    ->selectRaw('COALESCE(SUM(acquisition_cost), 0)'),
                'total_ppe_cost' => DB::table('asset_assignments')
                    ->leftJoin('custodians', 'asset_assignments.custodian_id', '=', 'custodians.id')
                    ->where(function($q) {
                        $q->whereColumn('asset_assignments.location', 'schools.name')
                          ->orWhereColumn('custodians.school_id', 'schools.school_id');
                    })
                    ->where('acquisition_cost', '>=', 50000)
                    ->selectRaw('COALESCE(SUM(acquisition_cost), 0)'),
                'total_semi_ppe_cost' => DB::table('asset_assignments')
                    ->leftJoin('custodians', 'asset_assignments.custodian_id', '=', 'custodians.id')
                    ->where(function($q) {
                        $q->whereColumn('asset_assignments.location', 'schools.name')
                          ->orWhereColumn('custodians.school_id', 'schools.school_id');
                    })
                    ->where('acquisition_cost', '<', 50000)
                    ->selectRaw('COALESCE(SUM(acquisition_cost), 0)'),
            ]);

        if (!empty($filters['quadrant'])) {
            $quads = is_array($filters['quadrant']) ? $filters['quadrant'] : [$filters['quadrant']];
            $query->whereIn('quadrants.name', $quads);
        }
        if (!empty($filters['district'])) {
            $districts = is_array($filters['district']) ? $filters['district'] : [$filters['district']];
            $query->whereIn('districts.name', $districts);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('schools.school_id', 'LIKE', "%$search%")
                  ->orWhere('schools.name', 'LIKE', "%$search%")
                  ->orWhereRaw("CONCAT_WS(' - ', schools.school_id, schools.name) LIKE ?", ["%$search%"]);
            });
        }

        // Sorting
        $sort = $filters['sort'] ?? null;
        if ($sort === 'name_asc') {
            $query->orderBy('schools.name', 'asc');
        } elseif ($sort === 'name_desc') {
            $query->orderBy('schools.name', 'desc');
        } elseif ($sort === 'id_asc') {
            $query->orderBy('schools.school_id', 'asc');
        } elseif ($sort === 'id_desc') {
            $query->orderBy('schools.school_id', 'desc');
        } elseif ($sort === 'cost_asc') {
            $query->orderByRaw('(total_bldg_cost + total_ppe_cost + total_semi_ppe_cost) asc');
        } elseif ($sort === 'cost_desc') {
            $query->orderByRaw('(total_bldg_cost + total_ppe_cost + total_semi_ppe_cost) desc');
        } elseif ($sort === 'bldg_asc') {
            $query->orderBy('total_bldg_cost', 'asc');
        } elseif ($sort === 'bldg_desc') {
            $query->orderBy('total_bldg_cost', 'desc');
        } elseif ($sort === 'ppe_asc') {
            $query->orderBy('total_ppe_cost', 'asc');
        } elseif ($sort === 'ppe_desc') {
            $query->orderBy('total_ppe_cost', 'desc');
        } elseif ($sort === 'semi_asc') {
            $query->orderBy('total_semi_ppe_cost', 'asc');
        } elseif ($sort === 'semi_desc') {
            $query->orderBy('total_semi_ppe_cost', 'desc');
        } else {
            $query->orderBy('schools.name', 'asc');
        }

        $rows = $query->get();
        return response()->json(['rows' => $rows]);
    }

    public function getSchoolsFilterOptions(Request $request)
    {
        $quadrant = $request->input('quadrant');
        
        $districtQuery = DB::table('districts');
        if ($quadrant) {
            $districtQuery->join('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
                         ->where('quadrants.name', $quadrant);
        }
        
        $districts = $districtQuery->whereNotNull('districts.name')
                                  ->where('districts.name', '!=', '')
                                  ->pluck('districts.name')
                                  ->map(function($name) { return trim($name); })
                                  ->unique()
                                  ->sort()
                                  ->values();
        $quadrants = DB::table('quadrants')->pluck('name')->unique()->sort()->values();

        // For search autocomplete
        $allSchools = DB::table('schools')
            ->select('school_id', 'name')
            ->get()
            ->map(function($s) {
                return "{$s->school_id} - {$s->name}";
            });

        return response()->json([
            'districts' => $districts,
            'quadrants' => $quadrants,
            'allSchools' => $allSchools
        ]);
    }

    public function getOfficesPreview(Request $request)
    {
        $filters = $request->input('filters', []);
        if (is_string($filters)) { $filters = json_decode($filters, true) ?: []; }

        $query = DB::table('offices')
            ->leftJoin('schools', 'offices.school_id', '=', 'schools.id')
            ->select(
                'offices.id',
                'offices.name',
                'offices.office_code',
                'offices.office_code as type',
                'offices.room_number',
                'schools.name as location',
                'schools.name as school_name'
            )
            ->addSelect([
                'total_ppe_cost' => DB::table('asset_assignments as ad2')
                    ->join('asset_sources as asrc2', 'ad2.asset_source_id', '=', 'asrc2.id')
                    ->join('custodians as c2', 'ad2.custodian_id', '=', 'c2.id')
                    ->whereColumn('c2.office_id', 'offices.id')
                    ->where('asrc2.asset_cost', '>=', 50000)
                    ->selectRaw('COALESCE(SUM(asrc2.asset_cost), 0)'),
                'total_semi_ppe_cost' => DB::table('asset_assignments as ad3')
                    ->join('asset_sources as asrc3', 'ad3.asset_source_id', '=', 'asrc3.id')
                    ->join('custodians as c3', 'ad3.custodian_id', '=', 'c3.id')
                    ->whereColumn('c3.office_id', 'offices.id')
                    ->where('asrc3.asset_cost', '<', 50000)
                    ->selectRaw('COALESCE(SUM(asrc3.asset_cost), 0)'),
                'total_assets' => DB::table('asset_assignments as ad4')
                    ->join('custodians as c4', 'ad4.custodian_id', '=', 'c4.id')
                    ->whereColumn('c4.office_id', 'offices.id')
                    ->selectRaw('COUNT(*)'),
            ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('offices.name', 'LIKE', "%$search%")
                  ->orWhere('offices.office_code', 'LIKE', "%$search%")
                  ->orWhere('schools.name', 'LIKE', "%$search%");
            });
        }

        $rows = $query->orderBy('offices.name')->get();
        return response()->json(['rows' => $rows]);
    }

    public function getOfficesFilterOptions(Request $request)
    {
        $schools = DB::table('schools')->pluck('name')->unique()->sort()->values();
        return response()->json([
            'schools' => $schools
        ]);
    }

    public function getCustodiansPreview(Request $request)
    {
        $filters = $request->input('filters', []);
        if (is_string($filters)) { $filters = json_decode($filters, true) ?: []; }

        $query = DB::table('custodians')
            ->select(
                'custodians.*'
            )
            ->addSelect([
                'total_assets' => DB::table('asset_assignments')
                    ->whereColumn('custodian_id', 'custodians.id')
                    ->selectRaw('COUNT(*)'),
                'total_value' => DB::table('asset_assignments as ad')
                    ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
                    ->whereColumn('ad.custodian_id', 'custodians.id')
                    ->selectRaw('COALESCE(SUM(asrc.asset_cost), 0)'),
            ]);

        if (!empty($filters['status'])) {
            $query->where('custodians.status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%$search%")
                  ->orWhere('last_name', 'LIKE', "%$search%")
                  ->orWhere('employee_id', 'LIKE', "%$search%")
                  ->orWhere('position', 'LIKE', "%$search%");
            });
        }

        $rows = $query->orderBy('last_name')->get();
        return response()->json(['rows' => $rows]);
    }

    public function getCustodiansFilterOptions(Request $request)
    {
        $positions = DB::table('custodians')->distinct()->pluck('position')->filter()->values();
        $statuses = DB::table('custodians')->distinct()->pluck('status')->filter()->values();

        return response()->json([
            'positions' => $positions,
            'statuses' => $statuses
        ]);
    }
}


```

## File: `app/Http/Controllers/BuildingController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BuildingRecord;

class BuildingController extends Controller
{
    public function profile($id)
    {
        $building = DB::table('building_records as br')
            ->join('schools', 'br.school_id', '=', 'schools.id')
            ->join('building_specs as bs', 'br.building_spec_id', '=', 'bs.id')
            ->join('building_types as bt', 'bs.building_type_id', '=', 'bt.id')
            ->join('building_classifications as bc', 'bt.building_classification_id', '=', 'bc.id')
            ->leftJoin('districts', 'schools.district_id', '=', 'districts.id')
            ->select(
                'br.*',
                'schools.name as school_name',
                'schools.school_id as school_identifier',
                'districts.name as district_name',
                'bs.description as spec_description',
                'bs.storeys',
                'bs.classrooms',
                'bt.name as type_name',
                'bc.name as classification_name'
            )
            ->where('br.id', $id)
            ->first();

        if (!$building) {
            abort(404, 'Building not found');
        }

        $classifications = DB::table('building_classifications')->select('id', 'name')->orderBy('name')->get();
        $types = DB::table('building_types')->select('id', 'name')->orderBy('name')->get();

        // Generate dummy timeline data
        $timeline = [
            [
                'date' => $building->date_constructed ?? 'N/A',
                'type' => 'Construction',
                'description' => 'Original construction completed.',
                'user' => 'System'
            ],
            [
                'date' => $building->acquisition_date ?? 'N/A',
                'type' => 'Recording',
                'description' => 'Building officially recorded in the inventory.',
                'user' => 'Admin'
            ]
        ];

        $documents = collect(); // empty for now

        return view('buildings.profile', compact('building', 'timeline', 'documents', 'classifications', 'types'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->check() || !auth()->user()->approved) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'classification' => 'required|string|max:255',
            'type_name' => 'required|string|max:255',
            'occupancy_nature' => 'nullable|string|max:255',
            'storeys' => 'required|integer|min:1',
            'classrooms' => 'required|integer|min:0',
            'property_number' => 'nullable|string|max:255',
            'date_constructed' => 'nullable|date',
            'acquisition_cost' => 'nullable|numeric|min:0',
            'estimated_useful_life' => 'nullable|integer|min:0',
            'appraised_value' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $building = DB::table('building_records')->where('id', $id)->first();
        if (!$building) {
            return back()->with('error', 'Building not found');
        }

        DB::transaction(function () use ($id, $building, $validated, $request) {
            // 1. Resolve Classification
            $classInput = trim($validated['classification']);
            $classification = DB::table('building_classifications')
                ->whereRaw('LOWER(name) = ?', [strtolower($classInput)])
                ->first();
            $finalClassId = $classification ? $classification->id : DB::table('building_classifications')->insertGetId([
                'name' => strtoupper($classInput),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Resolve Type
            $typeInput = trim($validated['type_name']);
            $type = DB::table('building_types')
                ->whereRaw('LOWER(name) = ?', [strtolower($typeInput)])
                ->where('building_classification_id', $finalClassId)
                ->first();
            $finalTypeId = $type ? $type->id : DB::table('building_types')->insertGetId([
                'building_classification_id' => $finalClassId,
                'name' => strtoupper($typeInput),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Resolve Spec
            $storeys = intval($validated['storeys']);
            $classrooms = intval($validated['classrooms']);
            
            $spec = DB::table('building_specs')
                ->where('building_type_id', $finalTypeId)
                ->where('storeys', $storeys)
                ->where('classrooms', $classrooms)
                ->first();

            $finalSpecId = $spec ? $spec->id : DB::table('building_specs')->insertGetId([
                'building_type_id' => $finalTypeId,
                'storeys' => $storeys,
                'classrooms' => $classrooms,
                'description' => "{$storeys} STOREY - {$classrooms} CLASSROOM " . strtoupper($typeInput),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Update Building Record
            DB::table('building_records')->where('id', $id)->update([
                'building_spec_id' => $finalSpecId,
                'occupancy_nature' => $validated['occupancy_nature'],
                'property_number' => $validated['property_number'],
                'date_constructed' => $validated['date_constructed'],
                'acquisition_cost' => $validated['acquisition_cost'],
                'estimated_useful_life' => $validated['estimated_useful_life'],
                'appraised_value' => $validated['appraised_value'],
                'remarks' => $validated['remarks'],
                'updated_at' => now(),
            ]);

            /** @var \App\Models\User|null $user */
            $user = auth()->user();

            // Log the activity
            DB::table('system_logs')->insert([
                'user' => $user ? $user->name : 'System',
                'action_type' => 'UPDATE',
                'module' => 'Buildings',
                'activity' => 'Updated building record ID ' . $id . ' (Spec ID ' . $finalSpecId . ')',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Building specifications updated successfully!');
    }
}
```

## File: `resources/views/register-item.blade.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Registration | DepEd Command Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .step-active { color: #c00000; }
        .step-active .icon-box { background-color: #c00000; color: white; border-color: #c00000; box-shadow: 0 10px 15px -3px rgba(192, 0, 0, 0.2); transform: scale(1.05); }
        .step-inactive { color: #cbd5e1; }
        .step-inactive .icon-box { background-color: white; color: #cbd5e1; border-color: #e2e8f0; }
        .step-complete .icon-box { background-color: #1e293b; color: white; border-color: #1e293b; }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }

        .stepper-line { flex-grow: 1; height: 3px; background-color: #e2e8f0; position: relative; top: -14px; margin: 0 20px; border-radius: 99px; }
        .stepper-line.active { background-color: #c00000; transition: all 0.5s ease; }
        
        .custom-scroll::-webkit-scrollbar { width: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

        .autocomplete-dropdown { position: absolute; z-index: 50; width: 100%; background: white; border: 1px solid #e2e8f0; border-radius: 1.5rem; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1); max-height: 200px; overflow-y: auto; top: calc(100% + 6px); }
        .autocomplete-item { padding: 0.85rem 1.25rem; font-size: 0.8rem; font-weight: 600; color: #334155; cursor: pointer; transition: all 0.15s; }
        .autocomplete-item:hover { background: #fef2f2; color: #c00000; }
        .autocomplete-item .hint { font-size: 0.65rem; color: #94a3b8; font-weight: 500; }

        .back-btn-cool {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .back-btn-cool:hover {
            border-color: #c00000;
            color: #c00000;
            box-shadow: 0 10px 15px -3px rgba(192, 0, 0, 0.1);
            transform: translateX(-4px);
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-900 overflow-x-hidden">

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    title: 'Item Registered!',
                    text: @json(session('success')),
                    icon: 'success',
                    confirmButtonColor: '#c00000',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    title: 'Registration Error',
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    icon: 'error',
                    confirmButtonColor: '#c00000',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                }).then(() => {
                    // Jump directly to step 2 on validation error (specs step)
                    goToStep(2);
                });
            });
        </script>
    @endif

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">

    <div class="max-w-[1100px] mx-auto p-6 lg:p-12 min-h-screen flex flex-col">
        
        {{-- Header Section --}}
        <div class="flex justify-between items-center mb-16 px-2">
            <div>
                <h2 class="text-3xl font-black text-slate-800 uppercase italic leading-none">Register new Inventory Item</h2>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-[0.2em] mt-2">Department of Education • Zamboanga City</p>
            </div>
            <a href="/inventory-setup?step=2&mode=add" class="back-btn-cool px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back
            </a>
        </div>

        {{-- Stepper --}}
        <div class="flex items-center justify-between mb-20 px-12 relative">
            <div id="step1-indicator" class="flex flex-col items-center gap-4 step-active z-10 transition-all duration-500">
                <div class="icon-box w-16 h-16 rounded-[1.8rem] border-2 flex items-center justify-center transition-all duration-500 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest leading-none italic">Asset Source</span>
            </div>
            <div id="line-1" class="stepper-line"></div>
            <div id="step2-indicator" class="flex flex-col items-center gap-4 step-inactive z-10 transition-all duration-500">
                <div class="icon-box w-16 h-16 rounded-[1.8rem] border-2 flex items-center justify-center transition-all duration-500 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest leading-none italic">Specifications</span>
            </div>
            <div id="line-2" class="stepper-line"></div>
            <div id="step3-indicator" class="flex flex-col items-center gap-4 step-inactive z-10 transition-all duration-500">
                <div class="icon-box w-16 h-16 rounded-[1.8rem] border-2 flex items-center justify-center transition-all duration-500 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest leading-none italic">Confirmation</span>
            </div>
        </div>

        <div class="flex-grow">

            {{-- ============================== --}}
            {{-- STEP 1: SOURCE                 --}}
            {{-- ============================== --}}
            <div id="step1-content" class="animate-fade space-y-8">
                <div class="bg-white border border-slate-100 rounded-[3.5rem] p-12 shadow-sm">
                    <h3 class="text-2xl font-black text-slate-800 uppercase italic mb-10">01. Source Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-10">
                        {{-- Entity Type --}}
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-[#c00000] uppercase tracking-widest ml-1 italic underline underline-offset-4 decoration-2">Entity Type <span class="text-red-500">*</span></label>
                            <select id="sourceEntityType" onchange="handleEntityTypeChange()" class="w-full p-6 bg-slate-50 border border-slate-100 rounded-3xl font-black text-slate-700 outline-none focus:ring-4 focus:ring-red-50 transition-all cursor-pointer appearance-none">
                                <option value="" selected disabled>-- Select Entity Type --</option>
                                <option value="school">School (Internal)</option>
                                <option value="external">External (Supplier / Provider)</option>
                            </select>
                        </div>
                        {{-- Dynamic source input --}}
                        <div class="space-y-3 relative">
                            <label id="sourceDynamicLabel" class="text-[10px] font-black text-slate-300 uppercase tracking-widest ml-1 italic">Provider Name</label>
                            <input type="text" id="sourceDynamicInput" disabled placeholder="Select type first..."
                                class="w-full p-6 bg-slate-100 border border-slate-100 rounded-3xl font-bold text-slate-700 outline-none transition-all placeholder:text-slate-300 shadow-inner"
                                autocomplete="off" oninput="filterSourceInput()" onfocus="filterSourceInput()">
                            <div id="sourceDropdown" class="autocomplete-dropdown hidden custom-scroll"></div>
                            <p id="sourceExistingHint" class="hidden text-[10px] font-semibold text-emerald-600 ml-2 mt-1">✓ Using existing provider</p>
                            <p id="sourceNewHint" class="hidden text-[10px] font-semibold text-blue-600 ml-2 mt-1">✦ Will be registered as new</p>
                        </div>
                    </div>
                    
                    <div class="pt-10 border-t border-slate-50 grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-3 relative">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Authorized Personnel</label>
                            <input type="text" id="receiverName" placeholder="Click to browse personnel..."
                                class="w-full p-5 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-slate-700 outline-none focus:border-slate-300 transition-all"
                                autocomplete="off" oninput="filterPersonnelInput()" onfocus="filterPersonnelInput()">
                            <div id="personnelDropdown" class="autocomplete-dropdown hidden custom-scroll"></div>
                            <p id="personnelExistingHint" class="hidden text-[10px] font-semibold text-emerald-600 ml-2 mt-1">✓ Using existing personnel</p>
                            <p id="personnelNewHint" class="hidden text-[10px] font-semibold text-blue-600 ml-2 mt-1">✦ Will be registered as new</p>
                        </div>
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Job Title / Position</label>
                            <input type="text" id="receiverPos" placeholder="e.g. Supply Officer" class="w-full p-5 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-slate-700 outline-none focus:border-slate-300 transition-all">
                        </div>
                    </div>
                </div>
                <div class="flex justify-end pb-12"> {{-- Added pb-12 for a generous gap --}}
    <button id="step1-next" onclick="goToStep(2)" disabled class="group px-14 py-6 bg-slate-200 text-slate-400 rounded-[2.5rem] font-black uppercase tracking-widest text-xs transition-all flex items-center gap-4 cursor-not-allowed shadow-sm">
        Next Step
        <svg class="w-5 h-5 transition-transform group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
        </svg>
    </button>
</div>
            </div>

            {{-- ============================== --}}
            {{-- STEP 2: ITEM SPECS + FORM      --}}
            {{-- ============================== --}}
            <div id="step2-content" class="hidden animate-fade space-y-8">
                <form id="registerItemForm" action="{{ route('register.item.store') }}" method="POST">
                    @csrf
                    {{-- Hidden source fields --}}
                    <input type="hidden" name="source_entity_type" id="hiddenSourceEntityType">
                    <input type="hidden" name="provider_id" id="hiddenProviderId">
                    <input type="hidden" name="provider_name" id="hiddenProviderName">
                    <input type="hidden" name="personnel_name" id="hiddenPersonnelName">
                    <input type="hidden" name="personnel_position" id="hiddenPersonnelPosition">

                  <div class="bg-white border border-slate-100 rounded-[3.5rem] p-12 shadow-sm">
    {{-- Header Section --}}
    <div class="flex justify-between items-center mb-10">
        <h3 class="text-2xl font-black text-slate-800 uppercase italic leading-none">02. Asset Details</h3>
        
        {{-- Add Button positioned at the top right of the card --}}
        <button type="button" onclick="addSubItemField()" 
            class="group bg-red-50 text-[#c00000] border border-red-100 px-6 py-3 rounded-2xl font-black text-[10px] uppercase hover:bg-[#c00000] hover:text-white transition-all flex items-center gap-2 shadow-sm active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 transition-transform group-hover:rotate-90">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add New Specification
        </button>
    </div>

   

                        {{-- Category & Item Name with autocomplete --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                            {{-- Category --}}
                            <div class="space-y-2 relative">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 italic">Main Category <span class="text-red-500">*</span></label>
                                <input type="hidden" name="category_id" id="categoryId">
                                <input type="text" name="category_name" id="categoryName" placeholder="e.g. ICT, Furniture"
                                    class="w-full p-6 bg-slate-50 border border-slate-100 rounded-3xl font-black text-sm outline-none focus:ring-4 focus:ring-red-50 transition-all"
                                    autocomplete="off" oninput="filterCategoryInput()" onfocus="filterCategoryInput()" required>
                                <div id="categoryDropdown" class="autocomplete-dropdown hidden custom-scroll"></div>
                                <p id="categoryHint" class="hidden text-[10px] font-semibold text-emerald-600 ml-2">✓ Using existing category</p>
                                <p id="categoryNewHint" class="hidden text-[10px] font-semibold text-blue-600 ml-2">✦ Will create new category</p>
                            </div>
                            {{-- Item Name --}}
                            <div class="space-y-2 relative">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 italic">Item Name <span class="text-red-500">*</span></label>
                                <input type="hidden" name="existing_item_id" id="existingItemId">
                                <input type="text" name="item_name" id="itemName" placeholder="e.g. Laptop, Table"
                                    class="w-full p-6 bg-slate-50 border border-slate-100 rounded-3xl font-black text-sm outline-none focus:ring-4 focus:ring-red-50 transition-all"
                                    autocomplete="off" oninput="filterItemInput()" onfocus="filterItemInput()" required>
                                <div id="itemDropdown" class="autocomplete-dropdown hidden custom-scroll"></div>
                                <p id="itemExistingHint" class="hidden text-[10px] font-semibold text-emerald-600 ml-2">✓ Adding stock to existing item</p>
                                <p id="itemNewHint" class="hidden text-[10px] font-semibold text-blue-600 ml-2">✦ Will register as new item</p>
                            </div>
                        </div>

                        {{-- Sub-item row (single spec) --}}
                        <div class="pt-10 border-t border-slate-50">
                            <div id="subItemContainer" class="space-y-8"></div>
                        </div>
                    </div>

                    <div class="flex justify-between my- py-6"> {{-- Idinagdag ang my-12 para sa gap sa taas/baba at py-6 para sa inner padding --}}
    
    {{-- Back Button --}}
    <button type="button" onclick="goToStep(1)" class="group px-10 py-6 bg-white border border-slate-200 text-slate-600 rounded-[2.5rem] font-black uppercase tracking-widest text-xs hover:bg-slate-50 transition-all flex items-center gap-4 italic shadow-sm active:scale-95">
        <svg class="w-5 h-5 transition-transform group-hover:-translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
        </svg>
        Source
    </button>

    {{-- Next Button --}}
    <button type="button" onclick="goToStep(3)" class="group px-14 py-6 bg-[#c00000] text-white rounded-[2.5rem] font-black uppercase tracking-widest text-xs shadow-xl shadow-red-100 hover:scale-105 hover:bg-red-700 transition-all flex items-center gap-4 italic">
        Final Review
        <svg class="w-5 h-5 transition-transform group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
        </svg>
    </button>

</div>
                </form>
            </div>

            {{-- ============================== --}}
            {{-- STEP 3: REVIEW & SUBMIT        --}}
            {{-- ============================== --}}
            <div id="step3-content" class="hidden animate-fade space-y-10 pb-20">
                <div class="bg-slate-900 rounded-[4rem] p-16 shadow-2xl relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="text-3xl font-black text-white uppercase italic mb-8 tracking-tight">Batch Summary Preview</h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                            <div class="bg-white/5 border border-white/10 rounded-[2.5rem] p-8 shadow-inner">
                                <span class="text-[#c00000] text-[9px] font-black uppercase tracking-[0.3em] block mb-3">Asset Source</span>
                                <h4 id="sumSource" class="text-2xl font-black text-white italic underline decoration-red-500 underline-offset-8">--</h4>
                                <div id="sumPersonnelContainer" class="hidden mt-3 border-l-2 border-[#c00000] pl-3 py-1 bg-white/5 rounded-r-xl pr-4 inline-block">
                                    <span class="text-slate-400 text-[9px] font-black uppercase tracking-widest block mb-1">Authorized Personnel</span>
                                    <p id="sumPersonnel" class="text-white text-sm font-bold m-0 leading-none">--</p>
                                </div>
                                <p id="sumType" class="text-slate-500 text-[10px] font-bold uppercase mt-4 tracking-widest italic">--</p>
                            </div>
                            <div class="bg-white/5 border border-white/10 rounded-[2.5rem] p-8 shadow-inner">
                                <span class="text-emerald-500 text-[9px] font-black uppercase tracking-[0.3em] block mb-3">Item Name</span>
                                <h4 id="sumItem" class="text-2xl font-black text-white italic underline decoration-emerald-500 underline-offset-8">--</h4>
                                <p id="sumCat" class="text-slate-500 text-[10px] font-bold uppercase mt-4 tracking-widest italic">--</p>
                            </div>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-[2.5rem] overflow-hidden">
                            <table class="w-full text-left">
                                <thead class="border-b border-white/10 bg-white/5">
                                    <tr>
                                        <th class="p-6 text-[9px] font-black text-slate-500 uppercase italic tracking-[0.2em]">Specifications</th>
                                        <th class="p-6 text-[9px] font-black text-slate-500 uppercase italic tracking-[0.2em]">Unit Price</th>
                                        <th class="p-6 text-[9px] font-black text-slate-500 uppercase italic tracking-[0.2em]">Qty</th>
                                        <th class="p-6 text-[9px] font-black text-slate-500 uppercase italic tracking-[0.2em]">Condition</th>
                                        <th class="p-6 text-[9px] font-black text-slate-500 uppercase italic text-right tracking-[0.2em]">Property No.</th>
                                    </tr>
                                </thead>
                                <tbody id="summaryTable" class="text-white text-xs font-bold uppercase tracking-tight"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="flex justify-between">
                    <button onclick="goToStep(2)" class="group px-10 py-6 bg-slate-200 text-slate-600 rounded-[2.5rem] font-black uppercase tracking-widest text-xs flex items-center gap-4">
                        <svg class="w-5 h-5 transition-transform group-hover:-translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path></svg>
                        Edit Specs
                    </button>
                    <button onclick="confirmSubmit()" class="px-20 py-6 bg-slate-900 text-white rounded-[2.5rem] font-black uppercase tracking-widest text-xs hover:bg-black transition-all shadow-2xl italic">
                        REGISTER TO MASTERLIST ⚡
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
        // =============================================
        // DATA FROM BACKEND
        // =============================================
        const rawCategories  = @json($categories);
        const rawItems       = @json($items);
        const rawSubItems    = @json($subItems);
        const rawStakeholders= @json($stakeholders);
        const rawSchools     = @json($allSchools);

        let selectedSourceId   = null;
        let selectedSourceType = null;

        // =============================================
        // RESTORE OLD INPUT (Validation Failure)
        // =============================================
        document.addEventListener('DOMContentLoaded', () => {
            const oldInput = @json(session()->getOldInput());
            
            if (Object.keys(oldInput).length > 0 && oldInput.source_entity_type) {
                // Restore Step 1 fields
                document.getElementById('sourceEntityType').value = oldInput.source_entity_type;
                handleEntityTypeChange(); // Trigger layout update
                
                selectedSourceId = oldInput.provider_id || null;
                document.getElementById('sourceDynamicInput').value = oldInput.provider_name || '';
                document.getElementById('receiverName').value = oldInput.personnel_name || '';
                document.getElementById('receiverPos').value = oldInput.personnel_position || '';
                updateStep1NextBtn();

                // Restore Step 2 fields
                document.getElementById('categoryId').value = oldInput.category_id || '';
                document.getElementById('categoryName').value = oldInput.category_name || '';
                document.getElementById('existingItemId').value = oldInput.existing_item_id || '';
                document.getElementById('itemName').value = oldInput.item_name || '';

                // Restore sub items dynamically
                if (oldInput.sub_items && Array.isArray(oldInput.sub_items)) {
                    document.getElementById('subItemContainer').innerHTML = ''; // clear default
                    oldInput.sub_items.forEach((subName, index) => {
                        addSubItemField();
                        const rows = document.querySelectorAll('.row-container');
                        const row = rows[rows.length - 1];
                        
                        row.querySelector('.spec-val').value = subName || '';
                        if (oldInput.sub_item_conditions && oldInput.sub_item_conditions[index]) {
                            row.querySelector('.cond-val').value = oldInput.sub_item_conditions[index];
                        }
                        if (oldInput.sub_item_quantities && oldInput.sub_item_quantities[index]) {
                            row.querySelector('.qty-val').value = oldInput.sub_item_quantities[index];
                        }
                        // Handle Checkbox
                        if (oldInput.sub_item_serialized && oldInput.sub_item_serialized[index]) {
                            const cb = row.querySelector('input[type="checkbox"]');
                            cb.checked = true;
                            // Extract 'id' properly from row ID string (e.g. "row-12345")
                            const rowId = row.id.split('-')[1];
                            handleSerializedChange(cb, rowId);
                        }
                        if (oldInput.sub_item_property_numbers && oldInput.sub_item_property_numbers[index]) {
                            row.querySelector('.prop-val').value = oldInput.sub_item_property_numbers[index];
                        }
                        if (oldInput.sub_item_serial_numbers && oldInput.sub_item_serial_numbers[index]) {
                            row.querySelector('.sn-val').value = oldInput.sub_item_serial_numbers[index];
                        }
                    });
                }
                
                // Immediately navigate to step 2 to correct errors
                setTimeout(() => { goToStep(2); }, 100);
            }
            
            // Check for Errors and alert
            @if ($errors->any())
                Swal.fire({
                    title: 'Incomplete Submission',
                    html: '{!! implode("<br>", $errors->all()) !!}',
                    icon: 'error',
                    confirmButtonColor: '#c00000',
                    customClass: { popup: 'rounded-[1.5rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
            @endif
        });

        // =============================================
        // STEPPER NAVIGATION
        // =============================================
        function goToStep(step) {
            ['step1-content','step2-content','step3-content'].forEach(id => {
                document.getElementById(id).classList.add('hidden');
            });
            document.getElementById(`step${step}-content`).classList.remove('hidden');

            document.getElementById('step1-indicator').className = 'flex flex-col items-center gap-4 z-10 transition-all duration-500 ' + (step === 1 ? 'step-active' : 'step-complete');
            document.getElementById('step2-indicator').className = 'flex flex-col items-center gap-4 z-10 transition-all duration-500 ' + (step === 2 ? 'step-active' : (step > 2 ? 'step-complete' : 'step-inactive'));
            document.getElementById('step3-indicator').className = 'flex flex-col items-center gap-4 z-10 transition-all duration-500 ' + (step === 3 ? 'step-active' : 'step-inactive');

            document.getElementById('line-1').className = 'stepper-line ' + (step >= 2 ? 'active' : '');
            document.getElementById('line-2').className = 'stepper-line ' + (step >= 3 ? 'active' : '');

            if (step === 2) {
                document.getElementById('hiddenSourceEntityType').value = selectedSourceType;
                document.getElementById('hiddenProviderId').value = selectedSourceId || '';
                document.getElementById('hiddenProviderName').value = document.getElementById('sourceDynamicInput').value;
                document.getElementById('hiddenPersonnelName').value = document.getElementById('receiverName').value;
                document.getElementById('hiddenPersonnelPosition').value = document.getElementById('receiverPos').value;

                if (document.getElementById('subItemContainer').children.length === 0) {
                    addSubItemField();
                }
            }

            if (step === 3) buildSummary();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // =============================================
        // STEP 1 — ENTITY TYPE HANDLER
        // =============================================
        function handleEntityTypeChange() {
            const type = document.getElementById('sourceEntityType').value;
            const input = document.getElementById('sourceDynamicInput');
            const label = document.getElementById('sourceDynamicLabel');
            const nextBtn = document.getElementById('step1-next');

            selectedSourceType = type;
            selectedSourceId = null;
            input.value = '';
            input.disabled = false;
            input.classList.remove('bg-slate-100', 'shadow-inner');
            input.classList.add('bg-white', 'border-slate-200', 'focus:ring-4', 'focus:ring-red-50');
            document.getElementById('sourceExistingHint')?.classList.add('hidden');
            document.getElementById('sourceNewHint')?.classList.add('hidden');
            label.classList.add('text-[#c00000]');
            label.classList.remove('text-slate-300');

            label.innerText = type === 'school'
                ? 'Search School Name *'
                : 'External Provider / Supplier Name *';

            // Keep Next disabled until they pick/type something
            updateStep1NextBtn();
        }

        function updateStep1NextBtn() {
            const input = document.getElementById('sourceDynamicInput');
            const btn   = document.getElementById('step1-next');
            const hasValue = input.value.trim().length > 0;

            if (hasValue) {
                btn.disabled = false;
                btn.classList.remove('bg-slate-200', 'text-slate-400', 'cursor-not-allowed');
                btn.classList.add('bg-slate-900', 'text-white', 'hover:bg-black', 'shadow-xl', 'cursor-pointer');
            } else {
                btn.disabled = true;
                btn.classList.add('bg-slate-200', 'text-slate-400', 'cursor-not-allowed');
                btn.classList.remove('bg-slate-900', 'text-white', 'hover:bg-black', 'shadow-xl', 'cursor-pointer');
            }
        }

        function filterSourceInput() {
            const q = document.getElementById('sourceDynamicInput').value.trim().toLowerCase();
            const dd = document.getElementById('sourceDropdown');
            document.getElementById('sourceExistingHint')?.classList.add('hidden');
            document.getElementById('sourceNewHint')?.classList.add('hidden');
            updateStep1NextBtn();

            let list = [];
            if (selectedSourceType === 'school') {
                list = rawSchools.filter(s =>
                    !q || s.name.toLowerCase().includes(q) || (s.school_id && s.school_id.toString().includes(q))
                ).slice(0, 30);
            } else if (selectedSourceType === 'external') {
                // Only Distributor type with entity_type = 'School' or 'External'
                const distributors = rawStakeholders.filter(s =>
                    s.type === 'Distributor' &&
                    (s.entity_type === 'School' || s.entity_type === 'External')
                );
                list = distributors.filter(s =>
                    !q || s.name.toLowerCase().includes(q)
                ).slice(0, 30);
            }

            const exactMatch = list.find(s => s.name.toLowerCase() === q);
            if (exactMatch) {
                document.getElementById('sourceExistingHint')?.classList.remove('hidden');
            } else if (q) {
                document.getElementById('sourceNewHint')?.classList.remove('hidden');
            }

            if (list.length === 0) { dd.classList.add('hidden'); return; }

            dd.innerHTML = list.map(s => `
                <div class="autocomplete-item" onclick="selectSource(${s.id}, '${s.name.replace(/'/g,"\\'")}')">
                    ${s.name}
                    ${s.entity_type ? `<div class="hint">${s.entity_type}</div>` : ''}
                    ${s.school_id ? `<div class="hint">ID: ${s.school_id}</div>` : ''}
                </div>`).join('');
            dd.classList.remove('hidden');
        }

        function selectSource(id, name) {
            selectedSourceId = id;
            document.getElementById('sourceDynamicInput').value = name;
            document.getElementById('sourceDropdown').classList.add('hidden');
            document.getElementById('sourceExistingHint')?.classList.remove('hidden');
            document.getElementById('sourceNewHint')?.classList.add('hidden');
            // Clear personnel when provider changes
            document.getElementById('receiverName').value = '';
            document.getElementById('receiverPos').value = '';
            document.getElementById('personnelDropdown').classList.add('hidden');
            document.getElementById('personnelExistingHint')?.classList.add('hidden');
            document.getElementById('personnelNewHint')?.classList.add('hidden');
            updateStep1NextBtn();
        }

        // =============================================
        // STEP 1 — AUTHORIZED PERSONNEL AUTOCOMPLETE
        // =============================================
        function filterPersonnelInput() {
            const q = document.getElementById('receiverName').value.trim().toLowerCase();
            const dd = document.getElementById('personnelDropdown');
            document.getElementById('personnelExistingHint')?.classList.add('hidden');
            document.getElementById('personnelNewHint')?.classList.add('hidden');

            if (!selectedSourceId) {
                dd.innerHTML = '<div class="autocomplete-item" style="color:#94a3b8;font-style:italic;cursor:default;">Select a provider first</div>';
                dd.classList.remove('hidden');
                return;
            }

            // Find personnel based on source entity binding
            let personnel = rawStakeholders.filter(s => {
                if (selectedSourceType === 'school') {
                    return s.school_id && String(s.school_id) === String(selectedSourceId);
                }
                return s.parent_id && String(s.parent_id) === String(selectedSourceId);
            });

            const filtered = personnel.filter(s =>
                !q ||
                (s.person_name && s.person_name.toLowerCase().includes(q)) ||
                s.name.toLowerCase().includes(q)
            ).slice(0, 30);

            const exactMatch = filtered.find(s => {
                const dName = (s.person_name || s.name).toLowerCase();
                return dName === q;
            });
            if (exactMatch) {
                document.getElementById('personnelExistingHint')?.classList.remove('hidden');
            } else if (q) {
                document.getElementById('personnelNewHint')?.classList.remove('hidden');
            }

            if (filtered.length === 0) { dd.classList.add('hidden'); return; }

            dd.innerHTML = filtered.map(s => {
                const displayName = s.person_name || s.name;
                const pos = s.position || '';
                return `<div class="autocomplete-item" onclick="selectPersonnel('${displayName.replace(/'/g,"\\'")}', '${pos.replace(/'/g,"\\'")}')">
                    ${displayName}
                    ${pos ? `<div class="hint">${pos}</div>` : ''}
                </div>`;
            }).join('');
            dd.classList.remove('hidden');
        }

        function selectPersonnel(name, position) {
            document.getElementById('receiverName').value = name;
            document.getElementById('receiverPos').value = position;
            document.getElementById('personnelDropdown').classList.add('hidden');
            document.getElementById('personnelExistingHint')?.classList.remove('hidden');
            document.getElementById('personnelNewHint')?.classList.add('hidden');
        }


        // =============================================
        // STEP 2 — CATEGORY AUTOCOMPLETE
        // =============================================
        function filterCategoryInput() {
            const q = document.getElementById('categoryName').value.trim().toLowerCase();
            const dd = document.getElementById('categoryDropdown');
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryHint').classList.add('hidden');
            document.getElementById('categoryNewHint').classList.add('hidden');

            const matches = rawCategories.filter(c => !q || c.name.toLowerCase().includes(q)).slice(0, 15);
            const exactMatch = rawCategories.find(c => c.name.toLowerCase() === q);

            if (exactMatch) {
                document.getElementById('categoryId').value = exactMatch.id;
                document.getElementById('categoryHint').classList.remove('hidden');
            } else if (q) {
                document.getElementById('categoryNewHint').classList.remove('hidden');
            }

            if (matches.length === 0) { dd.classList.add('hidden'); return; }

            dd.innerHTML = matches.map(c => `
                <div class="autocomplete-item" onclick="selectCategory(${c.id}, '${c.name.replace(/'/g,"\\'")}')">
                    ${c.name}
                </div>`).join('');
            dd.classList.remove('hidden');
        }

        function selectCategory(id, name) {
            document.getElementById('categoryId').value = id;
            document.getElementById('categoryName').value = name;
            document.getElementById('categoryDropdown').classList.add('hidden');
            document.getElementById('categoryHint').classList.remove('hidden');
            document.getElementById('categoryNewHint').classList.add('hidden');
        }

        // =============================================
        // STEP 2 — ITEM NAME AUTOCOMPLETE
        // =============================================
        function filterItemInput() {
            const q = document.getElementById('itemName').value.trim().toLowerCase();
            const dd = document.getElementById('itemDropdown');
            document.getElementById('existingItemId').value = '';
            document.getElementById('itemExistingHint').classList.add('hidden');
            document.getElementById('itemNewHint').classList.add('hidden');

            const catId = document.getElementById('categoryId').value;
            let matches = rawItems;
            if (catId) {
                matches = matches.filter(i => String(i.category_id) === String(catId));
            } else if (!q) {
                // If no category and no query, don't show the whole database of items
                // Prompt them gently
                dd.innerHTML = '<div class="autocomplete-item" style="color:#94a3b8;font-style:italic;cursor:default;">Select Category first</div>';
                dd.classList.remove('hidden');
                return;
            }
            
            matches = matches.filter(i => !q || i.name.toLowerCase().includes(q)).slice(0, 15);

            const exactMatch = rawItems.find(i => i.name.toLowerCase() === q);
            if (exactMatch) {
                document.getElementById('existingItemId').value = exactMatch.id;
                document.getElementById('itemExistingHint').classList.remove('hidden');
            } else if (q) {
                document.getElementById('itemNewHint').classList.remove('hidden');
            }

            if (matches.length === 0) { dd.classList.add('hidden'); return; }

            dd.innerHTML = matches.map(i => {
                const cat = rawCategories.find(c => c.id === i.category_id);
                return `<div class="autocomplete-item" onclick="selectItem(${i.id}, '${i.name.replace(/'/g,"\\'")}', ${i.category_id})">
                    ${i.name}
                    ${cat ? `<div class="hint">${cat.name}</div>` : ''}
                </div>`;
            }).join('');
            dd.classList.remove('hidden');
        }

        function selectItem(id, name, catId) {
            document.getElementById('existingItemId').value = id;
            document.getElementById('itemName').value = name;
            document.getElementById('itemDropdown').classList.add('hidden');
            document.getElementById('itemExistingHint').classList.remove('hidden');
            document.getElementById('itemNewHint').classList.add('hidden');

            // Auto-fill category if not yet filled
            const cat = rawCategories.find(c => c.id === catId);
            if (cat && !document.getElementById('categoryId').value) {
                document.getElementById('categoryId').value = cat.id;
                document.getElementById('categoryName').value = cat.name;
                document.getElementById('categoryHint').classList.remove('hidden');
                document.getElementById('categoryNewHint').classList.add('hidden');
            }
        }

       // =============================================
// STEP 2 — ADD SUB-ITEM ROW (single row only)
// =============================================
function addSubItemField() {
    const container = document.getElementById('subItemContainer');
    const id = Date.now();

    const html = `
        <div id="row-${id}" class="row-container p-8 bg-slate-50 border border-slate-100 rounded-[2.5rem] animate-fade relative group shadow-sm transition-all hover:border-[#c00000]/30 hover:bg-white">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">

                <div class="lg:col-span-5 space-y-2 relative">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block ml-1 italic">Specifications / Materials <span class="text-red-500">*</span></label>
                    <input type="text" name="sub_items[]" placeholder="e.g. Core i7, 4ft Steel Frame" required
                        autocomplete="off" oninput="filterSpecInput(this, ${id})" onfocus="filterSpecInput(this, ${id})"
                        class="spec-val w-full p-4 bg-white border border-slate-100 rounded-2xl font-bold text-sm outline-none focus:border-red-200 shadow-sm transition-all">
                    <div id="specDropdown-${id}" class="autocomplete-dropdown hidden custom-scroll"></div>
                    <p id="specExistingHint-${id}" class="hidden text-[9px] font-semibold text-emerald-600 ml-2 mt-1 italic">✓ Existing spec found</p>
                    <p id="specNewHint-${id}" class="hidden text-[9px] font-semibold text-blue-600 ml-2 mt-1 italic">✦ New spec entry</p>
                </div>

                <div class="lg:col-span-2 space-y-2">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block ml-1 italic">Condition</label>
                    <select name="sub_item_conditions[]" class="cond-val w-full p-4 bg-white border border-slate-100 rounded-2xl font-bold text-xs outline-none shadow-sm cursor-pointer transition-all focus:border-red-200">
                        <option value="Serviceable">Serviceable</option>
                        <option value="Unserviceable">Unserviceable</option>
                    </select>
                </div>

                <div class="lg:col-span-2 space-y-2">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block text-center italic">Qty <span class="text-red-500">*</span></label>
                    <input type="number" name="sub_item_quantities[]" id="qty-${id}" placeholder="0" min="1" required
                        class="qty-val w-full p-4 bg-white border border-slate-100 rounded-2xl font-bold text-sm text-center outline-none shadow-sm transition-all focus:border-red-200">
                </div>

                <div class="lg:col-span-3">
                    <button type="button" onclick="toggleSerial(${id})"
                        class="w-full py-4 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-md flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        Serial Info
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6 pt-6 border-t border-slate-100/50">
                <div class="space-y-2">
                    <label class="text-[9px] font-black text-[#c00000] uppercase tracking-widest block ml-1 italic underline underline-offset-4">₱ Unit Price</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-xs font-black text-slate-400 italic">₱</span>
                        <input type="number" name="sub_item_prices[]" placeholder="0.00" step="0.01" min="0"
                            class="price-val w-full pl-8 p-4 bg-white border border-red-50 rounded-2xl font-bold text-sm outline-none shadow-sm transition-all focus:ring-4 focus:ring-red-50">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[9px] font-black text-[#c00000] uppercase tracking-widest block ml-1 italic underline underline-offset-4">📅 Date Acquired</label>
                    <input type="date" name="sub_item_dates[]"
                        class="date-val w-full p-4 bg-white border border-red-50 rounded-2xl font-bold text-sm outline-none shadow-sm transition-all focus:ring-4 focus:ring-red-50 uppercase text-slate-500">
                </div>
            </div>

            <div id="serial-panel-${id}" class="hidden mt-8 pt-8 border-t-2 border-dashed border-slate-100 animate-fade">
                <div class="flex flex-col md:flex-row gap-8 items-center bg-white p-6 rounded-3xl border border-slate-100 shadow-inner">
                    <label class="flex items-center gap-4 cursor-pointer min-w-[180px] group">
                        <input type="hidden" name="sub_item_serialized[]" value="0" id="serial-flag-${id}">
                        <input type="checkbox" value="1"
                            class="w-6 h-6 rounded-lg border-slate-200 accent-[#c00000] transition-all transform group-hover:scale-110"
                            onchange="document.getElementById('serial-flag-${id}').value = this.checked ? '1' : '0'; handleSerializedChange(this, ${id})">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-black uppercase text-slate-700 leading-none">Serialized?</span>
                            <span class="text-[8px] font-bold text-slate-400 uppercase mt-1 italic tracking-tight">Locks Qty to 1</span>
                        </div>
                    </label>
                    <div class="flex gap-4 w-full">
                        <input type="text" name="sub_item_property_numbers[]" placeholder="Property No. (e.g. 2026-ICT-001)" disabled
                            class="prop-val flex-1 p-4 bg-slate-100 border border-slate-100 rounded-xl font-bold text-[11px] outline-none shadow-sm italic placeholder:text-slate-300 transition-all">
                        <input type="text" name="sub_item_serial_numbers[]" placeholder="Serial No. (e.g. SN-88920-X)" disabled
                            class="sn-val flex-1 p-4 bg-slate-100 border border-slate-100 rounded-xl font-bold text-[11px] outline-none shadow-sm italic placeholder:text-slate-300 transition-all">
                    </div>
                </div>
            </div>

            <button type="button" onclick="document.getElementById('row-${id}').remove()" 
                class="absolute -top-3 -right-3 w-10 h-10 bg-white border border-slate-100 text-slate-300 rounded-full hover:text-red-500 shadow-md flex items-center justify-center font-bold transition-all hover:scale-110 active:scale-95 italic">
                ✕
            </button>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
}

        function toggleSerial(id) {
            document.getElementById(`serial-panel-${id}`).classList.toggle('hidden');
        }

        function handleSerializedChange(checkbox, id) {
            const row = document.getElementById(`row-${id}`);
            const qtyInput = row.querySelector('.qty-val');
            const propInput = row.querySelector('.prop-val');
            const snInput = row.querySelector('.sn-val');
            
            if (checkbox.checked) {
                qtyInput.value = 1;
                qtyInput.readOnly = true;
                qtyInput.classList.add('bg-slate-100');
                
                propInput.disabled = false;
                propInput.classList.remove('bg-slate-100', 'placeholder:text-slate-300');
                propInput.classList.add('bg-white');
                
                snInput.disabled = false;
                snInput.classList.remove('bg-slate-100', 'placeholder:text-slate-300');
                snInput.classList.add('bg-white');
            } else {
                qtyInput.readOnly = false;
                qtyInput.classList.remove('bg-slate-100');
                
                propInput.disabled = true;
                propInput.value = '';
                propInput.classList.add('bg-slate-100', 'placeholder:text-slate-300');
                propInput.classList.remove('bg-white');
                
                snInput.disabled = true;
                snInput.value = '';
                snInput.classList.add('bg-slate-100', 'placeholder:text-slate-300');
                snInput.classList.remove('bg-white');
            }
        }

        function filterSpecInput(input, id) {
            const q = input.value.trim().toLowerCase();
            const dd = document.getElementById(`specDropdown-${id}`);
            const itemId = document.getElementById('existingItemId').value;
            document.getElementById(`specExistingHint-${id}`)?.classList.add('hidden');
            document.getElementById(`specNewHint-${id}`)?.classList.add('hidden');

            if (!itemId) {
                dd.innerHTML = '<div class="autocomplete-item" style="color:#94a3b8;font-style:italic;cursor:default;">Select an item name first</div>';
                dd.classList.remove('hidden');
                return;
            }

            let specs = rawSubItems.filter(s => String(s.item_id) === String(itemId) && !s.is_serialized);
            
            const filtered = specs.filter(s =>
                !q || s.name.toLowerCase().includes(q)
            ).slice(0, 20);

            const exactMatch = filtered.find(s => s.name.toLowerCase() === q);
            if (exactMatch) {
                document.getElementById(`specExistingHint-${id}`)?.classList.remove('hidden');
            } else if (q) {
                document.getElementById(`specNewHint-${id}`)?.classList.remove('hidden');
            }

            if (filtered.length === 0) { dd.classList.add('hidden'); return; }

            dd.innerHTML = filtered.map(s => {
                return `<div class="autocomplete-item" onclick="selectSpec(${id}, '${s.name.replace(/'/g,"\\'")}')">
                    ${s.name}
                    <div class="hint">Available: ${s.quantity}</div>
                </div>`;
            }).join('');
            dd.classList.remove('hidden');
        }

        function selectSpec(rowId, name) {
            const row = document.getElementById(`row-${rowId}`);
            row.querySelector('.spec-val').value = name;
            document.getElementById(`specDropdown-${rowId}`).classList.add('hidden');
            document.getElementById(`specExistingHint-${rowId}`)?.classList.remove('hidden');
            document.getElementById(`specNewHint-${rowId}`)?.classList.add('hidden');
        }

        // =============================================
        // STEP 3 — BUILD SUMMARY
        // =============================================
        function buildSummary() {
            document.getElementById('sumSource').innerText = document.getElementById('sourceDynamicInput').value || '—';
            
            const personnel = document.getElementById('receiverName').value;
            if (personnel) {
                document.getElementById('sumPersonnel').innerText = personnel;
                document.getElementById('sumPersonnelContainer').classList.remove('hidden');
            } else {
                document.getElementById('sumPersonnelContainer').classList.add('hidden');
            }

            document.getElementById('sumType').innerText   = (selectedSourceType || 'Unknown').toUpperCase() + ' SOURCE';
            document.getElementById('sumItem').innerText   = document.getElementById('itemName').value || 'Unnamed Asset';
            document.getElementById('sumCat').innerText    = document.getElementById('categoryName').value || '—';

            const table = document.getElementById('summaryTable');
            table.innerHTML = '';
            document.querySelectorAll('.row-container').forEach(row => {
                const spec  = row.querySelector('.spec-val').value || '—';
                const price = row.querySelector('.price-val').value;
                const qty   = row.querySelector('.qty-val').value || '0';
                const cond  = row.querySelector('.cond-val').value || '—';
                const prop  = row.querySelector('.prop-val') ? row.querySelector('.prop-val').value || '—' : '—';
                table.innerHTML += `
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                        <td class="p-6 italic">${spec}</td>
                        <td class="p-6 text-emerald-400 italic">${price ? '₱ ' + parseFloat(price).toLocaleString() : '—'}</td>
                        <td class="p-6 text-slate-300">${qty}</td>
                        <td class="p-6 text-slate-300">${cond}</td>
                        <td class="p-6 text-right text-slate-400">${prop}</td>
                    </tr>`;
            });
        }

        // =============================================
        // SUBMISSION WITH SWEETALERT CONFIRM
        // =============================================

        /** Highlight a field red, scroll it into view, and auto-remove the highlight after 3s */
        function highlightField(el) {
            el.classList.add('ring-4', 'ring-red-400', 'border-red-400');
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => {
                el.classList.remove('ring-4', 'ring-red-400', 'border-red-400');
            }, 3000);
        }

        function confirmSubmit() {
            const itemName = document.getElementById('itemName').value.trim();
            const catName  = document.getElementById('categoryName').value.trim();

            // ── Step-2 header fields ──────────────────────────────────────────────
            if (!catName) {
                goToStep(2);
                setTimeout(() => highlightField(document.getElementById('categoryName')), 150);
                Swal.fire({ title: 'Missing Category', text: 'Please fill in the Main Category before submitting.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            if (!itemName) {
                goToStep(2);
                setTimeout(() => highlightField(document.getElementById('itemName')), 150);
                Swal.fire({ title: 'Missing Item Name', text: 'Please fill in the Item Name before submitting.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }

            // ── Sub-item rows ─────────────────────────────────────────────────────
            const rows = document.querySelectorAll('.row-container');
            if (rows.length === 0) {
                goToStep(2);
                Swal.fire({ title: 'No Specifications', text: 'Please add at least one specification / sub-item row before submitting.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }

            let firstError = null;
            let errorMessages = [];

            rows.forEach((row, rowIndex) => {
                const rowNum = rowIndex + 1;
                const specEl  = row.querySelector('.spec-val');
                const qtyEl   = row.querySelector('.qty-val');
                const priceEl = row.querySelector('.price-val');
                const dateEl  = row.querySelector('.date-val');

                const spec  = specEl  ? specEl.value.trim()   : '';
                const qty   = qtyEl   ? parseFloat(qtyEl.value)  : 0;
                const price = priceEl ? priceEl.value.trim()  : '';
                const date  = dateEl  ? dateEl.value.trim()   : '';

                if (!spec) {
                    errorMessages.push(`Row ${rowNum}: Specification / Material name is required.`);
                    if (!firstError) firstError = specEl;
                }
                if (!qty || qty <= 0) {
                    errorMessages.push(`Row ${rowNum}: Quantity must be greater than zero.`);
                    if (!firstError) firstError = qtyEl;
                }
                if (!price || parseFloat(price) <= 0) {
                    errorMessages.push(`Row ${rowNum}: Unit Price is required and must be greater than ₱0.00.`);
                    if (!firstError) firstError = priceEl;
                }
                if (!date) {
                    errorMessages.push(`Row ${rowNum}: Date Acquired is required.`);
                    if (!firstError) firstError = dateEl;
                }
            });

            if (errorMessages.length > 0) {
                // Navigate back to step 2 WITHOUT clearing data
                goToStep(2);
                // Highlight the first offending field after transition
                if (firstError) {
                    setTimeout(() => highlightField(firstError), 150);
                }
                Swal.fire({
                    title: 'Incomplete Specification Data',
                    html: '<ul style="text-align:left;font-size:0.8rem;line-height:1.8;">' +
                          errorMessages.map(m => `<li>⚠ ${m}</li>`).join('') +
                          '</ul>',
                    icon: 'warning',
                    confirmButtonColor: '#c00000',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
                return;
            }

            // ── All good — show final confirm ─────────────────────────────────────
            Swal.fire({
                title: 'Register to Masterlist?',
                html: `<strong>${itemName}</strong> under <em>${catName}</em> will be added to the inventory.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1e293b',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: '⚡ Register Now',
                cancelButtonText: 'Review Again',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('registerItemForm').submit();
                }
            });
        }

        // =============================================
        // CLOSE DROPDOWNS ON OUTSIDE CLICK
        // =============================================
        document.addEventListener('click', e => {
            ['categoryDropdown', 'itemDropdown', 'sourceDropdown', 'personnelDropdown'].forEach(id => {
                const dd = document.getElementById(id);
                if (dd && !dd.contains(e.target) && !e.target.closest('input')) {
                    dd.classList.add('hidden');
                }
            });
            document.querySelectorAll('[id^="specDropdown-"]').forEach(dd => {
                if (dd && !dd.contains(e.target) && !e.target.closest('input')) {
                    dd.classList.add('hidden');
                }
            });
        });
    </script>

    </div> {{-- end max-w content --}}
    </div> {{-- end flex-grow scroll wrapper --}}
</body>
</html>
```

## File: `resources/views/inventory-setup.blade.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Setup | DepEd Zamboanga City</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .step-content { display: none; }
        .step-content.active { display: block; animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        
        @keyframes fadeIn { 
            from { opacity: 0; transform: translateY(10px) scale(0.98); } 
            to { opacity: 1; transform: translateY(0) scale(1); } 
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .toast-enter { animation: slideInRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .toast-exit { animation: slideOutRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        .back-btn-cool {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .back-btn-cool:hover {
            border-color: #c00000;
            color: #c00000;
            box-shadow: 0 10px 15px -3px rgba(192, 0, 0, 0.1);
            transform: translateX(-4px);
        }
        html.dark .back-btn-cool {
            background: #141f33;
            border-color: #1e2e47;
            color: #94a3b8;
        }
        html.dark .back-btn-cool:hover {
            border-color: #c00000;
            color: white;
            background: #c00000;
        }

        /* â”€â”€ Excel-like registration table â”€â”€ */
        .xls-th {
            padding: 14px 16px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #64748b;
            white-space: nowrap;
            border-right: 1px solid #e2e8f0;
            border-bottom: 2px solid #f1f5f9;
            background: #f8fafc;
            position: sticky;
            top: 0;
            z-index: 20;
        }
        .xls-td {
            height: 48px;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            position: relative;
            padding: 0;
            background: #ffffff;
        }
        /* row highlight */
        .xls-row { transition: background 0.1s; }
        .xls-row:hover .xls-td { background-color: #f8fafc !important; }
        .xls-row:hover .xls-td.xls-sticky-col { background-color: #f8fafc !important; }
        /* inputs inside cells */
        .xls-input {
            width: 100%;
            padding: 11px 14px;
            font-size: 11.5px;
            font-weight: 600;
            color: #334155;
            background: transparent;
            border: 1px solid transparent;
            outline: none;
            box-sizing: border-box;
            line-height: 1.4;
            transition: all 0.2s;
        }
        .xls-input:focus {
            background: rgba(192,0,0,0.045);
            border-color: #c00000;
            box-shadow: 0 0 0 2px rgba(192,0,0,0.1);
        }
        .xls-input::placeholder { color: #cbd5e1; font-weight: 500; }
        .xls-const {
            display: flex;
            align-items: center;
            padding: 0 16px;
            height: 100%;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
            font-style: normal;
        }
        /* Scroll container: min-height = 10 rows, scrollable beyond */
        .xls-scroll-wrap {
            position: relative;
            overflow-x: auto;
            overflow-y: auto;
            width: 100%;
            max-width: 100%;
            min-height: 400px;
            max-height: calc(100vh - 450px);
            flex-grow: 1;
            background: #ffffff;
        }
        .pg-btn {
            padding: 8px 18px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-radius: 9999px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e2e8f0;
            background: white;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        html.dark .pg-btn {
            background: white;
            color: #1e293b;
            border-color: rgba(255,255,255,0.1);
        }
        .pg-btn:hover:not(:disabled) {
            border-color: #c00000;
            color: #c00000;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .pg-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        .pg-btn-active {
            background: #c00000 !important;
            color: white !important;
            border-color: #c00000 !important;
        }
        /* â”€â”€ Dark mode overrides (keeping table white/light even in dark mode if preferred, or matching dark theme) â”€â”€ */
        html.dark .xls-th {
            background: #0f172a !important;
            color: #94a3b8 !important;
            border-color: #1e293b !important;
        }
        html.dark .xls-td { 
            background: #0f172a !important;
            border-color: #1e293b !important; 
        }
        html.dark .xls-row:hover .xls-td { background-color: #1e293b !important; }
        html.dark .xls-row:hover .xls-td.xls-sticky-col { background-color: #1e293b !important; }
        /* Typebox enhancements */
        html.dark .xls-input { background: transparent; color: #e2e8f0; }
        html.dark .xls-input:focus { background: rgba(192,0,0,0.1); border-color: #c00000; box-shadow: 0 0 0 2px rgba(192,0,0,0.2); }
        html.dark .xls-input::placeholder { color: #475569; }
        html.dark .xls-scroll-wrap { background-color: #0f172a !important; }
        html.dark .xls-const { color: #94a3b8 !important; }
        html.dark .xls-sticky-col { background-color: #0f172a !important; }

        /* Custom Autocomplete */
        .custom-autocomplete {
            position: absolute;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            max-height: 200px;
            overflow-y: auto;
            z-index: 9999;
            min-width: 150px;
            font-family: inherit;
        }
        html.dark .custom-autocomplete {
            background: #141f33;
            border-color: #1e293b;
        }
        .custom-autocomplete-item {
            padding: 10px 14px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            color: #334155;
            transition: background 0.1s;
        }
        html.dark .custom-autocomplete-item {
            color: #cbd5e1;
        }
        .custom-autocomplete-item:hover {
            background: #f8fafc;
        }
        html.dark .custom-autocomplete-item:hover {
            background: #1a2535;
        }

        /* NEW Badge */
        .new-badge {
            position: absolute;
            top: 3px;
            right: 3px;
            font-size: 8px;
            font-weight: 900;
            background: #10b981;
            color: white;
            padding: 1px 4px;
            border-radius: 4px;
            text-transform: uppercase;
            pointer-events: none;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
            letter-spacing: 0.5px;
        }
        html.dark .new-badge {
            background: #059669;
            color: #ecfdf5;
            box-shadow: none;
        }

        html.dark .xls-const { color: #2e4060 !important; }
        /* Dark: section 1 card */
        html.dark #acqSourceCard { background-color: #141f33 !important; border-color: #1e2e47 !important; }
        html.dark #acqSourceCard .border-b { border-color: #1e2e47 !important; }
        html.dark #acqSourceInput {
            background-color: #0d1525 !important;
            border-color: #1e2e47 !important;
            color: #94a3b8 !important;
        }
        /* Dark: section 2 table card */
        html.dark #assetTableCard { background-color: #141f33 !important; border-color: #1e2e47 !important; }
        html.dark #assetToolbar { background-color: #141f33 !important; border-color: #1e2e47 !important; }
        html.dark #assetToolbar .bg-slate-100 { background-color: #0d1525 !important; }
        html.dark #assetToolbar .bg-slate-50 { background-color: #0d1525 !important; border-color: #1e2e47 !important; }
        html.dark #assetToolbar .text-slate-600 { color: #64748b !important; }
        html.dark .xls-scroll-wrap { background-color: #141f33 !important; }
        html.dark #assetSourceEmpty, html.dark #assetDistEmpty { background: #141f33 !important; }
        html.dark #assetSourceEmpty p, html.dark #assetDistEmpty p { color: #253550 !important; }
        html.dark #assetSourceEmpty svg, html.dark #assetDistEmpty svg { color: #253550 !important; }
        /* Dark: footer */
        html.dark #assetTableFooter { background-color: #0d1525 !important; border-color: #1e2e47 !important; }
        html.dark #assetTableFooter #rowCountLabel { color: #2e4060 !important; }
        /* Dark: sticky row num col */
        html.dark .xls-sticky-col { background-color: #141f33 !important; }
        html.dark .xls-row:hover .xls-sticky-col { background-color: #0d1525 !important; }

        /* Pagination Styles */
        .pg-btn {
            padding: 8px 16px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-radius: 12px;
            transition: all 0.2s;
            display: flex;
            items-center: center;
            gap: 6px;
        }
        .pg-btn:not(:disabled):hover { background: #f1f5f9; color: #c00000; }
        .pg-btn:disabled { opacity: 0.3; cursor: not-allowed; }
        html.dark .pg-btn { background: #1e293b !important; color: #cbd5e1 !important; }
        html.dark .pg-btn:not(:disabled):hover { background: #c00000 !important; color: white !important; }

        /* Column Coloring */
        .col-identity { background-color: #eff6ff !important; border-color: #dbeafe !important; }
        .col-context  { background-color: #f8fafc !important; border-color: #f1f5f9 !important; }
        .col-personnel{ background-color: #fffbeb !important; border-color: #fef3c7 !important; }
        .col-financial{ background-color: #eef2ff !important; border-color: #e0e7ff !important; }
        .col-temporal { background-color: #ecfdf5 !important; border-color: #d1fae5 !important; }
        .col-status   { background-color: #f5f3ff !important; border-color: #ede9fe !important; }

        html.dark .col-identity { background-color: rgba(30, 58, 138, 0.15) !important; border-color: rgba(30, 58, 138, 0.3) !important; }
        html.dark .col-context  { background-color: rgba(30, 41, 59, 0.15) !important; border-color: rgba(30, 41, 59, 0.3) !important; }
        html.dark .col-personnel{ background-color: rgba(120, 53, 15, 0.15) !important; border-color: rgba(120, 53, 15, 0.3) !important; }
        html.dark .col-financial{ background-color: rgba(49, 46, 129, 0.15) !important; border-color: rgba(49, 46, 129, 0.3) !important; }
        html.dark .col-temporal { background-color: rgba(6, 78, 59, 0.15) !important; border-color: rgba(6, 78, 59, 0.3) !important; }
        html.dark .col-status   { background-color: rgba(76, 29, 149, 0.15) !important; border-color: rgba(76, 29, 149, 0.3) !important; }

        /* Stronger background for TH */
        th.col-identity { background-color: #dbeafe !important; }
        th.col-context  { background-color: #f1f5f9 !important; }
        th.col-personnel{ background-color: #fef3c7 !important; }
        th.col-financial{ background-color: #e0e7ff !important; }
        th.col-temporal { background-color: #d1fae5 !important; }
        th.col-status   { background-color: #ede9fe !important; }

        html.dark th.col-identity { background-color: rgba(30, 58, 138, 0.4) !important; }
        html.dark th.col-context  { background-color: rgba(30, 41, 59, 0.4) !important; }
        html.dark th.col-personnel{ background-color: rgba(120, 53, 15, 0.4) !important; }
        html.dark th.col-financial{ background-color: rgba(49, 46, 129, 0.4) !important; }
        html.dark th.col-temporal { background-color: rgba(6, 78, 59, 0.4) !important; }
        html.dark th.col-status   { background-color: rgba(76, 29, 149, 0.4) !important; }
    </style>

</head>
<body class="bg-slate-50 min-h-screen flex text-slate-800 overflow-x-hidden relative">

    @if(session('success'))
        <div id="successToast" class="fixed top-8 right-8 z-[100] bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-4 rounded-2xl shadow-xl flex items-center gap-3 toast-enter">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-emerald-500">
                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
            </svg>
            <div class="flex flex-col">
                <span class="font-bold text-sm tracking-tight">Success</span>
                <span class="text-xs font-semibold opacity-90">{{ session('success') }}</span>
            </div>
            <button onclick="closeToast()" class="ml-4 text-emerald-400 hover:text-emerald-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <script>
            function closeToast() {
                const toast = document.getElementById('successToast');
                if(toast) {
                    toast.classList.remove('toast-enter');
                    toast.classList.add('toast-exit');
                    setTimeout(() => toast.remove(), 400);
                }
            }
            setTimeout(closeToast, 4000);
        </script>
    @endif

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">
        <header class="lg:hidden bg-white border-b border-slate-200 p-4 sticky top-0 z-30 flex items-center gap-4">
            <button onclick="toggleSidebar()" class="p-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-100 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/deped_logo.png') }}" class="h-6 w-auto">
                <span class="font-extrabold italic text-sm">DepEd ZC</span>
            </div>
        </header>

        <main id="mainContent" class="p-6 lg:p-10 max-w-5xl mx-auto w-full transition-all duration-300">
            <header class="flex justify-between items-center mb-12">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight italic">Inventory Setup</h2>
                    <p class="text-slate-500 text-sm font-medium italic">Zamboanga City Division Asset Management</p>
                </div>
                <button id="backBtn" onclick="goBack()" class="hidden px-6 py-3 back-btn-cool rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </button>
            </header>

            {{-- Step 1: Add or Edit Selection --}}
            <div id="step1" class="step-content active">
                <h3 class="text-center text-lg font-bold text-slate-400 uppercase tracking-[0.3em] mb-10">What would you like to do?</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 px-4 mb-10">
                    <div onclick="nextStep(2, 'add')" class="group bg-white p-12 rounded-[3rem] shadow-xl shadow-slate-200/60 border-2 border-transparent hover:border-[#c00000] transition-all duration-300 cursor-pointer text-center">
                        <div class="w-20 h-20 bg-red-50 text-[#c00000] rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-10 h-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <h4 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Add Item</h4>
                        <p class="text-slate-400 text-xs font-bold uppercase mt-3 tracking-widest leading-tight">Register new items or equipment to the system</p>
                    </div>

                    <div onclick="nextStep(2, 'building')" class="group bg-white p-12 rounded-[3rem] shadow-xl shadow-slate-200/60 border-2 border-transparent hover:border-[#c00000] transition-all duration-300 cursor-pointer text-center">
                        <div class="w-20 h-20 bg-red-50 text-[#c00000] rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-10 h-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                            </svg>
                        </div>
                        <h4 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Add Building</h4>
                        <p class="text-slate-400 text-xs font-bold uppercase mt-3 tracking-widest leading-tight">Register new school buildings or infrastructure units</p>
                    </div>
                </div>

                {{-- Bottom Row: Management Cards (Slim Horizontal side-by-side) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 px-4 mt-10">
                    {{-- Inventory Management --}}
                    <div onclick="nextStep(2, 'edit')" class="group bg-white p-6 rounded-[2.5rem] shadow-xl shadow-slate-200/40 border-2 border-transparent hover:border-blue-600 transition-all duration-300 cursor-pointer flex items-center justify-between relative overflow-hidden">
                        <div class="flex items-center gap-5 relative z-10">
                            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-7 h-7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                            </div>
                            <div class="text-left">
                                <h4 class="text-lg font-black text-slate-800 tracking-tight uppercase">Inventory Management</h4>
                                <p class="text-slate-400 text-[8px] font-bold uppercase tracking-widest mt-1">Master Registry Records</p>
                            </div>
                        </div>
                        <div class="text-blue-600 group-hover:translate-x-2 transition-transform relative z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                    </div>

                    {{-- Infrastructure Management --}}
                    <div onclick="nextStep(2, 'infra')" class="group bg-white p-6 rounded-[2.5rem] shadow-xl shadow-slate-200/40 border-2 border-transparent hover:border-emerald-600 transition-all duration-300 cursor-pointer flex items-center justify-between relative overflow-hidden">
                        <div class="flex items-center gap-5 relative z-10">
                            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-7 h-7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5-1.5l-3-1m-3.182-5.182L15 4.5" />
                                </svg>
                            </div>
                            <div class="text-left">
                                <h4 class="text-lg font-black text-slate-800 tracking-tight uppercase">Infrastructure Management</h4>
                                <p class="text-slate-400 text-[8px] font-bold uppercase tracking-widest mt-1">Buildings & Facilities</p>
                            </div>
                        </div>
                        <div class="text-emerald-600 group-hover:translate-x-2 transition-transform relative z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                    </div>
                </div>

            </div>

{{-- Step 2: Category Selection --}}
<div id="step2" class="step-content">
    <h3 id="step2Title" class="text-lg font-black text-slate-900 uppercase tracking-[0.3em] text-center mb-6 -mt-6">Select Category</h3>
    
<div id="categoryGrid" class="grid grid-cols-2 gap-6 max-w-3xl mx-auto px-4 mb-8">        
    {{-- Empty Grid --}}


</div>
</div>

{{-- â•â•â•â•â•â•â• STEP: ADD NEW RECORD â€” Registration Form â•â•â•â•â•â•â• --}}
    @include('partials.register-item-step')

    @include('partials.register-building-step')

    {{-- Step 3: Form Content --}}
            <div id="step3" class="step-content">
                @if($errors->any())
                    <div class="max-w-4xl mx-auto mb-6 bg-red-50 text-red-600 p-6 font-bold rounded-3xl shadow-sm border border-red-100 flex items-start gap-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-red-500 shrink-0">
                            <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h4 class="text-sm font-black tracking-tight mb-1">Please fix the following errors:</h4>
                            <ul class="list-disc list-inside text-xs font-semibold opacity-90 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="max-w-4xl mx-auto bg-white p-10 rounded-[3rem] shadow-2xl border border-slate-50 relative overflow-visible">
                    <div id="formContent"></div>
                </div>
            </div>

            @include('partials.inventory-edit-step')
            @include('partials.building-edit-step')

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let stepHistory = [1];
        let currentMode = '';
        let currentModule = '';

        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('step') === '2' && urlParams.get('mode') === 'edit') {
                nextStep(2, 'edit');
            }
            if (urlParams.get('step') === '2' && urlParams.get('mode') === 'add') {
                nextStep(2, 'add');
            }

            @if(session('success'))
                Swal.fire({
                    title: 'Registration Successful!',
                    text: @json(session('success')),
                    icon: 'success',
                    confirmButtonColor: '#10b981',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
            @endif
        });

        const rawCategories = {{ Js::from($categories) }};
        const rawItems = {{ Js::from($items) }};
        const rawSubItems = {{ Js::from($subItems) }};
        
        const rawDistricts = @json($districts);
        const rawLds = @json($legislativeDistricts);
        const rawQuadrants = @json($quadrants);
        const allSchoolsList = @json($allSchools);
        const allCustodiansList = @json($allCustodians);
        const rawStakeholders = @json($stakeholders);
        const rawOwnerships = @json($stakeholderOwnerships);
        const districtMap = {};
        rawDistricts.forEach(d => {
            districtMap[d.name] = { ld: d.legislative_district_id, quad: d.quadrant_name.replace('Quadrant ', '') };
        });

        let selectedSchoolsArray = [];
        let selectedSubItemsArray = [];

        function nextStep(step, value) {
    if (step === 2) {
        currentMode = value;

        // Add Item
        if (value === 'add') {
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById('stepAddNew').classList.add('active');
            document.getElementById('mainContent').classList.replace('max-w-5xl', 'max-w-full');
            stepHistory.push('addnew');
            updateBackButton();
            return;
        }

        // Edit Items (Inventory Management)
        if (value === 'edit') {
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById('stepInventoryEdit').classList.add('active');
            document.getElementById('mainContent').classList.replace('max-w-5xl', 'max-w-full');
            stepHistory.push('edit');
            updateBackButton();
            if (typeof initInventoryEdit === 'function') initInventoryEdit();
            return;
        }

        // Add Building
        if (value === 'building') {
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById('stepAddBuilding').classList.add('active');
            document.getElementById('mainContent').classList.replace('max-w-5xl', 'max-w-full');
            stepHistory.push('addbuilding');
            updateBackButton();
            return;
        }

        // Infrastructure Management (Building Editor)
        if (value === 'infra') {
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById('stepBuildingEdit').classList.add('active');
            document.getElementById('mainContent').classList.replace('max-w-5xl', 'max-w-full');
            stepHistory.push('infra');
            updateBackButton();
            if (typeof initBldgEdit === 'function') initBldgEdit();
            return;
        }
    }

    if (step === 3) {
        if (value === 'school') { window.location.href = '/inventory-modifier/school'; return; }
        if (value === 'distribution') { window.location.href = '/inventory-modifier'; return; }
        currentModule = value;
        renderForm();
    }

    document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
    document.getElementById('step' + step).classList.add('active');
    stepHistory.push(step);
    updateBackButton();
}

        function goBack() {
            if (stepHistory.length > 1) {
                const leavingStep = stepHistory[stepHistory.length - 1];
                stepHistory.pop();
                const prevStep = stepHistory[stepHistory.length - 1];

                if (leavingStep === 'addnew' || leavingStep === 'addbuilding' || leavingStep === 'edit' || leavingStep === 'infra') {
                    document.getElementById('mainContent').classList.replace('max-w-full', 'max-w-5xl');
                    document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
                    document.getElementById('step1').classList.add('active');
                    updateBackButton();
                    return;
                }

                document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
                const targetId = prevStep === 'addnew' ? 'stepAddNew' : ('step' + prevStep);
                document.getElementById(targetId).classList.add('active');
                
                updateBackButton();
            }
        }

        function updateBackButton() {
            const btn = document.getElementById('backBtn');
            btn.classList.toggle('hidden', stepHistory[stepHistory.length - 1] === 1);
        }

        function filterQuadrants() {
            const ld = document.getElementById('dist_ld').value;
            const quadSelect = document.getElementById('dist_quad');
            quadSelect.innerHTML = '<option value="">Select Quadrant</option>';
            if (ld) {
                const filtered = rawQuadrants.filter(q => q.legislative_district_id == ld);
                quadSelect.innerHTML += filtered.map(q => `<option value="${q.id}">${q.name}</option>`).join('');
            }
        }

        // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
        @include('partials.scripts.item-manager')
        @include('partials.scripts.autocomplete-engine')
    </script>

    @include('partials.inventory-modals')

</body>
</html>
```

## File: `resources/views/partials/download-reports.blade.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Reports | DepEd Zamboanga City</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }
        .custom-scroll::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .text-deped { color: #c00000; }
        .bg-deped { background-color: #c00000; }
        [x-cloak] { display: none !important; }
        
        .fade-enter { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>

<body class="bg-[#fcfcfd] min-h-screen flex text-slate-800 overflow-x-hidden" x-data="reportManager()">

@include('partials.sidebar')

<div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">

    {{-- Mobile Header --}}
    <header class="lg:hidden bg-white border-b p-4 flex items-center justify-between sticky top-0 z-30">
        <div class="flex items-center gap-3">
            <button onclick="toggleSidebar()" class="p-2 rounded-xl border bg-slate-50">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-slate-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/deped_logo.png') }}" class="h-6">
                <span class="font-black italic text-sm tracking-tight uppercase">DepEd ZC</span>
            </div>
        </div>
        <div class="w-8 h-8 bg-deped rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-lg shadow-red-100 italic">A</div>
    </header>

    <main class="p-6 lg:p-8 max-w-7xl mx-auto w-full">

        {{-- STEP 1: REPORT SELECTION --}}
        <div x-show="step === 1" x-transition class="fade-enter">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 gap-6">
                <div>
                    <h1 class="text-3xl lg:text-5xl font-black text-slate-900 tracking-tighter italic uppercase leading-none text-red-600">Report <span class="text-slate-900">Vault</span></h1>
                    <div class="flex items-center gap-3 mt-3">
                        <div class="w-8 h-1 bg-deped rounded-full"></div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.4em]">Select Classification to Download Report</p>
                    </div>
                </div>

                <button onclick="window.location.href='/dashboard'"
                    class="group px-6 py-3 bg-white border border-slate-200 rounded-2xl text-[9px] font-black uppercase tracking-widest text-slate-500 flex items-center gap-3 shadow-sm hover:border-deped hover:text-deped transition-all active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 transition-transform group-hover:-translate-x-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back to System
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 px-2">
                {{-- RPCPPE Card --}}
                <div @click="selectReport('RPCPPE')" class="group bg-white rounded-[3rem] border border-slate-100 shadow-xl p-10 hover:shadow-red-50 hover:border-red-100 transition-all duration-500 relative overflow-hidden flex flex-col justify-between cursor-pointer min-h-[380px]">
                    <div class="absolute -right-10 -top-10 w-48 h-48 bg-red-50 rounded-full opacity-50 blur-3xl group-hover:bg-red-100 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-red-50 text-deped rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 group-hover:rotate-6 transition-all border border-red-100 shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <h2 class="text-4xl font-black text-slate-900 uppercase tracking-tighter italic mb-2">RPCPPE</h2>
                        <span class="px-4 py-1.5 bg-red-50 text-deped text-[9px] font-black uppercase tracking-[0.3em] rounded-full border border-red-100 italic mb-6 inline-block shadow-sm">High-Value Assets</span>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] leading-relaxed mt-4 max-w-[280px]">Items valued at ₱50,000.00 and above. Official Physical Count Reporting.</p>
                    </div>
                    <div class="mt-auto pt-8 flex items-center gap-3 text-deped font-black uppercase italic tracking-[0.3em] text-[10px] group-hover:translate-x-2 transition-all">
                        Configure Report Options
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </div>
                </div>

                {{-- RPCSP Card --}}
                <div @click="selectReport('RPCSP')" class="group bg-white rounded-[3rem] border border-slate-100 shadow-xl p-10 hover:shadow-emerald-50 hover:border-emerald-100 transition-all duration-500 relative overflow-hidden flex flex-col justify-between cursor-pointer min-h-[380px]">
                    <div class="absolute -right-10 -top-10 w-48 h-48 bg-emerald-50 rounded-full opacity-50 blur-3xl group-hover:bg-emerald-100 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 group-hover:-rotate-6 transition-all border border-emerald-100 shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <h2 class="text-4xl font-black text-slate-900 uppercase tracking-tighter italic mb-2">RPCSP</h2>
                        <span class="px-4 py-1.5 bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-[0.3em] rounded-full border border-emerald-100 italic mb-6 inline-block shadow-sm group-hover:text-emerald-700 group-hover:bg-emerald-100 group-hover:border-emerald-200 transition-all">Semi-Expendable Assets</span>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] leading-relaxed mt-4 max-w-[280px]">Items valued below ₱50,000.00. Consumable Inventory Reporting.</p>
                    </div>
                    <div class="mt-auto pt-8 flex items-center gap-3 text-emerald-600 font-black uppercase italic tracking-[0.3em] text-[10px] group-hover:translate-x-2 transition-all">
                        Configure Report Options
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </div>
                </div>

                {{-- PIF Card --}}
                <div @click="selectReport('PIF')" class="group bg-white rounded-[3rem] border border-slate-100 shadow-xl p-10 hover:shadow-blue-50 hover:border-blue-100 transition-all duration-500 relative overflow-hidden flex flex-col justify-between cursor-pointer min-h-[380px]">
                    <div class="absolute -right-10 -top-10 w-48 h-48 bg-blue-50 rounded-full opacity-50 blur-3xl group-hover:bg-blue-100 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 group-hover:rotate-6 transition-all border border-blue-100 shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <h2 class="text-4xl font-black text-slate-900 uppercase tracking-tighter italic mb-2">PIF</h2>
                        <span class="px-4 py-1.5 bg-blue-50 text-blue-600 text-[9px] font-black uppercase tracking-[0.3em] rounded-full border border-blue-100 italic mb-6 inline-block shadow-sm group-hover:text-blue-700 group-hover:bg-blue-100 group-hover:border-blue-200 transition-all">Full Asset Inventory</span>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] leading-relaxed mt-4 max-w-[280px]">Property Inventory Form. Combined reporting of all assets regardless of valuation (High-Value & Semi-Expendable).</p>
                    </div>
                    <div class="mt-auto pt-8 flex items-center gap-3 text-blue-600 font-black uppercase italic tracking-[0.3em] text-[10px] group-hover:translate-x-2 transition-all">
                        Configure Report Options
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </div>
                </div>
            </div>
            
            <div></div>
        </div>

        {{-- STEP 2: CONFIGURE & DOWNLOAD --}}
        <div x-show="step === 2" x-transition x-cloak class="fade-enter">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-12 gap-6">
                <div class="flex items-center gap-5">
                    <button @click="step = 1" class="w-12 h-12 bg-white border border-slate-200 rounded-2xl flex items-center justify-center text-slate-400 hover:text-deped hover:border-deped shadow-lg shadow-slate-100 hover:scale-105 transition-all active:scale-90 group">
                        <svg class="w-6 h-6 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <div>
                        <h1 class="text-2xl lg:text-4xl font-black text-slate-900 tracking-tighter italic uppercase leading-none" x-text="selectedReport"></h1>
                        <div class="flex items-center gap-3 mt-2">
                            <span class="px-2.5 py-0.5 bg-red-50 text-deped text-[8px] font-black uppercase rounded border border-red-100 italic" x-text="reportSubtext"></span>
                            <div class="w-1 h-1 bg-slate-200 rounded-full"></div>
                            <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest italic">Live Configuration Mode</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="showFilters = !showFilters" 
                        class="px-6 py-3 bg-white border border-slate-200 rounded-2xl text-[9px] font-black uppercase tracking-widest text-slate-500 flex items-center gap-3 shadow-sm hover:border-deped hover:text-deped transition-all active:scale-95 italic">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>
                        <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'"></span>
                    </button>

                    <button @click="download()" class="px-6 py-3 bg-deped text-white rounded-2xl text-[9px] font-black uppercase tracking-widest flex items-center gap-3 shadow-lg shadow-red-100 hover:bg-red-700 transition-all active:scale-95 italic">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Download Report
                    </button>
                </div>
            </div>

            <div x-show="showFilters" x-collapse class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8 lg:p-12 overflow-hidden relative mb-12">
                <div class="absolute -left-10 -top-10 w-32 h-32 bg-red-50/50 rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {{-- Classification --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Classification</label>
                            <select x-model="filters.classification" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Classifications</option>
                                <template x-for="c in filterOptions.classifications" :key="c">
                                    <option :value="c" x-text="c"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Category --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Category</label>
                            <select x-model="filters.category" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Categories</option>
                                <template x-for="cat in filterOptions.categories" :key="cat">
                                    <option :value="cat" x-text="cat"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Item --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Item</label>
                            <select x-model="filters.article" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Items</option>
                                <template x-for="item in filterOptions.items" :key="item">
                                    <option :value="item" x-text="item"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Cost Sorting --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Cost Sorting</label>
                            <select x-model="filters.sortCost" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">Default (ID)</option>
                                <option value="low_to_high">Low to High</option>
                                <option value="high_to_low">High to Low</option>
                            </select>
                        </div>

                        {{-- School Name --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">School Name</label>
                            <select x-model="filters.schoolName" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Schools</option>
                                <template x-for="school in filterOptions.schools" :key="school">
                                    <option :value="school" x-text="school"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Source of Acquisition --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Source of Acquisition</label>
                            <select x-model="filters.source" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Sources</option>
                                <template x-for="s in filterOptions.sources" :key="s">
                                    <option :value="s" x-text="s"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Mode of Acquisition --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Mode of Acquisition</label>
                            <select x-model="filters.mode" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Modes</option>
                                <template x-for="m in filterOptions.modes" :key="m">
                                    <option :value="m" x-text="m"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Date Acquired --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Date Acquired (Acceptance)</label>
                            <input type="date" x-model="filters.dateAcquired" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500">
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-50 flex justify-end gap-3">
                        <button @click="clearFilters()" class="px-8 py-3 bg-slate-100 text-slate-500 rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all active:scale-95 italic">Clear All Filters</button>
                        <button @click="applyFilters()" class="px-8 py-3 bg-slate-900 text-white rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-deped transition-all active:scale-95 italic">Apply Configuration</button>
                    </div>
                </div>
            </div>

            {{-- LIVE PREVIEW TABLE --}}
            <div class="mb-12 bg-white rounded-[2rem] border border-slate-100 shadow-xl overflow-hidden relative fade-enter">
                <div class="p-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest italic" x-text="selectedReport + ' Preview'"></h3>
                        <p class="text-[10px] text-slate-400 font-bold tracking-wider mt-1 uppercase" x-text="'Showing ' + previewRows.length + ' exact matched assets'"></p>
                    </div>
                    <div x-show="loading" class="w-5 h-5 border-2 border-deped border-t-transparent rounded-full animate-spin"></div>
                </div>
                
                <div class="overflow-x-auto custom-scroll transition-all duration-300" :class="showFilters ? 'max-h-[400px]' : 'max-h-[750px]'">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead class="sticky top-0 bg-slate-50 z-10 shadow-sm">
                            <tr class="text-[9px] font-black text-slate-400 uppercase tracking-widest border-b-2 border-slate-100">
                                <th class="px-4 py-4">#</th>
                                <th class="px-4 py-4">Region</th>
                                <th class="px-4 py-4">Division</th>
                                <th class="px-4 py-4">School Type</th>
                                <th class="px-4 py-4">School ID</th>
                                <th class="px-4 py-4">School Name</th>
                                <th class="px-4 py-4 text-slate-900">Article</th>
                                <th class="px-4 py-4">Description</th>
                                <th class="px-4 py-4">Classification</th>
                                <th class="px-4 py-4">Occupancy</th>
                                <th class="px-4 py-4">Location</th>
                                <th class="px-4 py-4">Acq. Date</th>
                                <th class="px-4 py-4 text-[#c00000]">Property No.</th>
                                <th class="px-4 py-4 text-right">Cost</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs">
                            <template x-for="(row, index) in paginatedRows" :key="index">
                                <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                    <td class="px-4 py-3 text-[10px] font-black text-slate-400" x-text="(currentPage - 1) * itemsPerPage + index + 1"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.region"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.division"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.office_school_type"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.school_id"></td>
                                    <td class="px-4 py-3 font-bold text-slate-700" x-text="row.office_school_name"></td>
                                    <td class="px-4 py-3 font-black text-slate-900" x-text="row.article"></td>
                                    <td class="px-4 py-3 text-slate-500 text-[10px] truncate max-w-[200px]" :title="row.description" x-text="row.description"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.classification"></td>
                                    <td class="px-4 py-3 text-slate-500 text-[10px]" x-text="row.nature_of_occupancy"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.location"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.acquisition_date"></td>
                                    <td class="px-4 py-3 font-black text-[#c00000]" x-text="row.property_number"></td>
                                    <td class="px-4 py-3 font-black text-right" x-text="'₱' + parseFloat(row.acquisition_cost).toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                                </tr>
                            </template>
                            <tr x-show="previewRows.length === 0 && !loading">
                                <td colspan="14" class="px-4 py-12 text-center text-slate-400 text-xs font-bold uppercase tracking-widest italic">No matching records found. Adjust your filters.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination Controls --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between" x-show="previewRows.length > 0">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        Showing <span x-text="((currentPage - 1) * itemsPerPage) + 1"></span> to <span x-text="Math.min(currentPage * itemsPerPage, previewRows.length)"></span> of <span x-text="previewRows.length"></span>
                    </span>
                    <div class="flex items-center gap-2">
                        <button @click="prevPage()" :disabled="currentPage === 1" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-[10px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">Prev</button>
                        <span class="text-[10px] font-bold text-slate-700 px-3">Page <span x-text="currentPage"></span> / <span x-text="totalPages"></span></span>
                        <button @click="nextPage()" :disabled="currentPage === totalPages" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-[10px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">Next</button>
                    </div>
                </div>
            </div>

            <div class="p-8 bg-white border border-slate-100 rounded-[3rem] flex items-center gap-6 shadow-sm fade-enter">
                <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center border border-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">System Information</p>
                    <p class="text-[10px] font-bold text-slate-500 uppercase mt-1 leading-relaxed">Generated reports are in Excel format. If you need specialized data exports, please contact the AMU Administrator.</p>
                </div>
            </div>
        </div>

    </main>
</div>

    <form id="downloadForm" method="POST" action="{{ route('assets.reports.download_rpc') }}" class="hidden">
        @csrf
        <input type="hidden" name="report_type" id="downloadReportType">
        <input type="hidden" name="filters" id="downloadFilters">
    </form>

    <script>
    function reportManager() {
        return {
            step: 1,
            showFilters: true,
            loading: false,
            selectedReport: '',
            reportSubtext: '',
            previewRows: [],
            currentPage: 1,
            itemsPerPage: 50,
            filterOptions: {
                classifications: [],
                categories: [],
                items: [],
                schools: [],
                sources: [],
                modes: []
            },
            filters: {
                classification: '',
                category: '',
                article: '',
                sortCost: '',
                schoolName: '',
                source: '',
                mode: '',
                dateAcquired: ''
            },

            get totalPages() {
                return Math.max(1, Math.ceil(this.previewRows.length / this.itemsPerPage));
            },

            get paginatedRows() {
                const start = (this.currentPage - 1) * this.itemsPerPage;
                return this.previewRows.slice(start, start + this.itemsPerPage);
            },

            nextPage() {
                if (this.currentPage < this.totalPages) this.currentPage++;
            },

            prevPage() {
                if (this.currentPage > 1) this.currentPage--;
            },

            selectReport(type) {
                this.selectedReport = type;
                if (type === 'RPCPPE') {
                    this.reportSubtext = '₱50,000.00 and Above valuation';
                } else if (type === 'RPCSP') {
                    this.reportSubtext = '₱49,999.00 and Below valuation';
                } else {
                    this.reportSubtext = 'Combined Asset Valuation (All Items)';
                }
                this.step = 2;
                this.fetchFilterOptions();
                this.clearFilters();
            },

            fetchFilterOptions() {
                fetch('{{ route("api.reports.filters") }}?report_type=' + this.selectedReport)
                .then(res => res.json())
                .then(data => {
                    this.filterOptions.classifications = data.classifications || [];
                    this.filterOptions.categories = data.categories || [];
                    this.filterOptions.items = data.items || [];
                    this.filterOptions.schools = data.schools || [];
                    this.filterOptions.sources = data.sources || [];
                    this.filterOptions.modes = data.modes || [];
                })
                .catch(err => console.error("Failed to fetch filter options", err));
            },

            clearFilters() {
                this.filters = {
                    classification: '',
                    category: '',
                    article: '',
                    sortCost: '',
                    schoolName: '',
                    source: '',
                    mode: '',
                    dateAcquired: ''
                };
                this.applyFilters();
            },

            applyFilters() {
                this.loading = true;
                
                fetch('{{ route("api.reports.preview") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        report_type: this.selectedReport,
                        filters: this.filters
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.previewRows = data.rows || [];
                    this.currentPage = 1;
                })
                .catch(err => {
                    console.error("Preview fetch error:", err);
                    Swal.fire('Error', 'Failed to fetch preview data.', 'error');
                })
                .finally(() => {
                    this.loading = false;
                });
            },

            download() {
                if (this.previewRows.length === 0) {
                    Swal.fire('Empty Report', 'No assets match your current filters.', 'info');
                    return;
                }

                Swal.fire({
                    title: 'Generating Report...',
                    html: `Generating exact ${this.selectedReport} Template with <strong>${this.previewRows.length}</strong> assets.`,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: () => { Swal.showLoading() },
                    willClose: () => {
                        document.getElementById('downloadReportType').value = this.selectedReport;
                        document.getElementById('downloadFilters').value = JSON.stringify(this.filters);
                        document.getElementById('downloadForm').submit();
                    }
                });
            }
        }
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (sidebar && overlay) {
            const isHidden = sidebar.classList.contains('-translate-x-full');
            if (isHidden) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('expanded');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.style.opacity = "1", 10);
                document.body.classList.add('overflow-hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('expanded');
                overlay.style.opacity = "0";
                setTimeout(() => {
                    overlay.classList.add('hidden');
                }, 300);
                document.body.classList.remove('overflow-hidden');
            }
        }
    }
</script>

</body>
</html>
```

## File: `resources/views/register-building.blade.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Building Records | DepEd Zamboanga City</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; border: 2px solid transparent; background-clip: padding-box; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #f87171; border: 2px solid transparent; background-clip: padding-box; }
        .back-btn-cool { background: white; border: 1px solid #e2e8f0; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .xls-th { padding: 14px 16px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: #475569; white-space: nowrap; border-right: 1px solid #e2e8f0; border-bottom: 2px solid #cbd5e1; background: #f8fafc; position: sticky; top: 0; z-index: 20; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .xls-td { height: 52px; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; vertical-align: middle; padding: 0; background: white; transition: all 0.3s ease; }
        .xls-row { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; position: relative; }
        .xls-row:hover { transform: translateX(4px); z-index: 10; }
        .xls-row:hover .xls-td { background-color: rgba(192, 0, 0, 0.03) !important; border-bottom-color: #c00000; }
        .xls-row:hover .xls-td:first-child { box-shadow: inset 4px 0 0 #c00000; }
        .xls-row:active { transform: scale(0.995); transition: all 0.1s; }
        .xls-row:active .xls-td { background-color: rgba(192, 0, 0, 0.08) !important; }
        .xls-const { display: flex; align-items: center; padding: 0 16px; height: 100%; font-size: 11.5px; font-weight: 700; color: inherit; white-space: nowrap; }
        .xls-scroll-wrap { position: relative; overflow-x: auto; overflow-y: auto; height: calc(100vh - 450px); min-height: 400px; background: white; flex-grow: 1; transition: height 0.4s cubic-bezier(0.4, 0, 0.2, 1); border-top: 1px solid #e2e8f0; }
        .xls-scroll-wrap.expanded { height: calc(100vh - 250px); }
        .pg-btn {
            padding: 8px 18px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-radius: 9999px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e2e8f0;
            background: white;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.05);
        }
        .pg-btn:hover:not(:disabled) {
            border-color: #ef4444;
            color: #ef4444;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.15);
        }
        .pg-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
            background: #f1f5f9;
        }
        
        /* Dark Mode Overrides */
        html.dark body { background-color: #0f172a; color: #f8fafc; }
        html.dark .bg-white { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .text-slate-800 { color: #f8fafc !important; }
        html.dark .text-slate-900 { color: #f8fafc !important; }
        html.dark .bg-slate-50 { background-color: #0f172a !important; border-color: #1e293b !important; }
        html.dark .bg-slate-50\/50 { background-color: #1e293b !important; }
        html.dark .border-t { border-color: #334155 !important; }
        html.dark .xls-td { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .xls-th { background-color: #0f172a !important; border-color: #334155 !important; color: #94a3b8 !important; }
        html.dark .xls-scroll-wrap { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .dep-tooltip { background-color: rgba(30, 41, 59, 0.9) !important; border-color: #334155 !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important; }
        html.dark .dep-stat-box { background-color: #0f172a !important; border-color: #334155 !important; }
        html.dark .dep-tooltip p { color: #94a3b8 !important; }
        html.dark .dep-tooltip .border-t { border-color: #334155 !important; }
        html.dark #tipYear1 { color: #f87171 !important; }
        html.dark #tipYear25 { color: #34d399 !important; }
        
        /* Glass Indicator Box */
        .glass-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }


        /* Depreciation Tooltip */
        .dep-tooltip {
            position: fixed;
            pointer-events: none;
            z-index: 1000;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1.5rem;
            width: 280px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(12px);
            opacity: 0;
            transform: scale(0.95);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .dep-tooltip.active {
            opacity: 1;
            transform: scale(1);
        }
        .dep-stat-box {
            padding: 10px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }



        /* Custom Autocomplete */
        .custom-autocomplete {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-top: 4px;
            max-height: 250px;
            overflow-y: auto;
            z-index: 50;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .custom-autocomplete-item {
            padding: 12px 16px;
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            text-transform: uppercase;
        }
        .custom-autocomplete-item:hover {
            background: #f8fafc;
            color: #c00000;
            padding-left: 20px;
        }
        html.dark .custom-autocomplete {
            background: #1e293b;
            border-color: #334155;
        }
        html.dark .custom-autocomplete-item {
            color: #94a3b8;
        }
        html.dark .custom-autocomplete-item:hover {
            background: #0f172a;
            color: #f8fafc;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-900 overflow-x-hidden selection:bg-red-100 selection:text-red-900 relative">
    <div class="absolute inset-0 z-[-1] opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 24px 24px;"></div>

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll relative">
    <div class="w-full mx-auto p-6 lg:p-10 min-h-screen flex flex-col relative z-10">

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10 px-2 animate-fade">
            <div class="shrink-0">
                <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-700 to-red-500 uppercase italic leading-none drop-shadow-sm tracking-tight">Building Records</h2>
                <p class="text-slate-500 text-[11px] font-bold uppercase tracking-[0.25em] mt-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.6)]"></span>
                    Master Building Registry
                </p>
            </div>

            <div class="flex-grow max-w-2xl relative">
                <div class="relative group">
                    <input type="text" id="bldgFilterSchool" oninput="bldgDebouncedSearch()" placeholder="SEARCH SCHOOL NAME OR PROPERTY #..." autocomplete="off" class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-700 shadow-sm pr-12 group-hover:border-slate-200">
                    <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 shrink-0">
                <button onclick="toggleBldgColumns()" id="toggleColumnsBtn" class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:scale-110 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    View All Columns
                </button>
                <button onclick="toggleBldgFilters()" id="toggleFilterBtn" class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:rotate-12 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                    Show Filters
                </button>
                <a href="/dashboard" class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </div>

        </div>

        <!-- Filter Configuration -->
        <div id="bldgFilterSection" class="hidden bg-white rounded-[2.5rem] shadow-lg border border-slate-100 p-8 mb-8 relative z-50 animate-fade transition-all duration-300 origin-top">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 relative z-10">
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Classification</label>
                    <select id="bldgFilterClass" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">All Classifications</option>
                    </select>
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Office Type</label>
                    <select id="bldgFilterType" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">All Types</option>
                    </select>
                </div>

                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Cost Sorting</label>
                    <select id="bldgFilterSort" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">Default (ID)</option>
                        <option value="low_to_high">Acquisition Cost: Low to High</option>
                        <option value="high_to_low">Acquisition Cost: High to Low</option>
                    </select>
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Location</label>
                    <select id="bldgFilterLoc" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">All Locations</option>
                    </select>
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Acquisition Date</label>
                    <input type="date" id="bldgFilterDate" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic text-red-500">Data Integrity (Empty Fields)</label>
                    <select id="bldgFilterIntegrity" class="w-full bg-slate-50 border-red-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">No Integrity Filter</option>
                        <option value="region">Missing Region</option>
                        <option value="division">Missing Division</option>
                        <option value="office_type">Missing Office Type</option>
                        <option value="school_identifier">Missing School ID</option>
                        <option value="office_name">Missing School Name</option>
                        <option value="address">Missing Address</option>
                        <option value="storeys">Missing Storeys</option>
                        <option value="classrooms">Missing Classrooms</option>
                        <option value="article">Missing Article</option>
                        <option value="description">Missing Description</option>
                        <option value="classification">Missing Classification</option>
                        <option value="occupancy_nature">Missing Occupancy</option>
                        <option value="location">Missing Location</option>
                        <option value="date_constructed">Missing Date Constructed</option>
                        <option value="acquisition_date">Missing Acquisition Date</option>
                        <option value="property_number">Missing Property Number</option>
                        <option value="acquisition_cost">Missing Acquisition Cost</option>
                        <option value="estimated_useful_life">Missing Useful Life</option>
                        <option value="appraised_value">Missing Appraised Value</option>
                        <option value="appraisal_date">Missing Appraisal Date</option>
                    </select>
                </div>
            </div>
                <div class="mt-8 flex justify-end items-center gap-8 relative z-10 pt-6 border-t border-slate-100/60">
                    <button onclick="clearBldgFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-[#c00000] hover:-translate-y-0.5 transition-all duration-300 italic">Clear All Filters</button>
                    <button onclick="bldgFetchData()" class="px-8 py-3 bg-gradient-to-r from-red-700 to-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:from-red-800 hover:to-red-600 transition-all duration-300 active:translate-y-0 shadow-lg shadow-red-500/30 italic transform hover:-translate-y-0.5 group flex items-center gap-2">
                        Apply Configuration
                        <svg class="w-3.5 h-3.5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>
        </div>

        <div class="bg-white rounded-[2rem] border border-slate-200/60 shadow-xl shadow-slate-200/50 overflow-hidden flex flex-col animate-fade relative ring-1 ring-black/5">
            <div class="xls-scroll-wrap expanded">
                <table id="bldgTable" class="w-full border-collapse" style="min-width:1200px;">
                    <thead id="bldgHeader">
                        <tr>
                            <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                            <th class="xls-th" style="min-width:100px">School ID</th>
                            <th class="xls-th" style="min-width:200px">Office/School Name</th>
                            <th class="xls-th" style="min-width:140px">Article</th>
                            <th class="xls-th" style="min-width:170px">Description</th>
                            <th class="xls-th" style="min-width:130px">Property No.</th>
                            <th class="xls-th" style="min-width:70px">Storeys</th>
                            <th class="xls-th" style="min-width:90px">Classrooms</th>
                            <th class="xls-th text-right" style="min-width:120px">Acq. Cost (₱)</th>
                            <th class="xls-th" style="min-width:120px">Date Constructed</th>
                        </tr>
                    </thead>
                    <tbody id="bldgBody"></tbody>
                </table>
                
                {{-- Loading State --}}
                <div id="bldgLoading" class="absolute inset-0 bg-white/80 backdrop-blur-[4px] z-50 flex items-center justify-center hidden transition-all duration-300">
                    <div class="flex flex-col items-center gap-5 bg-white px-10 py-8 rounded-3xl shadow-2xl shadow-slate-200/50 border border-slate-100">
                        <div class="w-12 h-12 border-4 border-slate-100 border-t-red-600 rounded-full animate-spin"></div>
                        <p class="text-[10px] font-black text-slate-800 uppercase tracking-widest italic animate-pulse">Fetching Building Data...</p>
                    </div>
                </div>

                {{-- Empty State --}}
                <div id="bldgEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none transition-all duration-300 bg-white/50 backdrop-blur-[2px]">
                    <div class="inline-flex flex-col items-center gap-4 bg-slate-50/80 px-12 py-10 rounded-[2.5rem] border border-dashed border-slate-200 shadow-sm">
                        <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center text-red-400 shadow-inner">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21"/></svg>
                        </div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.25em]">No buildings found — adjust filters</p>
                    </div>
                </div>
            </div>

            <div id="bldgTableFooter" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between relative z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <div class="flex items-center gap-6">
                    <p id="bldgRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                    <div id="bldgPaginationControls" class="flex items-center gap-3 border-l border-slate-200 pl-6">
                        <button onclick="bldgPrevPage()" id="bldgPrevBtn" class="pg-btn">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                            Prev
                        </button>
                        <div class="glass-indicator">
                            <span id="bldgCurrentPage" class="text-[10px] font-black text-red-600">1</span>
                            <span class="text-[10px] font-bold text-slate-500">/</span>
                            <span id="bldgTotalPages" class="text-[10px] font-black text-slate-500">1</span>
                        </div>
                        <button onclick="bldgNextPage()" id="bldgNextBtn" class="pg-btn">
                            Next
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <div></div>
            </div>
        </div>
    </div>
    </div>

    <script>
        let bldgRowsData = [];
        let bldgCurrentPage = 1;
        const bldgRowsPerPage = 50;
        let allSchools = [];
        let isAutocompleteInit = false;
        let bldgShowAllColumns = false;

        function toggleBldgColumns() {
            bldgShowAllColumns = !bldgShowAllColumns;
            const btn = document.getElementById('toggleColumnsBtn');
            const table = document.getElementById('bldgTable');
            const thead = document.getElementById('bldgHeader');
            
            if (bldgShowAllColumns) {
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg> Hide Extra Columns`;
                table.style.minWidth = '2400px';
                thead.innerHTML = `<tr>
                    <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                    <th class="xls-th" style="min-width:90px">Region</th>
                    <th class="xls-th" style="min-width:190px">Division</th>
                    <th class="xls-th" style="min-width:140px">Office/School Type</th>
                    <th class="xls-th" style="min-width:100px">School ID</th>
                    <th class="xls-th" style="min-width:200px">Office/School Name</th>
                    <th class="xls-th" style="min-width:180px">Address</th>
                    <th class="xls-th" style="min-width:70px">Storeys</th>
                    <th class="xls-th" style="min-width:90px">Classrooms</th>
                    <th class="xls-th" style="min-width:140px">Article</th>
                    <th class="xls-th" style="min-width:170px">Description</th>
                    <th class="xls-th" style="min-width:130px">Classification</th>
                    <th class="xls-th" style="min-width:130px">Occupancy</th>
                    <th class="xls-th" style="min-width:150px">Location</th>
                    <th class="xls-th" style="min-width:120px">Date Constructed</th>
                    <th class="xls-th" style="min-width:120px">Acquisition Date</th>
                    <th class="xls-th" style="min-width:130px">Property No.</th>
                    <th class="xls-th text-right" style="min-width:120px">Acq. Cost (₱)</th>
                    <th class="xls-th text-center" style="min-width:100px">Est. Useful Life</th>
                    <th class="xls-th text-right" style="min-width:120px">Appraised Value</th>
                    <th class="xls-th" style="min-width:120px">Appraisal Date</th>
                    <th class="xls-th" style="min-width:140px">Remarks</th>
                </tr>`;
            } else {
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg> View All Columns`;
                table.style.minWidth = '1200px';
                thead.innerHTML = `<tr>
                    <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                    <th class="xls-th" style="min-width:100px">School ID</th>
                    <th class="xls-th" style="min-width:200px">Office/School Name</th>
                    <th class="xls-th" style="min-width:140px">Article</th>
                    <th class="xls-th" style="min-width:170px">Description</th>
                    <th class="xls-th" style="min-width:130px">Property No.</th>
                    <th class="xls-th" style="min-width:70px">Storeys</th>
                    <th class="xls-th" style="min-width:90px">Classrooms</th>
                    <th class="xls-th text-right" style="min-width:120px">Acq. Cost (₱)</th>
                    <th class="xls-th" style="min-width:120px">Date Constructed</th>
                </tr>`;
            }
            renderBldgTable();
        }

        async function bldgFetchFilters() {
            try {
                const res = await fetch("{{ route('api.buildings.filters') }}");
                const data = await res.json();
                
                const populate = (id, list) => {
                    const el = document.getElementById(id);
                    const currentVal = el.value;
                    el.innerHTML = `<option value="">All ${id.replace('bldgFilter', '')}s</option>`;
                    list.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item;
                        opt.textContent = item;
                        if(item === currentVal) opt.selected = true;
                        el.appendChild(opt);
                    });
                };
                
                populate('bldgFilterClass', data.classifications);
                populate('bldgFilterType', data.officeTypes);
                populate('bldgFilterLoc', data.locations);
                
                allSchools = data.schools;

            } catch (e) { console.error('Failed to fetch building filters', e); }
        }

        let bldgSearchTimer;
        function bldgDebouncedSearch() {
            clearTimeout(bldgSearchTimer);
            bldgSearchTimer = setTimeout(() => {
                bldgCurrentPage = 1;
                bldgFetchData();
            }, 500);
        }


        async function bldgFetchData() {
            const loading = document.getElementById('bldgLoading');
            loading.classList.remove('hidden');
            const filters = {
                classification: document.getElementById('bldgFilterClass').value,
                officeType: document.getElementById('bldgFilterType').value,
                schoolName: document.getElementById('bldgFilterSchool').value,
                location: document.getElementById('bldgFilterLoc').value,
                sortCost: document.getElementById('bldgFilterSort').value,
                dateAcquired: document.getElementById('bldgFilterDate').value,
                emptyCol: document.getElementById('bldgFilterIntegrity').value
            };
            try {
                const res = await fetch("{{ route('api.buildings.preview') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ filters: filters })
                });
                const data = await res.json();
                bldgRowsData = data.rows || [];
                bldgCurrentPage = 1;
                renderBldgTable();
                bldgFetchFilters();
            } catch (e) {
                console.error('Failed to fetch buildings', e);
                Swal.fire('Error', 'Failed to load building data.', 'error');
            } finally {
                loading.classList.add('hidden');
            }
        }

        function clearBldgFilters() {
            document.getElementById('bldgFilterClass').value = '';
            document.getElementById('bldgFilterType').value = '';
            document.getElementById('bldgFilterSchool').value = '';
            document.getElementById('bldgFilterSort').value = '';
            document.getElementById('bldgFilterLoc').value = '';
            document.getElementById('bldgFilterDate').value = '';
            document.getElementById('bldgFilterIntegrity').value = '';
            bldgCurrentPage = 1;
            bldgFetchData();
        }

        function renderBldgTable() {
            const tbody = document.getElementById('bldgBody');
            tbody.innerHTML = '';
            if (bldgRowsData.length === 0) {
                document.getElementById('bldgEmpty').classList.remove('hidden');
                document.getElementById('bldgRowCountLabel').textContent = '0 Rows';
                return;
            }
            document.getElementById('bldgEmpty').classList.add('hidden');
            const start = (bldgCurrentPage - 1) * bldgRowsPerPage;
            const pageData = bldgRowsData.slice(start, start + bldgRowsPerPage);
            pageData.forEach((row, idx) => {
                const displayNum = start + idx + 1;
                const tr = document.createElement('tr');
                tr.className = 'xls-row group border-b border-slate-100';
                tr.style.cursor = 'pointer';
                tr.onclick = (e) => {
                    if (e.target.closest('a')) return;
                    window.location.href = `/buildings/${row.id}`;
                };
                
                const cell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const">${val || ''}</span></td>`;
                const numCell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const justify-center">${val || '0'}</span></td>`;
                const costCell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const justify-end font-bold text-red-600">₱ ${val ? parseFloat(val).toLocaleString(undefined, {minimumFractionDigits: 2}) : '0.00'}</span></td>`;

                if (bldgShowAllColumns) {
                    tr.innerHTML = `
                        <td class="xls-td text-center sticky left-0 w-10 z-10"><span class="text-[10px] font-black text-slate-500">${displayNum}</span></td>
                        ${cell(row.region)}
                        ${cell(row.division)}
                        ${cell(row.office_type)}
                        ${cell(row.school_identifier)}
                        ${cell(row.office_name)}
                        ${cell(row.address)}
                        ${numCell(row.storeys)}
                        ${numCell(row.classrooms)}
                        ${cell(row.article)}
                        ${cell(row.description)}
                        ${cell(row.classification)}
                        ${cell(row.occupancy_nature)}
                        ${cell(row.location)}
                        ${cell(row.date_constructed)}
                        ${cell(row.acquisition_date)}
                        ${cell(row.property_number)}
                        ${costCell(row.acquisition_cost)}
                        ${numCell(row.estimated_useful_life)}
                        ${costCell(row.appraised_value)}
                        ${cell(row.appraisal_date)}
                        ${cell(row.remarks)}
                    `;
                } else {
                    tr.innerHTML = `
                        <td class="xls-td text-center sticky left-0 w-10 z-10"><span class="text-[10px] font-black text-slate-500">${displayNum}</span></td>
                        ${cell(row.school_identifier)}
                        ${cell(row.office_name, 'font-bold text-[#c00000]')}
                        ${cell(row.article)}
                        ${cell(row.description)}
                        ${cell(row.property_number)}
                        ${numCell(row.storeys)}
                        ${numCell(row.classrooms)}
                        ${costCell(row.acquisition_cost)}
                        ${cell(row.date_constructed)}
                    `;
                }
                tr.onmouseenter = (e) => showDepTooltip(e, row);
                tr.onmouseleave = () => hideDepTooltip();
                tr.onmousemove = (e) => moveDepTooltip(e);
                tbody.appendChild(tr);
            });
            const totalPages = Math.ceil(bldgRowsData.length / bldgRowsPerPage) || 1;
            document.getElementById('bldgRowCountLabel').textContent = bldgRowsData.length + " Buildings Found";
            
            document.getElementById('bldgCurrentPage').textContent = bldgCurrentPage;
            document.getElementById('bldgTotalPages').textContent = totalPages;
            document.getElementById('bldgPrevBtn').disabled = bldgCurrentPage === 1;
            document.getElementById('bldgNextBtn').disabled = bldgCurrentPage === totalPages;
        }

        function bldgPrevPage() { if (bldgCurrentPage > 1) { bldgCurrentPage--; renderBldgTable(); } }
        function bldgNextPage() { const t = Math.ceil(bldgRowsData.length/bldgRowsPerPage); if (bldgCurrentPage < t) { bldgCurrentPage++; renderBldgTable(); } }

        function toggleBldgFilters() {
            const section = document.getElementById('bldgFilterSection');
            const btn = document.getElementById('toggleFilterBtn');
            const tableWrap = document.querySelector('.xls-scroll-wrap');
            
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
                tableWrap.classList.remove('expanded');
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Hide Filters`;
            } else {
                section.classList.add('hidden');
                tableWrap.classList.add('expanded');
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Show Filters`;
            }
        }

        function showDepTooltip(e, row) {
            const tip = document.getElementById('depTooltip');
            const cost = parseFloat(row.acquisition_cost) || 0;
            const life = parseInt(row.estimated_useful_life) || 1;
            
            if (cost === 0) return;

            const residual = cost * 0.05;
            const annualDep = (cost - residual) / life;
            
            const year1Val = Math.max(residual, cost - annualDep);
            const year25Val = Math.max(residual, cost - (annualDep * 25));

            document.getElementById('tipAnnualDep').textContent = '₱ ' + annualDep.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('tipYear1').textContent = '₱ ' + year1Val.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('tipYear25').textContent = '₱ ' + year25Val.toLocaleString(undefined, {minimumFractionDigits: 2});

            tip.classList.add('active');
            moveDepTooltip(e);
        }

        function moveDepTooltip(e) {
            const tip = document.getElementById('depTooltip');
            const x = e.clientX + 20;
            const y = e.clientY + 20;
            
            // Boundary check
            const tipRect = tip.getBoundingClientRect();
            let finalX = x;
            let finalY = y;

            if (x + tipRect.width > window.innerWidth) finalX = e.clientX - tipRect.width - 20;
            if (y + tipRect.height > window.innerHeight) finalY = e.clientY - tipRect.height - 20;

            tip.style.left = finalX + 'px';
            tip.style.top = finalY + 'px';
        }

        function hideDepTooltip() {
            document.getElementById('depTooltip').classList.remove('active');
        }



        document.addEventListener('DOMContentLoaded', () => {
            bldgFetchFilters();
            bldgFetchData();
        });
    </script>
    <!-- Depreciation Tooltip -->
    <div id="depTooltip" class="dep-tooltip">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-lg bg-red-600/20 flex items-center justify-center">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Financial Projection</p>
                <h4 class="text-xs font-bold text-slate-800 uppercase italic">Depreciation Preview</h4>
            </div>
        </div>
        
        <div class="space-y-3">
            <div class="dep-stat-box">
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Annual Expense</p>
                <p id="tipAnnualDep" class="text-sm font-black text-slate-800 italic">₱ 0.00</p>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div class="dep-stat-box">
                    <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest mb-1">Year 1 Book Value</p>
                    <p id="tipYear1" class="text-[11px] font-bold text-red-600">₱ 0.00</p>
                </div>
                <div class="dep-stat-box">
                    <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest mb-1">Year 25 Book Value</p>
                    <p id="tipYear25" class="text-[11px] font-bold text-emerald-600">₱ 0.00</p>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100">
                <p class="text-[8px] font-bold text-slate-500 leading-relaxed">Calculated at 5% residual value over estimated useful life.</p>
            </div>
        </div>
    </div>


</body>
</html>
```

## File: `resources/views/import-buildings.blade.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Import Assets | DepEd Zamboanga City</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .drop-zone { border: 3px dashed #e2e8f0; transition: all 0.3s ease; }
        .drop-zone.dragover { border-color: #c00000; background-color: #fef2f2; }
        .preview-table th, .preview-table td { white-space: nowrap; min-width: 130px; }
        .preview-table th:first-child, .preview-table td:first-child { position: sticky; left: 0; z-index: 2; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }

        /* Dark mode overrides for Import UI - Midnight Blue Theme */
        /* Dark mode overrides for Import UI - Midnight Blue Theme */
        html.dark body .bg-amber-50 { background-color: rgba(30,41,59,0.6) !important; }
        html.dark body .bg-amber-100 { background-color: rgba(15,23,42,0.8) !important; }
        html.dark body .bg-amber-200 { background-color: rgba(30,41,59,0.9) !important; }
        html.dark body .border-amber-200 { border-color: rgba(51,65,85,0.6) !important; }
        html.dark body .text-amber-800 { color: #fde68a !important; }
        html.dark body .text-amber-700 { color: #fcd34d !important; }
        html.dark body tr.bg-amber-50:hover td { background-color: rgba(51,65,85,0.4) !important; }

        html.dark body .bg-red-50 { background-color: rgba(30,41,59,0.6) !important; }
        html.dark body .bg-red-100 { background-color: rgba(15,23,42,0.8) !important; }
        html.dark body .bg-red-200 { background-color: rgba(30,41,59,0.9) !important; }
        html.dark body .text-red-800 { color: #fca5a5 !important; }
        html.dark body tr.bg-red-50:hover td { background-color: rgba(51,65,85,0.4) !important; }

        html.dark body .bg-emerald-100 { background-color: rgba(15,23,42,0.8) !important; }
        html.dark body .text-emerald-800 { color: #6ee7b7 !important; }
        
        html.dark body .bg-white\/50 { background-color: rgba(30,41,59,0.3) !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-800 overflow-x-hidden">

@include('partials.sidebar')

<div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">
    <header class="lg:hidden bg-white border-b p-4 flex items-center gap-4 sticky top-0 z-30">
        <button onclick="toggleSidebar()" class="p-2 rounded-xl border bg-slate-50">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-slate-600"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
        </button>
        <span class="font-black italic text-sm">DepEd ZC</span>
    </header>

    <main class="p-6 lg:p-10 w-full">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4 max-w-[1600px] mx-auto">
            <div>
                <h1 class="text-3xl lg:text-4xl font-black text-slate-900 tracking-tight italic uppercase leading-none">Import Assets</h1>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-2">Property Inventory Form • Multi-Template Import</p>
            </div>
            @if(isset($allGroups) && (($totalBuildings ?? 0) > 0 || ($totalAssets ?? 0) > 0))
            <button onclick="window.location.href='{{ route('buildings.import') }}'" class="group px-6 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm hover:border-[#c00000] hover:text-[#c00000] transition-all active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 transition-transform group-hover:-translate-x-1"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Back to Import
            </button>
            @endif
        </div>

        @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({ title: 'Import Successful!', text: @json(session('success')), icon: 'success', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            });
        </script>
        @endif

        @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({ title: 'Import Error', html: `{!! implode('<br>', $errors->all()) !!}`, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            });
        </script>
        @endif

        @if(isset($allGroups) && (($totalBuildings ?? 0) > 0 || ($totalAssets ?? 0) > 0))
        <div class="animate-fade max-w-[1600px] mx-auto">
            
            @if(isset($dbDuplicates) && $dbDuplicates > 0 || isset($fileDuplicates) && $fileDuplicates > 0)
            <div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="mt-1 w-10 h-10 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-amber-800 font-black text-lg tracking-tight">Duplicate Records Detected</h3>
                        <div class="text-amber-700 text-sm mt-1 flex flex-wrap gap-x-6 gap-y-2">
                            @if(isset($dbDuplicates) && $dbDuplicates > 0)
                                <span class="font-bold bg-amber-100 px-2 py-0.5 rounded-md text-amber-800">{{ $dbDuplicates }} Already Registered</span>
                            @endif
                            @if(isset($fileDuplicates) && $fileDuplicates > 0)
                                <span class="font-bold bg-red-100 px-2 py-0.5 rounded-md text-red-800">{{ $fileDuplicates }} Repeated In File</span>
                            @endif
                            <span class="font-bold bg-emerald-100 px-2 py-0.5 rounded-md text-emerald-800">{{ (($totalBuildings ?? 0) + ($totalAssets ?? 0)) - ($dbDuplicates ?? 0) - ($fileDuplicates ?? 0) }} New Records</span>
                        </div>
                    </div>
                </div>
                <div class="text-xs text-amber-600 font-bold bg-amber-100 px-4 py-2 rounded-xl text-center md:text-right w-full md:w-auto">
                    Please review highlighted rows below before confirming.
                </div>
            </div>
            @endif

            <div class="flex flex-wrap items-center gap-4 mb-6">
                @if(($totalBuildings ?? 0) > 0)
                <div class="bg-white border border-slate-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/></svg>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Buildings</p>
                        <p class="text-2xl font-black text-slate-900 tracking-tighter">{{ number_format($totalBuildings) }}</p>
                    </div>
                </div>
                @endif
                @if(($totalAssets ?? 0) > 0)
                <div class="bg-white border border-slate-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Assets (RPCPPE / RPCSP)</p>
                        <p class="text-2xl font-black text-slate-900 tracking-tighter">{{ number_format($totalAssets) }}</p>
                    </div>
                </div>
                @endif
                <div class="bg-white border border-slate-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Rows</p>
                        <p class="text-2xl font-black text-slate-900 tracking-tighter">{{ number_format(($totalBuildings ?? 0) + ($totalAssets ?? 0)) }}</p>
                    </div>
                </div>
                <div class="flex-grow"></div>
                <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest" id="pageIndicator">Page 1 of 1</div>
            </div>

            <div class="flex gap-2 mb-4" id="tabButtons"></div>

            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl overflow-hidden">
                <div class="overflow-x-auto custom-scroll">
                    <table class="preview-table w-full text-left border-separate border-spacing-0">
                        <thead id="previewTableHead"></thead>
                        <tbody id="previewTableBody" class="text-xs font-bold text-slate-700"></tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between px-8 py-5 border-t border-slate-100 bg-slate-50/50">
                    <button onclick="goToPage(currentPage - 1)" id="prevBtn" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:border-[#c00000] hover:text-[#c00000] transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm">← Previous</button>
                    <div class="flex items-center gap-1" id="pageNumbers"></div>
                    <button onclick="goToPage(currentPage + 1)" id="nextBtn" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:border-[#c00000] hover:text-[#c00000] transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm">Next →</button>
                </div>
            </div>

            <div class="flex justify-between items-center mt-8">
                <a href="{{ route('buildings.import') }}" class="group px-8 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-black text-slate-500 uppercase tracking-widest flex items-center gap-3 shadow-sm hover:border-red-200 hover:text-[#c00000] transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Cancel
                </a>
                <form action="{{ route('buildings.import.confirm') }}" method="POST" id="confirmForm">
                    @csrf
                    <input type="hidden" name="duplicate_action" id="duplicateAction" value="keep">
                    <button type="button" onclick="confirmImport()" class="group px-12 py-4 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-widest shadow-xl hover:bg-[#c00000] transition-all active:scale-95 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Confirm Registration — {{ number_format(($totalBuildings ?? 0) + ($totalAssets ?? 0)) }} Records
                    </button>
                </form>
            </div>
        </div>

        <script>
            const allGroups = @json($allGroups);
            const duplicates = @json($duplicates ?? []);
            const ROWS_PER_PAGE = 20;
            let currentTab = null, currentPage = 1, currentRows = [], totalPages = 1;
            const tabs = [];
            if (allGroups.buildings && allGroups.buildings.length > 0) tabs.push({ key: 'buildings', label: 'Buildings', count: allGroups.buildings.length });
            if (allGroups.assets && allGroups.assets.length > 0) tabs.push({ key: 'assets', label: 'Assets (RPCPPE / RPCSP)', count: allGroups.assets.length });

            const tabContainer = document.getElementById('tabButtons');
            if (tabs.length > 1) {
                tabs.forEach(t => {
                    tabContainer.innerHTML += `<button onclick="switchTab('${t.key}')" id="tab_${t.key}" class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all border shadow-sm">${t.label} (${t.count.toLocaleString()})</button>`;
                });
            }

            const buildingHeaders = ['#','Region','Division','Office/School Type','School ID','Office/School Name','Address','Storeys','Classrooms','Article/Item','Description','Classification','Occupancy','Location','Date Constructed','Acquisition Date','Property No.','Acquisition Cost','Est. Useful Life','Appraised Value','Appraisal Date','Remarks'];
            const assetHeaders    = ['#','Region','Division','Office/School Type','School ID','Office/School Name','Article/Item','Description','Classification','Occupancy','Location','Acquisition Date','Property No.','Acquisition Cost'];

            function switchTab(key) {
                currentTab = key; currentPage = 1;
                currentRows = allGroups[key] || [];
                totalPages = Math.ceil(currentRows.length / ROWS_PER_PAGE) || 1;
                tabs.forEach(t => {
                    const btn = document.getElementById('tab_' + t.key);
                    if (!btn) return;
                    btn.className = t.key === key
                        ? 'px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all border shadow-sm bg-[#c00000] text-white border-[#c00000]'
                        : 'px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all border shadow-sm bg-white text-slate-500 border-slate-200 hover:border-[#c00000] hover:text-[#c00000]';
                });
                const headers = key === 'buildings' ? buildingHeaders : assetHeaders;
                const thead = document.getElementById('previewTableHead');
                let hHtml = '<tr class="text-[9px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 border-b-2 border-slate-100">';
                headers.forEach((h, i) => {
                    const cls = i === 0 ? 'px-4 py-5 bg-slate-50' : (h.includes('Cost') || h.includes('Value') ? 'px-4 py-5 text-right' : 'px-4 py-5');
                    hHtml += `<th class="${cls}">${h}</th>`;
                });
                thead.innerHTML = hHtml + '</tr>';
                renderPage(1);
            }

            function renderPage(page) {
                currentPage = page;
                const start = (page - 1) * ROWS_PER_PAGE;
                const pageRows = currentRows.slice(start, start + ROWS_PER_PAGE);
                const tbody = document.getElementById('previewTableBody');
                let html = '';
                pageRows.forEach((row, idx) => {
                    const n = start + idx + 1;
                    const isDup = duplicates.find(d => d.property_number === row.property_number);
                    const rowClass = isDup 
                        ? (isDup.reason === 'Exists in database' ? 'bg-amber-50 hover:bg-amber-100' : 'bg-red-50 hover:bg-red-100')
                        : 'hover:bg-slate-50/80';
                    const pnBadge = isDup
                        ? (isDup.reason === 'Exists in database' 
                            ? `<span class="ml-2 text-[9px] font-black uppercase tracking-widest bg-amber-200 text-amber-800 px-2 py-0.5 rounded-md">⚠ Registered</span>`
                            : `<span class="ml-2 text-[9px] font-black uppercase tracking-widest bg-red-200 text-red-800 px-2 py-0.5 rounded-md">Duplicate</span>`)
                        : '';

                    if (currentTab === 'buildings') {
                        html += `<tr class="${rowClass} transition-colors border-b border-slate-50">
                            <td class="px-4 py-4 text-slate-400 font-black italic bg-white/50">${n}</td>
                            <td class="px-4 py-4">${esc(row.region)}</td><td class="px-4 py-4">${esc(row.division)}</td>
                            <td class="px-4 py-4">${esc(row.office_type)}</td><td class="px-4 py-4">${esc(row.school_identifier)}</td>
                            <td class="px-4 py-4 font-black text-slate-900">${esc(row.office_name)}</td><td class="px-4 py-4">${esc(row.address)}</td>
                            <td class="px-4 py-4 text-center">${row.storeys ?? '—'}</td><td class="px-4 py-4 text-center">${row.classrooms ?? '—'}</td>
                            <td class="px-4 py-4">${esc(row.article)}</td><td class="px-4 py-4">${esc(row.description)}</td>
                            <td class="px-4 py-4">${esc(row.classification)}</td><td class="px-4 py-4">${esc(row.occupancy_nature)}</td>
                            <td class="px-4 py-4">${esc(row.location)}</td><td class="px-4 py-4">${esc(row.date_constructed)}</td>
                            <td class="px-4 py-4">${esc(row.acquisition_date)}</td>
                            <td class="px-4 py-4 text-[#c00000] font-black flex items-center whitespace-nowrap">${esc(row.property_number)}${pnBadge}</td>
                            <td class="px-4 py-4 text-right font-black">${fmtCost(row.acquisition_cost)}</td>
                            <td class="px-4 py-4 text-center font-bold">${row.estimated_useful_life ?? 25}</td>
                            <td class="px-4 py-4 text-right">${fmtCost(row.appraised_value)}</td>
                            <td class="px-4 py-4">${esc(row.appraisal_date)}</td>
                            <td class="px-4 py-4 text-slate-500 italic">${esc(row.remarks)}</td>
                        </tr>`;
                    } else {
                        html += `<tr class="${rowClass} transition-colors border-b border-slate-50">
                            <td class="px-4 py-4 text-slate-400 font-black italic bg-white/50">${n}</td>
                            <td class="px-4 py-4">${esc(row.region)}</td><td class="px-4 py-4">${esc(row.division)}</td>
                            <td class="px-4 py-4">${esc(row.office_type)}</td><td class="px-4 py-4">${esc(row.school_identifier)}</td>
                            <td class="px-4 py-4 font-black text-slate-900">${esc(row.office_name)}</td>
                            <td class="px-4 py-4 font-black">${esc(row.article)}</td><td class="px-4 py-4">${esc(row.description)}</td>
                            <td class="px-4 py-4">${esc(row.classification)}</td><td class="px-4 py-4">${esc(row.occupancy_nature)}</td>
                            <td class="px-4 py-4">${esc(row.location)}</td><td class="px-4 py-4">${esc(row.acquisition_date)}</td>
                            <td class="px-4 py-4 text-[#c00000] font-black flex items-center whitespace-nowrap">${esc(row.property_number)}${pnBadge}</td>
                            <td class="px-4 py-4 text-right font-black">${fmtCost(row.acquisition_cost)}</td>
                        </tr>`;
                    }
                });
                tbody.innerHTML = html;
                document.getElementById('prevBtn').disabled = page <= 1;
                document.getElementById('nextBtn').disabled = page >= totalPages;
                document.getElementById('pageIndicator').textContent = `Page ${page} of ${totalPages}`;
                const pnC = document.getElementById('pageNumbers');
                let pn = '', sp = Math.max(1, page - 2), ep = Math.min(totalPages, sp + 4);
                if (sp > 1) pn += '<span class="text-slate-400 text-xs px-1">...</span>';
                for (let i = sp; i <= ep; i++) pn += `<button onclick="goToPage(${i})" class="w-8 h-8 rounded-lg text-[10px] font-black transition-all ${i === page ? 'bg-[#c00000] text-white shadow-md' : 'text-slate-400 hover:bg-slate-100'}">${i}</button>`;
                if (ep < totalPages) pn += '<span class="text-slate-400 text-xs px-1">...</span>';
                pnC.innerHTML = pn;
            }

            function goToPage(p) { if (p >= 1 && p <= totalPages) renderPage(p); }
            function esc(v) { if (v === null || v === undefined || v === '') return '<span class="text-slate-300">—</span>'; const d = document.createElement('div'); d.textContent = String(v); return d.innerHTML; }
            function fmtCost(v) { return v != null ? '₱' + Number(v).toLocaleString('en-PH', {minimumFractionDigits: 2}) : '<span class="text-slate-300">—</span>'; }

            function confirmImport() {
                const totalRecords = ((allGroups.buildings ? allGroups.buildings.length : 0) + (allGroups.assets ? allGroups.assets.length : 0));
                const dbDups = duplicates.filter(d => d.reason === 'Exists in database');
                const fileDups = duplicates.filter(d => d.reason === 'Repeated in file');
                const newRecordsCount = totalRecords - dbDups.length - fileDups.length;

                if (duplicates && duplicates.length > 0) {
                    let confirmButtons = '';
                    let title = 'Duplicate Records Detected';
                    let messageHtml = '';

                    if (dbDups.length > 0 && fileDups.length === 0) {
                        messageHtml = `<p class="text-sm text-slate-600 mb-4"><strong>${dbDups.length}</strong> record(s) are already registered in the system. <strong>${newRecordsCount}</strong> record(s) are new.</p>`;
                        confirmButtons = `<div class="flex flex-col gap-3">
                            <button onclick="submitWithAction('skip_existing')" class="px-6 py-4 bg-[#c00000] text-white rounded-xl font-bold hover:bg-red-700 w-full">Add New Only (${newRecordsCount})</button>
                            <button onclick="submitWithAction('overwrite')" class="px-6 py-4 bg-amber-500 text-white rounded-xl font-bold hover:bg-amber-600 w-full">Overwrite All Existing</button>
                            <button onclick="Swal.close()" class="px-6 py-4 bg-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-300 w-full">Cancel</button>
                        </div>`;
                    } else if (fileDups.length > 0 && dbDups.length === 0) {
                        messageHtml = `<p class="text-sm text-slate-600 mb-4"><strong>${fileDups.length}</strong> record(s) have identical Property Numbers within the file itself.</p>`;
                        confirmButtons = `<div class="flex flex-col gap-3">
                            <button onclick="submitWithAction('skip_existing')" class="px-6 py-4 bg-amber-500 text-white rounded-xl font-bold hover:bg-amber-600 w-full">Keep First, Skip Duplicates</button>
                            <button onclick="Swal.close()" class="px-6 py-4 bg-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-300 w-full">Cancel</button>
                        </div>`;
                    } else {
                        // Mixed
                        messageHtml = `<p class="text-sm text-slate-600 mb-4"><strong>${dbDups.length}</strong> already registered, <strong>${fileDups.length}</strong> repeated in file, <strong>${newRecordsCount}</strong> new.</p>`;
                        confirmButtons = `<div class="flex flex-col gap-3">
                            <button onclick="submitWithAction('skip_existing')" class="px-6 py-4 bg-[#c00000] text-white rounded-xl font-bold hover:bg-red-700 w-full">Add New Only (${newRecordsCount})</button>
                            <button onclick="submitWithAction('overwrite')" class="px-6 py-4 bg-amber-500 text-white rounded-xl font-bold hover:bg-amber-600 w-full">Overwrite Existing, Skip File Repeats</button>
                            <button onclick="Swal.close()" class="px-6 py-4 bg-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-300 w-full">Cancel</button>
                        </div>`;
                    }

                    Swal.fire({
                        title: title,
                        html: messageHtml + confirmButtons,
                        icon: 'warning',
                        showConfirmButton: false,
                        showCancelButton: false,
                        customClass: { popup: 'rounded-[2rem]' }
                    });
                } else {
                    Swal.fire({
                        title: 'Confirm Import?',
                        html: `<p class="text-sm text-slate-600">You are about to register <strong>${totalRecords}</strong> records into the system database.</p><p class="text-xs text-slate-400 mt-2">This action cannot be undone.</p>`,
                        icon: 'question', showCancelButton: true, confirmButtonColor: '#c00000', cancelButtonColor: '#64748b',
                        confirmButtonText: 'Yes, Register All', cancelButtonText: 'Cancel',
                        customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
                    }).then(r => { if (r.isConfirmed) submitWithAction('keep'); });
                }
            }

            function submitWithAction(action) {
                document.getElementById('duplicateAction').value = action;
                document.getElementById('confirmForm').submit();
                Swal.fire({ title: 'Processing...', html: 'Please wait while records are imported.', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
            }

            document.addEventListener('DOMContentLoaded', () => switchTab(tabs[0].key));
        </script>

        @else
        <div class="max-w-3xl mx-auto animate-fade">
            <form action="{{ route('buildings.import.preview') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <div class="bg-white rounded-[3rem] border border-slate-100 shadow-xl p-12 lg:p-16">
                    <div id="dropZone" class="drop-zone rounded-[2.5rem] p-16 text-center cursor-pointer transition-all" onclick="document.getElementById('fileInput').click()">
                        <div class="w-20 h-20 bg-red-50 text-[#c00000] rounded-3xl flex items-center justify-center mx-auto mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        </div>
                        <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight italic mb-2">Upload Property Inventory Form</h2>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] max-w-sm mx-auto leading-relaxed mb-6">Drop your .xlsx file here or click to browse. The system will auto-detect Building PIF, RPCPPE, and RPCSP templates from all sheets.</p>
                        <p id="fileName" class="hidden text-sm font-black text-[#c00000] italic mb-4"></p>
                        <input type="file" name="xlsx_file" id="fileInput" accept=".xlsx,.xls" class="hidden" onchange="handleFileSelect(this)">
                    </div>
                    <div class="mt-8 p-6 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest mb-1">Supported Templates</p>
                        <p class="text-[11px] text-slate-500 font-bold leading-relaxed">
                            The system auto-detects 3 template types:
                            <span class="text-amber-600 italic">Building PIF</span>,
                            <span class="text-blue-600 italic">RPCPPE</span>, and
                            <span class="text-blue-600 italic">RPCSP</span>.
                            Data rows should start from row 11. Multi-sheet files are fully supported.
                        </p>
                    </div>
                    <button type="submit" id="submitBtn" disabled class="w-full mt-8 py-5 bg-slate-200 text-slate-400 rounded-2xl font-black uppercase tracking-widest text-sm transition-all cursor-not-allowed flex items-center justify-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        Upload &amp; Preview
                    </button>
                </div>
            </form>
        </div>
        <script>
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');
            const submitBtn = document.getElementById('submitBtn');
            const fileNameEl = document.getElementById('fileName');
            ['dragenter','dragover'].forEach(evt => dropZone.addEventListener(evt, e => { e.preventDefault(); dropZone.classList.add('dragover'); }));
            ['dragleave','drop'].forEach(evt => dropZone.addEventListener(evt, e => { e.preventDefault(); dropZone.classList.remove('dragover'); }));
            dropZone.addEventListener('drop', e => { const f = e.dataTransfer.files; if (f.length > 0) { const dt = new DataTransfer(); dt.items.add(f[0]); fileInput.files = dt.files; handleFileSelect(fileInput); } });
            function handleFileSelect(input) {
                if (input.files.length > 0) {
                    fileNameEl.textContent = '📄 ' + input.files[0].name;
                    fileNameEl.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('bg-slate-200','text-slate-400','cursor-not-allowed');
                    submitBtn.classList.add('bg-slate-900','text-white','hover:bg-[#c00000]','shadow-xl','cursor-pointer');
                }
            }
        </script>

        @endif
    </main>
</div>
</body>
</html>
```

## File: `resources/views/assets/view-all.blade.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Inventory Masterlist | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        [x-cloak] { display: none !important; }
        .table-row-transition { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .back-btn-hover:hover { transform: translateX(-5px); color: #c00000; border-color: #c00000; }
        .school-card { 
            transition: all 0.2s ease; 
            border: 1px solid #f1f5f9; 
            padding: 0.5rem;
            border-radius: 0.75rem;
            background: #fff;
        }
        .school-card:hover { border-color: #c00000; background: #fffcfc; transform: translateY(-1px); }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden" x-data="assetInventory()">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scrollbar">
        <main class="p-4 lg:p-8">
            {{-- Header --}}
            <header class="flex flex-col md:flex-row md:justify-between md:items-start mb-6 gap-4">
                <div class="flex flex-col gap-3">
                    <a href="{{ route('assets.view') }}" class="back-btn-hover no-print inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-[10px] font-black text-slate-500 transition-all w-fit shadow-sm uppercase tracking-wider">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                        Back to View Selection
                    </a>
                    <div>
                        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tighter italic uppercase leading-none">Inventory Masterlist</h2>
                        <p class="text-slate-400 text-[11px] mt-1 font-bold italic uppercase tracking-widest">Warehouse & school distribution summary</p>
                    </div>
                </div>
                <button onclick="window.print()" class="no-print group bg-white text-slate-600 border border-slate-200 px-5 py-3 rounded-xl font-bold hover:bg-slate-50 transition-all flex items-center gap-3 shadow-sm active:scale-95 text-[11px] uppercase tracking-widest">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-slate-400 group-hover:text-[#c00000] transition-colors"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231a1.125 1.125 0 01-1.117-1.227L6.34 18m11.318-4.171a42.41 42.41 0 014.232.748 1.125 1.125 0 01.815 1.39l-1.077 4.195a1.125 1.125 0 01-1.392.815l-1.332-.342M17.66 18l-1.332-.342m-11.318-4.171a42.41 42.41 0 00-4.232.748 1.125 1.125 0 00-.815 1.39l1.077 4.195a1.125 1.125 0 001.392.815l1.332-.342M6.34 18l1.332-.342m0 0V5.25A2.25 2.25 0 019 3h6a2.25 2.25 0 012.25 2.25v12.75m-11.25 0h11.25" /></svg>
                    Print Summary
                </button>
            </header>

            {{-- Compact Stats --}}
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 border-l-4 border-l-slate-800">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Master Stock</p>
                    <h3 class="text-2xl font-black text-slate-800" x-text="totals().master"></h3>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 border-l-4 border-l-blue-600">
                    <p class="text-[9px] font-black text-blue-400 uppercase tracking-widest mb-1">Deployed Units</p>
                    <h3 class="text-2xl font-black text-slate-800" x-text="totals().distributed"></h3>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 border-l-4 border-l-emerald-600">
                    <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest mb-1">In Warehouse</p>
                    <h3 class="text-2xl font-black text-slate-800" x-text="totals().available"></h3>
                </div>
            </div>

            {{-- Filter Bar --}}
            <section class="no-print bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 flex flex-wrap gap-4">
                <div class="flex-grow grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Category Filter --}}
                    <select x-model="filters.category" class="w-full p-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold uppercase outline-none focus:ring-2 focus:ring-red-100 transition-all">
                        <option value="all">Categories: ALL</option>
                        <template x-for="cat in categories" :key="cat">
                            <option :value="cat" x-text="cat"></option>
                        </template>
                    </select>

                    {{-- Quadrant Filter --}}
                    <select x-model="filters.quadrant" class="w-full p-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold uppercase outline-none focus:ring-2 focus:ring-red-100 transition-all">
                        <option value="all">Quadrants: ALL</option>
                        <template x-for="q in quadrants" :key="q">
                            <option :value="q" x-text="q"></option>
                        </template>
                    </select>

                    {{-- Sort Filter --}}
                    <select x-model="filters.sort" class="w-full p-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold uppercase outline-none text-[#c00000]">
                        <option value="none">Sort: Default</option>
                        <option value="high">High Qty first</option>
                        <option value="low">Low Qty first</option>
                        <option value="name_asc">Name A→Z</option>
                        <option value="name_desc">Name Z→A</option>
                    </select>

                    {{-- Search --}}
                    <div class="relative">
                        <input type="text" x-model="filters.search" placeholder="Search item/school..." class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:ring-2 focus:ring-red-100 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3 absolute left-3 top-1/2 -translate-y-1/2 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                </div>
            </section>

            {{-- Results count --}}
            <div class="flex items-center justify-between mb-3 px-1">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Showing <span class="text-slate-700" x-text="filteredInventory().length"></span> of <span class="text-slate-700" x-text="inventory.length"></span> assets
                </p>
                <template x-if="filters.category !== 'all' || filters.quadrant !== 'all' || filters.search">
                    <button @click="filters.category='all'; filters.quadrant='all'; filters.sort='none'; filters.search=''" class="text-[10px] font-black text-red-500 uppercase tracking-wider hover:underline">✕ Clear Filters</button>
                </template>
            </div>

            {{-- Inventory Table --}}
            <section class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50/80">
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Asset</th>
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Specs Stock</th>
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Recipient Schools</th>
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Master</th>
                                <th class="px-5 py-4 text-[9px] font-black text-blue-500 uppercase tracking-widest border-b border-slate-100 text-center bg-blue-50/20">Sent</th>
                                <th class="px-5 py-4 text-[9px] font-black text-emerald-500 uppercase tracking-widest border-b border-slate-100 text-center bg-emerald-50/20">Available Stock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="asset in filteredInventory()" :key="asset.id">
                                <tr class="group hover:bg-slate-50/50 transition-all table-row-transition">
                                    {{-- Asset Identity --}}
                                    <td class="px-5 py-4 min-w-[150px]">
                                        <div class="flex flex-col">
                                            <span class="font-black text-slate-800 uppercase text-[12px] leading-tight" x-text="asset.name"></span>
                                            <span class="text-[8px] font-black text-blue-500 uppercase mt-1 tracking-tighter" x-text="asset.category"></span>
                                        </div>
                                    </td>
                                    {{-- Specs Pills --}}
                                    <td class="px-5 py-4 min-w-[150px]">
                                        <div class="flex flex-col gap-1">
                                            <template x-for="spec in asset.specs" :key="spec.name">
                                                <div class="flex items-center justify-between bg-white border border-slate-100 px-2 py-1 rounded-md">
                                                    <span class="text-[8px] font-bold text-slate-500 uppercase" x-text="spec.name"></span>
                                                    <span class="text-[9px] font-black text-slate-800" x-text="spec.qty"></span>
                                                </div>
                                            </template>
                                            <template x-if="asset.specs.length === 0">
                                                <span class="text-[8px] font-bold text-slate-300 italic">No specs</span>
                                            </template>
                                        </div>
                                    </td>
                                    {{-- Compact Recipient Count --}}
                                    <td class="px-5 py-4 min-w-[200px] text-center">
                                        <template x-if="getFilteredDistribution(asset).length > 0">
                                            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-50 text-[#c00000] border border-red-100 rounded-full font-black text-[12px]">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M10 2a.75.75 0 01.59.299l7.5 9.75a.75.75 0 01-1.18.902L10 3.864 3.09 12.951a.75.75 0 01-1.18-.902l7.5-9.75A.75.75 0 0110 2zM3 15.75a.75.75 0 01.75-.75h12.5a.75.75 0 010 1.5H3.75a.75.75 0 01-.75-.75z" clip-rule="evenodd" /></svg>
                                                <span x-text="`${getFilteredDistribution(asset).length} Schools`"></span>
                                            </div>
                                        </template>
                                        <template x-if="getFilteredDistribution(asset).length === 0">
                                            <span class="text-[10px] font-bold text-slate-300 italic">No deployments</span>
                                        </template>
                                    </td>
                                    {{-- Numbers --}}
                                    <td class="px-5 py-4 text-center font-black text-[13px] text-slate-900" x-text="asset.master_quantity"></td>
                                    <td class="px-5 py-4 text-center bg-blue-50/10 font-black text-[13px] text-blue-600" x-text="calculateDistributed(asset)"></td>
                                    <td class="px-5 py-4 text-center bg-emerald-50/10 font-black text-[13px] text-emerald-600" x-text="calculateAvailableStock(asset)"></td>
                                </tr>
                            </template>

                            {{-- Empty State --}}
                            <template x-if="filteredInventory().length === 0">
                                <tr>
                                    <td colspan="6" class="px-8 py-16 text-center">
                                        <p class="text-slate-400 font-bold text-sm">No assets match your filters</p>
                                        <p class="text-slate-300 text-xs mt-1">Try adjusting the category, quadrant, or search query</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        function assetInventory() {
            return {
                filters: { category: 'all', quadrant: 'all', sort: 'none', search: '' },

                // Dynamic data from the backend
                inventory: {!! $inventoryJson !!},
                categories: {!! $categoriesJson !!},
                quadrants: {!! $quadrantsJson !!},

                calculateDistributed(a) { return a.distribution.reduce((s, x) => s + x.qty, 0); },
                calculateAvailableStock(a) { return a.specs.reduce((s, sp) => s + sp.qty, 0); },

                totals() {
                    const filtered = this.filteredInventory();
                    let m = 0, d = 0, avail = 0;
                    filtered.forEach(a => { 
                        m += a.master_quantity; 
                        d += this.calculateDistributed(a);
                        avail += a.specs.reduce((s, sp) => s + sp.qty, 0);
                    });
                    return { master: m, distributed: d, available: avail };
                },

                getFilteredDistribution(asset) {
                    if (this.filters.quadrant === 'all') return asset.distribution;
                    return asset.distribution.filter(d => d.quadrant === this.filters.quadrant);
                },

                filteredInventory() {
                    const s = this.filters.search.toLowerCase().trim();

                    let filtered = this.inventory.filter(asset => {
                        // Category filter
                        if (this.filters.category !== 'all' && asset.category !== this.filters.category) return false;

                        // Quadrant filter: asset must have at least one distribution in the selected quadrant
                        // OR we show all assets even without distribution if quadrant is 'all'
                        if (this.filters.quadrant !== 'all') {
                            const hasQuadrant = asset.distribution.some(d => d.quadrant === this.filters.quadrant);
                            if (!hasQuadrant) return false;
                        }

                        // Search filter: match item name, category, spec names, or school names
                        if (s) {
                            const nameMatch = asset.name.toLowerCase().includes(s);
                            const catMatch = asset.category.toLowerCase().includes(s);
                            const specMatch = asset.specs.some(sp => sp.name.toLowerCase().includes(s));
                            const schoolMatch = asset.distribution.some(d => d.school.toLowerCase().includes(s));
                            if (!nameMatch && !catMatch && !specMatch && !schoolMatch) return false;
                        }

                        return true;
                    });

                    // Sorting
                    if (this.filters.sort === 'high') filtered.sort((a, b) => b.master_quantity - a.master_quantity);
                    else if (this.filters.sort === 'low') filtered.sort((a, b) => a.master_quantity - b.master_quantity);
                    else if (this.filters.sort === 'name_asc') filtered.sort((a, b) => a.name.localeCompare(b.name));
                    else if (this.filters.sort === 'name_desc') filtered.sort((a, b) => b.name.localeCompare(a.name));

                    return filtered;
                }
            }
        }
    </script>
</body>
</html>
```

## File: `resources/views/assets/profile.blade.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Profile | DepEd ZC Inventory</title>

    {{-- Error/Success Alerts --}}
    @if(session('error') || $errors->any())
        <div class="fixed top-6 left-1/2 -translate-x-1/2 z-[300] w-full max-w-md animate-in slide-in-from-top duration-300">
            <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-4 shadow-xl flex items-start gap-4">
                <div class="w-10 h-10 bg-red-100 text-red-600 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div class="flex-grow pt-0.5">
                    <h4 class="text-sm font-black text-red-800 uppercase tracking-tight">Updating Failed</h4>
                    <p class="text-xs font-bold text-red-600 mt-0.5 leading-relaxed">
                        @if(session('error')) {{ session('error') }} @endif
                        @foreach ($errors->all() as $error)
                            • {{ $error }}<br>
                        @endforeach
                    </p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-red-400 hover:text-red-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="fixed top-6 left-1/2 -translate-x-1/2 z-[300] w-full max-w-md animate-in slide-in-from-top duration-300">
            <div class="bg-emerald-50 border-2 border-emerald-200 rounded-2xl p-4 shadow-xl flex items-start gap-4">
                <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div class="flex-grow pt-0.5">
                    <h4 class="text-sm font-black text-emerald-800 uppercase tracking-tight">Success</h4>
                    <p class="text-xs font-bold text-emerald-600 mt-0.5 leading-relaxed">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        deped: '#c00000',
                        deped_light: '#fef2f2',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        [x-cloak] { display: none !important; }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        
        .timeline-line {
            position: absolute;
            left: 11px;
            top: 24px;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
            z-index: 0;
        }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll p-4 lg:p-8" x-data="{ activeTab: 'specs', isEditing: false, showConfirmModal: false, showTransferModal: false, showReturnAmuModal: false, showImageFullscreen: false, showRemoveConfirmModal: false, isSaving: false }">
        
        {{-- Global Header (Fixed/Sticky) --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 sticky top-0 z-50">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 bg-deped_light rounded-xl flex items-center justify-center border border-deped/20 shadow-sm shrink-0">
                    <svg class="w-6 h-6 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase italic">{{ $asset->description }}</h1>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-200">{{ $asset->property_number }}</span>
                        {{-- Status Badge (Success placeholder) --}}
                        <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest bg-emerald-100 px-2 py-0.5 rounded-full flex items-center gap-1.5 shadow-sm">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Serviceable
                        </span>
                    </div>
                </div>
            </div>

            {{-- Actions Menu --}}
            <div class="flex items-center gap-3 shrink-0" x-data="{ open: false }">
                <button @click="isEditing = true" x-show="!isEditing" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                    <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Edit
                </button>
                <button @click="isEditing = false" x-show="isEditing" x-cloak class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-500 uppercase tracking-widest hover:border-slate-300 hover:text-slate-700 transition-all duration-300 shadow-sm flex items-center gap-2">
                    Cancel
                </button>
                <button @click="showConfirmModal = true" x-show="isEditing" x-cloak class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm shadow-emerald-600/30 hover:shadow-md flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    Save Changes
                </button>
                <div class="relative">
                    <button @click="open = !open" @click.away="open = false" class="px-5 py-2.5 bg-deped text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-800 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-md shadow-red-200 hover:shadow-lg hover:shadow-red-300 flex items-center gap-2">
                        Quick Actions
                        <svg class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="{'rotate-180': open}"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-2 scale-95" class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden transform origin-top-right">
                        <button @click="showTransferModal = true; open = false" class="w-full text-left px-4 py-3 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-blue-600 hover:pl-5 transition-all flex items-center gap-2 border-b border-slate-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg> Initiate Transfer
                        </button>
                        <button @click="showReturnAmuModal = true; open = false" class="w-full text-left px-4 py-3 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-emerald-600 hover:pl-5 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg> Return to AMU
                        </button>
                    </div>
                </div>
                <div class="w-px h-8 bg-slate-200 mx-1"></div>
                <a href="/view-assets" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-grow pb-10">
            
            {{-- Left Sidebar: Asset Identity Card --}}
            <aside class="lg:col-span-3 flex flex-col gap-6 z-40 relative">
                <form action="{{ route('assets.photo.upload', $asset->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-visible flex flex-col relative" x-data="{ photoPreview: null, showPhotoConfirmModal: false, isHoveringImage: false }">
                    @csrf
                    <div class="aspect-square bg-slate-50 border-b border-slate-100 flex items-center justify-center p-6 relative group rounded-t-2xl overflow-hidden" @mouseenter="isHoveringImage = true" @mouseleave="isHoveringImage = false">
                        <input type="file" name="photo" id="photo-upload" class="hidden" accept="image/*" capture="environment" @change="
                            const file = $event.target.files[0]; 
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = (e) => photoPreview = e.target.result;
                                reader.readAsDataURL(file);
                            }
                        ">
                        <img :src="photoPreview || '{{ $asset->photo_path ? asset('storage/' . $asset->photo_path) : asset('images/asset.png') }}'" alt="Asset Photo" class="w-full h-full object-contain transition-transform duration-500 cursor-pointer" :class="(photoPreview || '{{ $asset->photo_path }}') ? 'opacity-100 scale-100 group-hover:scale-110' : 'opacity-50 group-hover:scale-105'" @click="if(!photoPreview && '{{ $asset->photo_path }}') showImageFullscreen = true">
                        
                        {{-- Hover Preview Popout (E-commerce Style) --}}
                        <div x-show="isHoveringImage && !photoPreview && '{{ $asset->photo_path }}'" 
                             x-transition:enter="transition ease-out duration-300 delay-150" 
                             x-transition:enter-start="opacity-0 scale-95 -translate-x-4" 
                             x-transition:enter-end="opacity-100 scale-100 translate-x-0" 
                             x-transition:leave="transition ease-in duration-150" 
                             x-transition:leave-start="opacity-100 scale-100" 
                             x-transition:leave-end="opacity-0 scale-95" 
                             class="absolute top-0 -right-[420px] w-[400px] h-[400px] bg-white rounded-2xl shadow-2xl border border-slate-200 z-[150] pointer-events-none flex items-center justify-center p-4 hidden lg:flex" x-cloak>
                            <img src="{{ $asset->photo_path ? asset('storage/' . $asset->photo_path) : '' }}" class="w-full h-full object-contain rounded-xl drop-shadow-md">
                        </div>
                        
                        {{-- Controls for View/Remove --}}
                        @if($asset->photo_path)
                        <div x-show="!photoPreview" class="absolute top-4 right-4 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10">
                            <button type="button" @click="showImageFullscreen = true" class="w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full text-slate-700 hover:text-blue-600 shadow-sm flex items-center justify-center hover:scale-110 active:scale-95 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                            </button>
                            <button type="button" @click="showRemoveConfirmModal = true" class="w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full text-slate-700 hover:text-red-600 shadow-sm flex items-center justify-center hover:scale-110 active:scale-95 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                        @endif

                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4 pointer-events-none">
                            
                            {{-- State: No new photo selected --}}
                            <label x-show="!photoPreview" for="photo-upload" class="w-full py-2.5 bg-white/90 backdrop-blur-md rounded-lg text-xs font-black uppercase tracking-widest text-slate-800 hover:bg-white shadow-lg text-center cursor-pointer transition-all hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2 pointer-events-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span>{{ $asset->photo_path ? 'Change Photo' : 'Upload / Take Photo' }}</span>
                            </label>

                            {{-- State: New photo selected --}}
                            <div x-show="photoPreview" x-cloak class="w-full flex gap-2 pointer-events-auto">
                                <button type="button" @click="photoPreview = null; document.getElementById('photo-upload').value = ''" class="flex-1 py-2.5 bg-white/90 backdrop-blur-md text-slate-700 hover:bg-white rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm transition-all active:scale-95">Cancel</button>
                                <button type="button" @click="showPhotoConfirmModal = true" class="flex-[2] py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-600/30 transition-all active:scale-95">Save Photo</button>
                            </div>

                        </div>
                    </div>

                    {{-- Photo Confirm Modal (Internal to the form so it submits correctly) --}}
                    <div x-show="showPhotoConfirmModal" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center">
                        <div x-show="showPhotoConfirmModal" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showPhotoConfirmModal = false"></div>
                        <div x-show="showPhotoConfirmModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" class="bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 relative z-10 border border-slate-100 flex flex-col items-center text-center">
                            <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-5 ring-8 ring-blue-50">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            </div>
                            <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight mb-2">Save New Photo?</h3>
                            <p class="text-xs font-bold text-slate-500 mb-8 leading-relaxed">Are you sure you want to permanently update the photo for this asset?</p>
                            <div class="flex items-center gap-3 w-full">
                                <button type="button" @click="showPhotoConfirmModal = false" class="flex-1 py-3.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-widest transition-colors">Cancel</button>
                                <button type="submit" class="flex-1 py-3.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-blue-600/30 transition-all active:scale-95">Yes, Save</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-5 space-y-5">
                        <div class="bg-slate-50 border border-slate-100 p-4 rounded-2xl shadow-sm relative overflow-hidden group">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-deped"></div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                <svg class="w-3 h-3 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Current Custodian
                            </p>
                            <div class="flex items-center gap-3 pl-1">
                                <div class="w-10 h-10 rounded-full bg-slate-200 border border-slate-300 flex items-center justify-center text-slate-600 font-black text-xs shrink-0 shadow-sm group-hover:scale-110 group-hover:bg-deped group-hover:border-deped group-hover:text-white transition-all">
                                    {{ strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $asset->office_school_name), 0, 2)) ?: 'NA' }}
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-700 uppercase leading-tight group-hover:text-deped transition-colors">{{ $asset->office_school_name }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">Designated Custodian</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Current Location</p>
                            <a href="#" class="group flex items-start gap-2">
                                <svg class="w-4 h-4 text-deped shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <div>
                                    <p class="text-xs font-bold text-deped uppercase leading-tight group-hover:underline">{{ $asset->office_school_name }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">{{ $asset->division ?? 'Division of Zamboanga City' }}</p>
                                </div>
                            </a>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <div class="flex justify-between items-end mb-1.5">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Est. Lifespan</p>
                                <p class="text-[10px] font-black text-slate-700">75%</p>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-deped to-blue-400 h-full rounded-full" style="width: 75%"></div>
                            </div>
                            <p class="text-[8px] font-bold text-slate-400 uppercase mt-1.5 text-right">3 of 4 Years Remaining</p>
                        </div>
                    </div>
                </form>
            </aside>

            {{-- Main Content Area --}}
            <div class="lg:col-span-9 flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                
                {{-- Tabs Header --}}
                <div class="flex border-b border-slate-200 bg-slate-50/50 px-2 pt-2">
                    <button @click="activeTab = 'specs'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'specs', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'specs'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Specifications
                    </button>
                    <button @click="activeTab = 'history'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'history', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'history'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Lifecycle & History
                    </button>
                    <button @click="activeTab = 'docs'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'docs', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'docs'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Documents & Media
                    </button>
                </div>

                {{-- Tab Contents --}}
                <div class="p-6 lg:p-8 flex-grow overflow-y-auto custom-scroll bg-white">
                    
                    {{-- TAB 1: Specifications --}}
                    <form id="update-asset-form" action="{{ route('assets.update', $asset->id) }}" method="POST" x-show="activeTab === 'specs'" class="animate-fade space-y-8">
                        @csrf
                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Custodian Details
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-8 bg-slate-50 rounded-xl p-6 border border-slate-100 mb-8">
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Region</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->region ?? 'Region IX - Zamboanga Peninsula' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Division</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->division ?? 'Division of Zamboanga City' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Office / School Name</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->office_school_name }}{{ $asset->nature_of_occupancy ? ' - ' . $asset->nature_of_occupancy : '' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Custodian</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">
                                        {{ trim($asset->custodian_first . ' ' . $asset->custodian_middle . ' ' . $asset->custodian_last) ?: 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Position</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->custodian_position ?: 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Contact No.</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->custodian_contact ?: 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Technical Details
                            </h3>
                             <div class="grid grid-cols-1 md:grid-cols-3 gap-y-6 gap-x-8 bg-slate-50 rounded-xl p-6 border border-slate-100">
                                {{-- Searchable Classification --}}
                                <div x-data="{ 
                                    open: false, 
                                    search: '{{ $asset->classification_name }}', 
                                    selectedId: '{{ $asset->classification_id }}',
                                    options: @js($classifications)
                                }" class="relative">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Classification</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->classification_name }}</p>
                                    <div x-show="isEditing" class="relative group" @click.away="open = false">
                                        <input type="text" x-model="search" @focus="open = true" @input="open = true" placeholder="Search..." class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                        <input type="hidden" name="classification_id" :value="options.find(o => o.name.toLowerCase() === search.trim().toLowerCase())?.id || search.trim()">
                                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-hover:text-deped transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                        
                                        <div x-show="open" x-cloak class="absolute z-50 w-full mt-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto custom-scroll p-1 animate-in fade-in zoom-in duration-200">
                                            <template x-for="opt in options.filter(o => o.name.toLowerCase().includes(search.toLowerCase()))" :key="opt.id">
                                                <div @click="selectedId = opt.id; search = opt.name; open = false; $dispatch('input')" class="px-4 py-2.5 text-[10px] font-black text-slate-300 uppercase hover:bg-slate-700 hover:text-white rounded-lg cursor-pointer transition-colors" x-text="opt.name"></div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Searchable Category --}}
                                <div x-data="{ 
                                    open: false, 
                                    search: '{{ $asset->category_name }}', 
                                    selectedId: '{{ $asset->category_id }}',
                                    options: @js($categories)
                                }" class="relative">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Category</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->category_name }}</p>
                                    <div x-show="isEditing" class="relative group" @click.away="open = false">
                                        <input type="text" x-model="search" @focus="open = true" @input="open = true" placeholder="Search..." class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                        <input type="hidden" name="category_id" :value="options.find(o => o.name.toLowerCase() === search.trim().toLowerCase())?.id || search.trim()">
                                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-hover:text-deped transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                        
                                        <div x-show="open" x-cloak class="absolute z-50 w-full mt-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto custom-scroll p-1 animate-in fade-in zoom-in duration-200">
                                            <template x-for="opt in options.filter(o => o.name.toLowerCase().includes(search.toLowerCase()))" :key="opt.id">
                                                <div @click="selectedId = opt.id; search = opt.name; open = false; $dispatch('input')" class="px-4 py-2.5 text-[10px] font-black text-slate-300 uppercase hover:bg-slate-700 hover:text-white rounded-lg cursor-pointer transition-colors" x-text="opt.name"></div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Searchable Item --}}
                                <div x-data="{ 
                                    open: false, 
                                    search: '{{ $asset->item_name }}', 
                                    selectedId: '{{ $asset->item_id }}',
                                    options: @js($items)
                                }" class="relative">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Article / Item</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->item_name }}</p>
                                    <div x-show="isEditing" class="relative group" @click.away="open = false">
                                        <input type="text" x-model="search" @focus="open = true" @input="open = true" placeholder="Search..." class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                        <input type="hidden" name="item_id" :value="options.find(o => o.name.toLowerCase() === search.trim().toLowerCase())?.id || search.trim()">
                                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-hover:text-deped transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                        
                                        <div x-show="open" x-cloak class="absolute z-50 w-full mt-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto custom-scroll p-1 animate-in fade-in zoom-in duration-200">
                                            <template x-for="opt in options.filter(o => o.name.toLowerCase().includes(search.toLowerCase()))" :key="opt.id">
                                                <div @click="selectedId = opt.id; search = opt.name; open = false; $dispatch('input')" class="px-4 py-2.5 text-[10px] font-black text-slate-300 uppercase hover:bg-slate-700 hover:text-white rounded-lg cursor-pointer transition-colors" x-text="opt.name"></div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Description</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->description }}</p>
                                    <input x-show="isEditing" type="text" name="description" value="{{ $asset->description }}" placeholder="Description" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Unit Cost</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-emerald-600 mt-1 uppercase px-1">₱ {{ number_format($asset->asset_cost, 2) }}</p>
                                    <div x-show="isEditing" class="relative flex items-center group">
                                        <span class="absolute left-4 text-slate-500 font-black text-[13px] pointer-events-none">₱</span>
                                        <input type="number" step="0.01" name="asset_cost" value="{{ $asset->asset_cost }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl pl-8 pr-4 py-3 text-sm font-black text-slate-100 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Quantity</p>
                                    <p x-show="!isEditing" class="text-xs font-black text-deped mt-1 uppercase px-1">{{ $asset->quantity }} Unit(s)</p>
                                    <div x-show="isEditing" class="relative flex items-center group">
                                        <input type="number" name="quantity" value="{{ $asset->quantity }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl pl-4 pr-16 py-3 text-sm font-black text-slate-100 focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                        <span class="absolute right-4 text-slate-500 font-black text-[10px] uppercase tracking-widest pointer-events-none">Unit(s)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Procurement Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8 bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Acquisition Date</p>
                                    <p class="text-sm font-black text-slate-800 mt-1 uppercase">{{ $asset->acquisition_date ? \Carbon\Carbon::parse($asset->acquisition_date)->format('F d, Y') : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Funding / Source</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase">{{ $asset->source_name }}</p>
                                    <div x-show="isEditing" class="relative group mt-1.5">
                                        <select name="acquisition_source_id" class="w-full appearance-none bg-white border-2 border-slate-100 rounded-xl px-4 py-2.5 text-xs font-black text-slate-700 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-200 cursor-pointer">
                                            @foreach($acquisitionSources as $source)
                                                <option value="{{ $source->id }}" {{ $asset->acquisition_source_id == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                                            @endforeach
                                        </select>
                                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Mode of Acquisition</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase">{{ $asset->mode_of_acquisition }}</p>
                                    <input x-show="isEditing" type="text" name="mode_of_acquisition" value="{{ $asset->mode_of_acquisition }}" class="w-full mt-1.5 bg-white border-2 border-slate-100 rounded-xl px-4 py-2.5 text-xs font-black text-slate-700 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-200">
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- TAB 2: Lifecycle & History --}}
                    <div x-show="activeTab === 'history'" class="animate-fade relative" x-cloak>
                        
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Activity Timeline
                            </h3>
                            <div class="relative">
                                <input type="text" placeholder="Filter history..." class="pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:outline-none focus:ring-2 focus:ring-deped/20 focus:border-deped transition-all">
                                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>

                        <div class="relative pl-3 max-w-3xl">
                            <div class="timeline-line"></div>
                            
                            <div class="space-y-6">
                                @foreach($timeline as $event)
                                @php
                                    $colors = match($event['type']) {
                                        'Transfer' => ['border' => 'border-deped', 'bg' => 'bg-deped'],
                                        'Return' => ['border' => 'border-amber-500', 'bg' => 'bg-amber-500'],
                                        'Temporary Borrow' => ['border' => 'border-blue-500', 'bg' => 'bg-blue-500'],
                                        default => ['border' => 'border-emerald-500', 'bg' => 'bg-emerald-500'],
                                    };
                                @endphp
                                <div class="relative pl-8 group">
                                    <div class="absolute left-[-2px] top-1 w-6 h-6 rounded-full bg-white border-2 {{ $colors['border'] }} flex items-center justify-center shadow-sm z-10">
                                        <div class="w-2 h-2 {{ $colors['bg'] }} rounded-full"></div>
                                    </div>
                                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] font-black text-white uppercase tracking-widest {{ $colors['bg'] }} px-2 py-0.5 rounded">{{ $event['type'] }}</span>
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $event['date'] }}</span>
                                            </div>
                                        </div>
                                        <p class="text-sm font-bold text-slate-800 mt-2">{{ $event['description'] }}</p>
                                        <div class="mt-3 flex items-center gap-2 border-t border-slate-100 pt-2">
                                            <div class="w-4 h-4 rounded-full bg-slate-200 flex items-center justify-center">
                                                <svg class="w-2.5 h-2.5 text-slate-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                                            </div>
                                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Performed by: {{ $event['user'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach

                                {{-- Load More Button --}}
                                <div class="relative pl-8 pt-4 pb-2">
                                    <button class="text-[10px] font-black text-deped uppercase tracking-[0.2em] hover:underline bg-deped_light px-4 py-2 rounded-lg">Load More History</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: Documents & Media --}}
                    <div x-show="activeTab === 'docs'" class="animate-fade space-y-6" x-cloak>
                        
                        {{-- Upload Form --}}
                        <form action="{{ route('assets.document.upload', $asset->id) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl border-2 border-dashed border-slate-200 hover:border-blue-500 transition-colors group relative" x-data="{ docName: null }">
                            @csrf
                            <input type="file" name="document" id="doc-upload" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,image/*" @change="docName = $event.target.files[0]?.name; document.getElementById('camera-upload').value = ''">
                            <input type="file" name="document_camera" id="camera-upload" class="hidden" accept="image/*" capture="environment" @change="docName = $event.target.files[0]?.name; document.getElementById('doc-upload').value = ''">
                            
                            <div class="flex flex-col items-center justify-center h-32" x-show="!docName">
                                <div class="flex gap-4 mb-3">
                                    <label for="doc-upload" class="w-14 h-14 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center shadow-sm cursor-pointer hover:scale-110 hover:bg-blue-100 transition-all" title="Browse Files">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    </label>
                                    <label for="camera-upload" class="w-14 h-14 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center shadow-sm cursor-pointer hover:scale-110 hover:bg-emerald-100 transition-all" title="Take a Picture">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </label>
                                </div>
                                <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest mb-1">Upload or Capture</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">PDF, DOCX, or Image (Max 10MB)</p>
                            </div>

                            <div x-show="docName" x-cloak class="flex flex-col items-center justify-center h-32">
                                <div class="w-14 h-14 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center shadow-sm mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest mb-1 truncate max-w-[250px]" x-text="docName"></h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Ready to upload</p>
                            </div>
                            
                            <div x-show="docName" x-cloak class="mt-4 flex justify-center gap-3">
                                <button type="button" @click="docName = null; document.getElementById('doc-upload').value = ''; document.getElementById('camera-upload').value = ''" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest transition-colors">Cancel</button>
                                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-600/30 transition-all active:scale-95">Upload Document</button>
                            </div>
                        </form>

                        {{-- Document List --}}
                        @if($documents->count() > 0)
                            <div class="space-y-3">
                                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-2 mb-3">Uploaded Files ({{ $documents->count() }})</h3>
                                @foreach($documents as $doc)
                                    <div class="flex items-center justify-between p-4 bg-white rounded-2xl shadow-sm border border-slate-100 hover:border-slate-300 transition-all group">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 border border-slate-100">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </div>
                                            <div>
                                                <p class="text-xs font-black text-slate-700 truncate max-w-[200px] lg:max-w-[300px]">{{ $doc->file_name }}</p>
                                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ number_format($doc->file_size / 1024, 1) }} KB &bull; {{ \Carbon\Carbon::parse($doc->created_at)->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:text-blue-600 hover:bg-blue-50 flex items-center justify-center transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </a>
                                            <form action="{{ route('assets.document.remove', $doc->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this document?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-8 border border-slate-100 rounded-2xl bg-slate-50/50">
                                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic mb-1">No Documents Uploaded</h3>
                            </div>
                        @endif

                    </div>

                </div>
            </div>

        </div>
        
        {{-- Confirmation Modal --}}
        <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center">
            <div x-show="showConfirmModal" x-transition.opacity class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showConfirmModal = false"></div>
            <div x-show="showConfirmModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95" class="bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 relative z-10 border border-slate-100 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-amber-100 text-amber-500 rounded-full flex items-center justify-center mb-5 ring-8 ring-amber-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight mb-2">Save Changes?</h3>
                <p class="text-xs font-bold text-slate-500 mb-8 leading-relaxed">Are you sure you want to update the asset specifications? This action will modify the permanent records.</p>
                <div class="flex items-center gap-3 w-full">
                    <button @click="showConfirmModal = false" class="flex-1 py-3.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-widest transition-colors">Cancel</button>
                    <button @click="isEditing = false; showConfirmModal = false" class="flex-1 py-3.5 px-4 bg-deped hover:bg-red-800 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-deped/30 transition-all active:scale-95">Yes, Save</button>
                </div>
            </div>
        </div>

        {{-- Transfer Asset Modal --}}
        <div x-show="showTransferModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center">
            <div x-show="showTransferModal" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showTransferModal = false"></div>
            <div x-show="showTransferModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95" class="bg-white rounded-3xl shadow-2xl w-full max-w-xl mx-4 relative z-10 flex flex-col overflow-hidden border border-slate-100 max-h-[90vh]">
                
                {{-- Modal Header --}}
                <div class="bg-slate-50 border-b border-slate-100 px-6 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-[0.1em]">Transfer Asset</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5">Reassign to a new custodian</p>
                        </div>
                    </div>
                    <button @click="showTransferModal = false" class="text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 p-2.5 rounded-full transition-colors active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                {{-- Form Body --}}
                <form action="{{ route('assets.transfer', $asset->id) }}" method="POST" class="flex flex-col min-h-0">
                    @csrf
                    <div class="p-6 space-y-5 overflow-y-auto custom-scroll" x-data="{
                        schools: @js($schools),
                        selectedSchoolId: '',
                        selectedOfficeSchoolName: '',
                        transferType: 'Permanent Reassignment',
                        
                        autofillLocation(schoolName) {
                            let base = schoolName.replace(/\s*(Elem|Elementary|National|High|Central|School|Main|Annex|\(.*\)).*/gi, '').trim();
                            let loc = base ? base + ', Zamboanga City' : schoolName + ', Zamboanga City';
                            let locInput = document.querySelector('input[name=location]');
                            if(locInput) {
                                locInput.value = loc;
                                locInput.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        },

                        updateFromName() {
                            let match = this.schools.find(s => s.name === this.selectedOfficeSchoolName);
                            if (match) {
                                this.selectedSchoolId = match.school_id;
                                this.autofillLocation(match.name);
                            }
                        },
                        
                        updateFromId() {
                            let match = this.schools.find(s => s.school_id === this.selectedSchoolId);
                            if (match) {
                                this.selectedOfficeSchoolName = match.name;
                                this.autofillLocation(match.name);
                            }
                        }
                    }">
                        
                        {{-- Current Info Header remains for context --}}
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex justify-between items-center relative overflow-hidden group mb-6">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500"></div>
                            <div class="pl-2">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Current Location</p>
                                <p class="text-xs font-black text-slate-700 uppercase">{{ $asset->office_school_name }}</p>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm border border-slate-200">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Office / School Type</label>
                                <input type="text" name="office_school_type" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300" placeholder="Type">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Nature of Occupancy</label>
                                <input type="text" name="nature_of_occupancy" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300" placeholder="Occupancy">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 relative z-40">
                            <!-- School ID -->
                            <div x-data="{ openId: false, searchId: '' }" class="relative" x-init="$watch('selectedSchoolId', val => searchId = val)">
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">School ID</label>
                                <div class="relative group" @click.away="openId = false">
                                    <input type="text" x-model="searchId" @focus="openId = true" @input="openId = true; selectedSchoolId = searchId" name="school_id" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300" placeholder="Search ID...">
                                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                    
                                    <div x-show="openId" x-cloak class="absolute w-full mt-2 bg-white border-2 border-slate-200 rounded-xl shadow-xl max-h-48 overflow-y-auto custom-scroll p-1 z-50">
                                        <template x-for="s in schools.filter(o => o.school_id && o.school_id.toLowerCase().includes(searchId.toLowerCase()))" :key="s.id">
                                            <div @click="searchId = s.school_id; selectedSchoolId = s.school_id; updateFromId(); openId = false" class="px-4 py-2.5 text-[10px] font-black text-slate-600 uppercase hover:bg-slate-100 hover:text-blue-600 rounded-lg cursor-pointer transition-colors" x-text="s.school_id + ' - ' + s.name"></div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- School Name -->
                            <div x-data="{ openName: false, searchName: '' }" class="relative" x-init="$watch('selectedOfficeSchoolName', val => searchName = val)">
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Office / School Name</label>
                                <div class="relative group" @click.away="openName = false">
                                    <input type="text" x-model="searchName" @focus="openName = true" @input="openName = true; selectedOfficeSchoolName = searchName" name="office_school_name" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300" placeholder="Search Name...">
                                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                    
                                    <div x-show="openName" x-cloak class="absolute w-full mt-2 bg-white border-2 border-slate-200 rounded-xl shadow-xl max-h-48 overflow-y-auto custom-scroll p-1 z-50">
                                        <template x-for="s in schools.filter(o => o.name && o.name.toLowerCase().includes(searchName.toLowerCase()))" :key="s.id">
                                            <div @click="searchName = s.name; selectedOfficeSchoolName = s.name; updateFromName(); openName = false" class="px-4 py-2.5 text-[10px] font-black text-slate-600 uppercase hover:bg-slate-100 hover:text-blue-600 rounded-lg cursor-pointer transition-colors" x-text="s.name"></div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Location</label>
                            <input type="text" name="location" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300" placeholder="Location">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Custodian (First / Middle / Last)</label>
                            <div class="grid grid-cols-3 gap-3">
                                <input type="text" name="custodian_first" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300" placeholder="First Name">
                                <input type="text" name="custodian_middle" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300" placeholder="Middle Name">
                                <input type="text" name="custodian_last" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300" placeholder="Last Name">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Position</label>
                                <input type="text" name="custodian_position" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300" placeholder="Position">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Contact No.</label>
                                <input type="text" name="custodian_contact" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300" placeholder="Contact No.">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Date of Transfer</label>
                                <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Transfer Type</label>
                                <div class="relative group">
                                    <select name="transfer_type" x-model="transferType" class="w-full appearance-none bg-white border-2 border-slate-200 rounded-xl pl-4 pr-10 py-3.5 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer">
                                        <option value="Permanent Reassignment">Permanent Reassignment</option>
                                        <option value="Temporary Borrow">Temporary Borrow</option>
                                    </select>
                                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <div x-show="transferType === 'Temporary Borrow'" x-cloak>
                            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Borrowed Until</label>
                            <input type="date" name="return_date" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Asset Condition <span class="text-deped">*</span></label>
                            <div class="relative group">
                                <select name="condition" required class="w-full appearance-none bg-white border-2 border-slate-200 rounded-xl pl-4 pr-10 py-3.5 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer">
                                    <option value="" disabled selected>Select condition...</option>
                                    <option value="Serviceable">Serviceable</option>
                                    <option value="For Repair">For Repair</option>
                                    <option value="Unserviceable">Unserviceable</option>
                                </select>
                                <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Reason / Remarks</label>
                            <textarea name="remarks" rows="2" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm resize-none hover:border-slate-300" placeholder="State reason for transfer..."></textarea>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="bg-slate-50 border-t border-slate-100 p-6 flex items-center justify-end gap-3">
                        <button type="button" @click="showTransferModal = false" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl text-xs font-black uppercase tracking-widest transition-colors shadow-sm active:scale-95">Cancel</button>
                        <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-blue-600/30 transition-all active:scale-95 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            Confirm Transfer
                        </button>
                    </div>
                </form>

            </div>
        </div>

        {{-- Return to AMU Modal --}}
        <div x-show="showReturnAmuModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center">
            <div x-show="showReturnAmuModal" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showReturnAmuModal = false"></div>
            <form action="{{ route('assets.return', $asset->id) }}" method="POST" x-show="showReturnAmuModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95" class="bg-white rounded-3xl shadow-2xl w-full max-w-xl mx-4 relative z-10 flex flex-col overflow-hidden border border-slate-100">
                @csrf
                {{-- Modal Header --}}
                <div class="bg-slate-50 border-b border-slate-100 px-6 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-[0.1em]">Return to AMU</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5">Surrender asset back to division</p>
                        </div>
                    </div>
                    <button type="button" @click="showReturnAmuModal = false" class="text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 p-2.5 rounded-full transition-colors active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 space-y-6">
                    
                    {{-- Current Info --}}
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex justify-between items-center relative overflow-hidden group">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500"></div>
                        <div class="pl-2">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Returning From</p>
                            <p class="text-xs font-black text-slate-700 uppercase">{{ $asset->office_school_name }}</p>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm border border-slate-200">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        </div>
                    </div>

                    {{-- Form Fields --}}
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Date of Return <span class="text-deped">*</span></label>
                            <input type="date" name="return_date" value="{{ date('Y-m-d') }}" required class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Asset Condition <span class="text-deped">*</span></label>
                            <div class="relative group">
                                <select name="condition" required class="w-full appearance-none bg-white border-2 border-slate-200 rounded-xl pl-4 pr-10 py-3 text-xs font-black text-slate-700 uppercase focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer">
                                    <option value="" disabled selected>Select condition...</option>
                                    <option value="Serviceable">Serviceable</option>
                                    <option value="For Repair">For Repair</option>
                                    <option value="Unserviceable">Unserviceable</option>
                                </select>
                                <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-hover:text-emerald-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Reason for Return</label>
                        <textarea name="remarks" rows="3" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-bold text-slate-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all shadow-sm resize-none hover:border-slate-300" placeholder="State reason why the asset is being surrendered..."></textarea>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="bg-slate-50 border-t border-slate-100 p-6 flex items-center justify-end gap-3">
                    <button type="button" @click="showReturnAmuModal = false" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl text-xs font-black uppercase tracking-widest transition-colors shadow-sm active:scale-95">Cancel</button>
                    <button type="submit" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-emerald-600/30 transition-all active:scale-95 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        Confirm Return
                    </button>
                </div>

            </form>
        </div>

        {{-- Hidden Form for Photo Removal --}}
        <form id="remove-photo-form" action="{{ route('assets.photo.remove', $asset->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        {{-- Remove Photo Confirm Modal --}}
        <div x-show="showRemoveConfirmModal" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center">
            <div x-show="showRemoveConfirmModal" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showRemoveConfirmModal = false"></div>
            <div x-show="showRemoveConfirmModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" class="bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 relative z-10 border border-slate-100 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-5 ring-8 ring-red-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight mb-2">Remove Photo?</h3>
                <p class="text-xs font-bold text-slate-500 mb-8 leading-relaxed">Are you sure you want to delete this asset's photo permanently? This action cannot be undone.</p>
                <div class="flex items-center gap-3 w-full">
                    <button type="button" @click="showRemoveConfirmModal = false" class="flex-1 py-3.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-widest transition-colors">Cancel</button>
                    <button type="button" @click="document.getElementById('remove-photo-form').submit()" class="flex-1 py-3.5 px-4 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-red-600/30 transition-all active:scale-95">Yes, Remove</button>
                </div>
            </div>
        </div>

        {{-- Save Changes Confirm Modal --}}
        <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center">
            <div x-show="showConfirmModal" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showConfirmModal = false"></div>
            <div x-show="showConfirmModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" class="bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 relative z-10 border border-slate-100 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-5 ring-8 ring-emerald-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight mb-2">Save Changes?</h3>
                <p class="text-xs font-bold text-slate-500 mb-8 leading-relaxed">Are you sure you want to update the specifications for this asset?</p>
                <div class="flex items-center gap-3 w-full">
                    <button type="button" @click="showConfirmModal = false" class="flex-1 py-3.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-widest transition-colors">Go Back</button>
                    <button type="button" @click="isSaving = true; document.getElementById('update-asset-form').submit()" :disabled="isSaving" class="flex-1 py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-emerald-600/30 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <template x-if="!isSaving">
                            <span>Yes, Save</span>
                        </template>
                        <template x-if="isSaving">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Saving...</span>
                            </div>
                        </template>
                    </button>
                </div>
            </div>
        </div>

        {{-- Fullscreen Image Modal --}}
        <div x-show="showImageFullscreen" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/95 backdrop-blur-md">
            <button @click="showImageFullscreen = false" class="absolute top-6 right-6 text-white/50 hover:text-white transition-colors p-2 rounded-full hover:bg-white/10 active:scale-95">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <img src="{{ $asset->photo_path ? asset('storage/' . $asset->photo_path) : '' }}" class="max-w-[90vw] max-h-[90vh] object-contain rounded-xl shadow-2xl" @click.away="showImageFullscreen = false" x-show="showImageFullscreen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        </div>

    </div>

</body>
</html>
```

## File: `resources/views/partials/inventory-edit-step.blade.php`

```html
<div id="stepInventoryEdit" class="step-content">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-6">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase text-blue-600">Inventory <span class="text-slate-900">Editor</span></h2>
            <p class="text-slate-400 text-sm font-bold uppercase mt-1 tracking-widest leading-tight">Bulk update master inventory records</p>
        </div>
        <div class="flex items-center gap-4">
            <button onclick="toggleEditFilters()" id="toggleEditFilterBtn" class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-500 bg-white border border-slate-100 hover:border-blue-600 transition-all flex items-center gap-2 active:scale-95 shadow-sm italic">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                Hide Filters
            </button>
        </div>
    </div>

    <!-- Filter Configuration -->
    <div id="editFilterSection" class="bg-white rounded-[2.5rem] shadow-lg border border-slate-100 p-8 mb-8 relative z-50 transition-all duration-300 origin-top">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 relative z-10">
            {{-- Row 1 --}}
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Classification</label>
                <select id="editFilterClass" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
                    <option value="">All Classifications</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Category</label>
                <select id="editFilterCat" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
                    <option value="">All Categories</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Item</label>
                <select id="editFilterItem" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
                    <option value="">All Items</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Cost Sorting</label>
                <select id="editFilterSort" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
                    <option value="">Default (ID)</option>
                    <option value="low_to_high">Acquisition Cost: Low to High</option>
                    <option value="high_to_low">Acquisition Cost: High to Low</option>
                </select>
            </div>

            {{-- Row 2 --}}
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">School Name</label>
                <select id="editFilterSchool" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
                    <option value="">All Schools</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Source of Acquisition</label>
                <select id="editFilterSource" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
                    <option value="">All Sources</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Mode of Acquisition</label>
                <select id="editFilterMode" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
                    <option value="">All Modes</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Date Acquired (Acceptance)</label>
                <input type="date" id="editFilterDate" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic text-red-500">Data Integrity (Empty Fields)</label>
                <select id="editFilterIntegrity" class="w-full bg-slate-50 border-red-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                    <option value="">No Integrity Filter</option>
                    <option value="article">Missing Article/Item</option>
                    <option value="category">Missing Category</option>
                    <option value="classification">Missing Classification</option>
                    <option value="description">Missing Description</option>
                    <option value="property_number">Missing Property Number</option>
                    <option value="unit_of_measurement">Missing Unit (UOM)</option>
                    <option value="acq_source">Missing Acquisition Source</option>
                    <option value="mode_of_acquisition">Missing Mode</option>
                    <option value="acceptance_date">Missing Acceptance Date</option>
                    <option value="school_id">Missing School ID</option>
                    <option value="school_name">Missing School Name</option>
                    <option value="occupancy">Missing Nature of Occupancy</option>
                    <option value="location">Missing Location</option>
                    <option value="acquisition_date">Missing Acquisition Date</option>
                </select>
            </div>
        </div>
        <div class="mt-8 flex justify-end items-center gap-8 relative z-10">
            <button onclick="clearEditFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-blue-600 transition-all italic">Clear All Filters</button>
            <button onclick="editFetchData()" class="px-8 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 transition-all active:scale-95 shadow-lg shadow-slate-200 italic">Apply Configuration</button>
        </div>
    </div>

    <!-- Data Grid -->
    <div id="editAssetTableCard" class="bg-white rounded-[2rem] border border-slate-100 shadow-xl relative overflow-hidden flex flex-col">
        
        {{-- Toolbar --}}
        <div id="editAssetToolbar" class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap bg-slate-50">
            <div class="flex items-center gap-3">
                <div class="flex bg-slate-200/50 rounded-xl p-1 gap-1">
                    <button id="editTabAssetSource" onclick="switchEditAssetTab('source')"
                        class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg bg-blue-600 text-white shadow-sm transition-all">
                        Asset Source
                    </button>
                    <button id="editTabAssetDist" onclick="switchEditAssetTab('distribution')"
                        class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg text-slate-500 hover:text-slate-700 transition-all">
                        Asset Distribution
                    </button>
                </div>
                <span id="editAssetTabLabel" class="hidden md:block text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Asset Source</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex bg-slate-100 rounded-2xl p-1 gap-1 border border-slate-200">
                    <button onclick="editUndo()" id="editUndoBtn" class="px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white hover:text-blue-600 transition-all active:scale-95 flex items-center gap-2 opacity-50 cursor-not-allowed group">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        Undo
                    </button>
                    <div class="w-[1px] bg-slate-200 h-3 self-center my-auto"></div>
                    <button onclick="editRedo()" id="editRedoBtn" class="px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white hover:text-blue-600 transition-all active:scale-95 flex items-center gap-2 opacity-50 cursor-not-allowed group">
                        Redo
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"/></svg>
                    </button>
                </div>

                <button onclick="openEditBulkModal()" class="px-5 py-2.5 bg-blue-50 text-blue-600 rounded-xl text-[9px] font-black uppercase tracking-widest flex items-center gap-2 shadow-sm hover:bg-blue-100 transition-all active:scale-95 italic border border-blue-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16.862 4.487l1.688-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                    Bulk Edit
                </button>

                <button onclick="saveEditChanges()" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[9px] font-black uppercase tracking-widest flex items-center gap-2 shadow-lg hover:bg-blue-600 transition-all active:scale-95 italic">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
        </div>

        {{-- ── Asset Source Table ── --}}
        <div id="editPanelAssetSource" class="flex-grow flex flex-col min-h-0">
            <div id="editSourceScroll" class="xls-scroll-wrap custom-scroll overflow-x-auto overflow-y-auto transition-all duration-300" style="max-height: calc(100vh - 450px);">
                <table class="w-full border-collapse" style="min-width:2800px;">
                    <thead class="sticky top-0 bg-slate-50 z-20 shadow-sm">
                        <tr>
                            <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                            <th class="xls-th col-identity" style="min-width:140px">CLASSIFICATION</th>
                            <th class="xls-th col-identity" style="min-width:140px">CATEGORY</th>
                            <th class="xls-th col-identity" style="min-width:140px">ITEM</th>
                            <th class="xls-th col-context" style="min-width:180px">DESCRIPTION</th>
                            <th class="xls-th col-context" style="min-width:120px">BRAND</th>
                            <th class="xls-th col-context" style="min-width:120px">MODEL</th>
                            <th class="xls-th col-context" style="min-width:140px">SERIAL NO.</th>
                            <th class="xls-th col-context" style="min-width:100px">UNIT</th>
                            <th class="xls-th col-status" style="min-width:160px">ACQUISITION SOURCE</th>
                            <th class="xls-th col-status" style="min-width:140px">MODE</th>
                            <th class="xls-th col-personnel" style="min-width:160px">SOURCE PERSONNEL</th>
                            <th class="xls-th col-personnel" style="min-width:160px">PERSONNEL POS</th>
                            <th class="xls-th col-financial text-right" style="min-width:120px">COST / UNIT (₱)</th>
                            <th class="xls-th col-financial text-right" style="min-width:80px">QTY</th>
                            <th class="xls-th col-temporal text-right" style="min-width:110px">USEFUL LIFE (YRS)</th>
                            <th class="xls-th col-temporal" style="min-width:140px">ACCEPTANCE DATE</th>
                            <th class="xls-th col-status" style="min-width:160px">CONDITION</th>
                        </tr>
                    </thead>
                    <tbody id="editAssetSourceBody"></tbody>
                </table>
            </div>
        </div>

        {{-- ── Asset Distribution Table ── --}}
        <div id="editPanelAssetDist" class="hidden flex-grow flex flex-col min-h-0">
            <div id="editDistScroll" class="xls-scroll-wrap custom-scroll overflow-x-auto overflow-y-auto transition-all duration-300" style="max-height: calc(100vh - 450px);">
                <table class="w-full border-collapse" style="min-width:2400px;">
                    <thead class="sticky top-0 bg-slate-50 z-20 shadow-sm">
                        <tr>
                            <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                            <th class="xls-th col-context" style="min-width:120px">REGION</th>
                            <th class="xls-th col-context" style="min-width:180px">DIVISION</th>
                            <th class="xls-th col-context" style="min-width:160px">OFFICE/SCHOOL TYPE</th>
                            <th class="xls-th col-identity" style="min-width:100px">SCHOOL ID</th>
                            <th class="xls-th col-identity" style="min-width:210px">OFFICE/SCHOOL NAME</th>
                            <th class="xls-th col-personnel" style="min-width:160px">CUSTODIAN FIRST NAME</th>
                            <th class="xls-th col-personnel" style="min-width:160px">CUSTODIAN MIDDLE NAME</th>
                            <th class="xls-th col-personnel" style="min-width:160px">CUSTODIAN LAST NAME</th>
                            <th class="xls-th col-personnel" style="min-width:160px">CUSTODIAN POSITION</th>
                            <th class="xls-th col-personnel" style="min-width:160px">CUSTODIAN CONTACT NO.</th>
                            <th class="xls-th col-context" style="min-width:160px">NATURE OF OCCUPANCY</th>
                            <th class="xls-th col-context" style="min-width:160px">LOCATION</th>
                            <th class="xls-th col-identity" style="min-width:150px">PROPERTY NO.</th>
                            <th class="xls-th col-financial text-right" style="min-width:130px">ACQUISITION COST (₱)</th>
                            <th class="xls-th col-temporal" style="min-width:140px">ACQUISITION DATE</th>
                        </tr>
                    </thead>
                    <tbody id="editAssetDistBody"></tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-6">
                <p id="editRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                <div id="editPaginationControls" class="flex items-center gap-3 border-l border-slate-200 pl-6">
                    <button onclick="editPrevPage()" id="editPrevBtn" class="pg-btn">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                        Prev
                    </button>
                    <div class="flex items-center gap-2 px-4 py-2 bg-slate-900/40 dark:bg-slate-800/60 rounded-xl border border-slate-200/10 backdrop-blur-md">
                        <span id="editCurrentPage" class="text-[10px] font-black text-slate-700 dark:text-blue-400">1</span>
                        <span class="text-[10px] font-bold text-slate-400">/</span>
                        <span id="editTotalPages" class="text-[10px] font-black text-slate-400">1</span>
                    </div>
                    <button onclick="editNextPage()" id="editNextBtn" class="pg-btn">
                        Next
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Edit Modal -->
<div id="editBulkModal" class="fixed inset-0 z-[150] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" onclick="closeEditBulkModal()"></div>
    <div class="bg-[#1e293b] border border-slate-700 rounded-[2rem] shadow-2xl w-[95vw] max-w-5xl max-h-[90vh] flex flex-col relative z-10 transform scale-95 transition-transform duration-300 overflow-hidden">
        
        {{-- Header --}}
        <div class="px-6 md:px-10 py-6 border-b border-slate-700/50 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-slate-800/30">
            <div class="flex-grow">
                <h3 class="text-2xl md:text-3xl font-black text-blue-400 uppercase tracking-tighter italic">Bulk Edit Rows</h3>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mt-1">Update specific columns for a range of rows</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-4 bg-slate-900/50 px-5 py-3 rounded-2xl border border-slate-700 shadow-inner">
                    <div class="flex flex-col">
                        <label class="text-[8px] font-black text-slate-500 uppercase tracking-widest leading-none mb-1">From Row #</label>
                        <input type="number" id="editBulkFrom" value="1" min="1" class="w-12 bg-transparent font-black text-white outline-none text-lg leading-none">
                    </div>
                    <div class="w-px h-6 bg-slate-700"></div>
                    <div class="flex flex-col">
                        <label class="text-[8px] font-black text-slate-500 uppercase tracking-widest leading-none mb-1">To Row #</label>
                        <input type="number" id="editBulkTo" value="1" min="1" class="w-16 bg-transparent font-black text-white outline-none text-lg leading-none">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="closeEditBulkModal()" class="px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-white transition-all italic">Cancel</button>
                    <button onclick="applyEditBulk()" class="px-8 py-4 rounded-2xl text-[11px] font-black text-white bg-blue-600 hover:bg-blue-500 shadow-xl shadow-blue-500/20 transition-all active:scale-95 uppercase tracking-widest italic">Apply Bulk Edit</button>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6 md:p-10 overflow-y-auto custom-scroll flex-1 space-y-12 bg-slate-800/20">
            
            {{-- Source Section --}}
            <div class="animate-fade">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-8 h-8 bg-blue-500/10 text-blue-400 rounded-xl flex items-center justify-center text-xs font-black shrink-0 border border-blue-500/20 shadow-lg shadow-blue-500/5">1</div>
                    <h4 class="font-black text-white uppercase tracking-[0.15em] text-sm italic">Asset Data Entry (Source)</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Classification</label>
                        <input type="text" id="ebClassification" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Category</label>
                        <input type="text" id="ebCategory" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Item</label>
                        <input type="text" id="ebItem" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Description</label>
                        <input type="text" id="ebDescription" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Brand</label>
                        <input type="text" id="ebBrand" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Model</label>
                        <input type="text" id="ebModel" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Serial Number</label>
                        <input type="text" id="ebSerialNo" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Unit of Measurement</label>
                        <input type="text" id="ebUom" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Acquisition Source</label>
                        <input type="text" id="ebAcqSource" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Mode of Procurement</label>
                        <input type="text" id="ebMode" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Source Personnel</label>
                        <input type="text" id="ebPersonnel" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Personnel Position</label>
                        <input type="text" id="ebPosition" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:col-span-2">
                        <div class="relative space-y-2">
                            <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Cost per Unit</label>
                            <div class="relative">
                                <input type="number" id="ebCost" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-right pr-12 placeholder:text-slate-700" placeholder="Leave empty to ignore" min="0" step="0.01">
                                <span class="absolute right-5 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-600">₱</span>
                            </div>
                        </div>
                        <div class="relative space-y-2">
                            <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Quantity</label>
                            <input type="number" id="ebQty" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-right placeholder:text-slate-700" placeholder="Leave empty to ignore" min="0" step="1">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:col-span-2">
                        <div class="relative space-y-2">
                            <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Expected Useful Life</label>
                            <div class="relative">
                                <input type="number" id="ebLife" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-right pr-14 placeholder:text-slate-700" placeholder="Leave empty to ignore" min="0" step="1">
                                <span class="absolute right-5 top-1/2 -translate-y-1/2 text-[8px] font-black text-slate-600">YRS</span>
                            </div>
                        </div>
                        <div class="relative space-y-2">
                            <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Acceptance Date</label>
                            <input type="date" id="ebDate1" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all [color-scheme:dark]">
                        </div>
                    </div>

                    <div class="relative space-y-2 md:col-span-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Condition</label>
                        <select id="ebRemarks" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                            <option value="" class="bg-[#1e293b]">-- IGNORE CHANGES --</option>
                            <option value="Good Condition" class="bg-[#1e293b]">GOOD CONDITION</option>
                            <option value="Needs Repair" class="bg-[#1e293b]">NEEDS REPAIR</option>
                            <option value="Not Useable" class="bg-[#1e293b]">NOT USEABLE</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-700/50"></div>

            {{-- Target Section --}}
            <div class="animate-fade">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-8 h-8 bg-blue-500/10 text-blue-400 rounded-xl flex items-center justify-center text-xs font-black shrink-0 border border-blue-500/20 shadow-lg shadow-blue-500/5">2</div>
                    <h4 class="font-black text-white uppercase tracking-[0.15em] text-sm italic">Asset Distribution (Target)</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1 block">Region</label>
                        <div class="w-full bg-slate-900/30 border border-slate-800 rounded-xl px-5 py-4 text-xs font-black text-slate-600 uppercase tracking-widest italic opacity-50 cursor-not-allowed">Region IX</div>
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1 block">Division</label>
                        <div class="w-full bg-slate-900/30 border border-slate-800 rounded-xl px-5 py-4 text-xs font-black text-slate-600 uppercase tracking-widest italic opacity-50 cursor-not-allowed">Division of Zamboanga City</div>
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Office/School Type</label>
                        <input type="text" list="dl-school-type" id="ebSchoolType" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-600" placeholder="-- IGNORE CHANGES --">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">School ID</label>
                        <input type="text" id="ebSchoolId" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore" inputmode="numeric">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Office/School Name</label>
                        <input type="text" id="ebSchoolName" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore" oninput="
                            if(typeof allSchoolsList !== 'undefined' && typeof detectItemSchoolType === 'function'){
                                const s = allSchoolsList.find(x => x.name.toLowerCase() === this.value.toLowerCase());
                                if(s) {
                                    const t = detectItemSchoolType(s.name) || '';
                                    document.getElementById('ebSchoolType').value = t;
                                    document.getElementById('ebSchoolId').value = s.school_id;
                                    if(typeof cleanSchoolNameForLocation === 'function') document.getElementById('ebLocation').value = cleanSchoolNameForLocation(s.name);
                                } else if(this.value.trim()){
                                    const t = detectItemSchoolType(this.value);
                                    if(t) document.getElementById('ebSchoolType').value = t;
                                    document.getElementById('ebSchoolId').value = '';
                                    if(typeof cleanSchoolNameForLocation === 'function') document.getElementById('ebLocation').value = cleanSchoolNameForLocation(this.value);
                                }
                            }">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Custodian Full Name</label>
                        <div class="grid grid-cols-3 gap-2">
                            <input type="text" id="ebCustFirst" placeholder="First" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-4 text-[10px] font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                            <input type="text" id="ebCustMiddle" placeholder="Middle" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-4 text-[10px] font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                            <input type="text" id="ebCustLast" placeholder="Last" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-4 text-[10px] font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                        </div>
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Custodian Position</label>
                        <input type="text" id="ebCustPos" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Custodian Contact No.</label>
                        <input type="text" id="ebCustContact" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Nature of Occupancy</label>
                        <input type="text" id="ebOccupancy" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Location / Room</label>
                        <input type="text" id="ebLocation" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Property Number</label>
                        <input type="text" id="ebPropertyNo" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Acquisition Date</label>
                        <input type="date" id="ebDate2" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all [color-scheme:dark]">
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .update-badge {
        position: absolute; top: 3px; left: 3px; font-size: 8px; font-weight: 900; background: #3b82f6; color: white; padding: 1px 4px; border-radius: 4px; text-transform: uppercase; pointer-events: none; z-index: 10; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2); letter-spacing: 0.5px;
    }
    .edit-input {
        width: 100%; padding: 11px 14px; font-size: 11.5px; font-weight: 600; color: #334155; background: rgba(59, 130, 246, 0.03); border: 1px solid transparent; outline: none; box-sizing: border-box; line-height: 1.4; transition: all 0.2s; height: 100%; min-height: 40px;
    }
    .edit-input:focus {
        background: rgba(59, 130, 246, 0.05); border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }
    .edit-readonly {
        background: rgba(0,0,0,0.02) !important; color: #94a3b8 !important; cursor: not-allowed;
    }
</style>

<script>
    let editAllData = [];
    let editOriginalData = []; // Deep copy to check diffs
    let editUndoStack = [];
    let editRedoStack = [];
    let editCurrentPage = 1;
    const editRowsPerPage = 50;

    function toggleEditFilters() {
        const section = document.getElementById('editFilterSection');
        const btn = document.getElementById('toggleEditFilterBtn');
        const srcScroll = document.getElementById('editSourceScroll');
        const distScroll = document.getElementById('editDistScroll');
        
        if (section.classList.contains('hidden')) {
            section.classList.remove('hidden');
            srcScroll.classList.remove('!max-h-[750px]');
            distScroll.classList.remove('!max-h-[750px]');
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Hide Filters`;
        } else {
            section.classList.add('hidden');
            srcScroll.classList.add('!max-h-[750px]');
            distScroll.classList.add('!max-h-[750px]');
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Show Filters`;
        }
    }

    function initInventoryEdit() {
        // Fetch filters on load
        fetch('{{ route("api.reports.filters") }}?report_type=ALL')
            .then(res => res.json())
            .then(data => {
                populateEditSelect('editFilterClass', data.classifications);
                populateEditSelect('editFilterCat', data.categories);
                populateEditSelect('editFilterItem', data.items);
                populateEditSelect('editFilterSchool', data.schools);
                populateEditSelect('editFilterSource', data.sources);
                populateEditSelect('editFilterMode', data.modes);
            });
    }

    function clearEditFilters() {
        ['editFilterClass', 'editFilterCat', 'editFilterItem', 'editFilterSort', 'editFilterSchool', 'editFilterSource', 'editFilterMode', 'editFilterDate', 'editFilterIntegrity'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        editFetchData();
    }

    function populateEditSelect(id, options) {
        const sel = document.getElementById(id);
        if (!sel) return;
        const originalFirstOption = sel.options[0];
        sel.innerHTML = '';
        sel.appendChild(originalFirstOption);
        options.forEach(opt => {
            const el = document.createElement('option');
            el.value = opt; el.textContent = opt;
            sel.appendChild(el);
        });
    }

    function editFetchData() {
        const filterIds = {
            'editFilterClass': 'classification',
            'editFilterCat': 'category',
            'editFilterItem': 'article',
            'editFilterSchool': 'schoolName',
            'editFilterSource': 'source',
            'editFilterMode': 'mode',
            'editFilterDate': 'dateAcquired',
            'editFilterIntegrity': 'emptyCol',
            'editFilterSort': 'sortCost'
        };

        const filters = {};
        for (const [id, key] of Object.entries(filterIds)) {
            const el = document.getElementById(id);
            filters[key] = el ? el.value : '';
        }

        const loader = document.getElementById('editAssetLoading');
        if (loader) loader.classList.remove('hidden');

        fetch('{{ route("api.inventory.edit_preview") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ report_type: 'ALL', filters: filters })
        })
        .then(res => res.json())
        .then(data => {
            editAllData = data.rows || [];
            editOriginalData = JSON.parse(JSON.stringify(editAllData));
            editCurrentPage = 1;
            editUndoStack = [];
            editRedoStack = [];
            updateEditUndoBtn();
            renderEditTable();
            if (editAllData.length === 0) {
                Swal.fire({
                    title: 'No Assets Found',
                    text: 'No records match your current filter configuration.',
                    icon: 'info',
                    customClass: { popup: 'rounded-[2rem]' }
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                title: 'Error',
                text: 'Failed to load inventory data.',
                icon: 'error',
                customClass: { popup: 'rounded-[2rem]' }
            });
        })
        .finally(() => {
            if (loader) loader.classList.add('hidden');
        });
    }

    function switchEditAssetTab(tab) {
        const srcPanel = document.getElementById('editPanelAssetSource');
        const distPanel = document.getElementById('editPanelAssetDist');
        const tabSrc   = document.getElementById('editTabAssetSource');
        const tabDst   = document.getElementById('editTabAssetDist');
        const label    = document.getElementById('editAssetTabLabel');
        const ON  = 'px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg bg-blue-600 text-white shadow-sm transition-all';
        const OFF = 'px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg text-slate-500 hover:text-slate-700 transition-all';
        if (tab === 'source') {
            srcPanel.classList.remove('hidden');
            distPanel.classList.add('hidden');
            tabSrc.className = ON; tabDst.className = OFF;
            label.textContent = 'Asset Source';
        } else {
            srcPanel.classList.add('hidden');
            distPanel.classList.remove('hidden');
            tabSrc.className = OFF; tabDst.className = ON;
            label.textContent = 'Asset Distribution';
        }
    }

    function renderEditTable() {
        const srcTbody = document.getElementById('editAssetSourceBody');
        const dstTbody = document.getElementById('editAssetDistBody');
        if (!srcTbody || !dstTbody) return;
        srcTbody.innerHTML = '';
        dstTbody.innerHTML = '';
        
        if (editAllData.length === 0) {
            document.getElementById('editRowCountLabel').textContent = "0 Rows";
            return;
        }

        const start = (editCurrentPage - 1) * editRowsPerPage;
        const end = start + editRowsPerPage;
        const pageData = editAllData.slice(start, end);

        pageData.forEach((row, idx) => {
            const displayNum = start + idx + 1;
            const orig = editOriginalData.find(o => String(o.dist_id) === String(row.dist_id)) || {};
            
            const renderCell = (col, val, isReadonly) => {
                const val1 = String(val ?? '').trim();
                const val2 = String(orig[col] ?? '').trim();
                const hasChanged = val1 !== val2;
                const badgeHtml = hasChanged ? `<span class="update-badge">Update</span>` : '';
                const safeVal = (val ?? '').toString().replace(/"/g, '&quot;');
                
                if (isReadonly) {
                    return `<td class="xls-td p-0 relative"><input type="text" class="xls-input edit-readonly w-full h-full" value="${safeVal}" readonly tabindex="-1">${badgeHtml}</td>`;
                }
                
                if (col === 'remarks') {
                    return `<td class="xls-td p-0 relative">
                        <select data-id="${row.dist_id}" data-col="${col}" onchange="syncEditCell(this)" class="xls-input w-full h-full bg-transparent">
                            <option value="Good Condition" ${val === 'Good Condition' ? 'selected' : ''}>Good Condition</option>
                            <option value="Needs Repair" ${val === 'Needs Repair' ? 'selected' : ''}>Needs Repair</option>
                            <option value="Not Useable" ${val === 'Not Useable' ? 'selected' : ''}>Not Useable</option>
                        </select>
                        ${badgeHtml}
                    </td>`;
                }
                
                return `<td class="xls-td p-0 relative"><input type="text" data-id="${row.dist_id}" data-col="${col}" value="${safeVal}" onchange="syncEditCell(this)" class="xls-input w-full h-full bg-transparent">${badgeHtml}</td>`;
            };

            // Source Table Row
            const srcTr = document.createElement('tr');
            srcTr.className = 'xls-row group border-b border-slate-100';
            srcTr.innerHTML = `
                <td class="xls-td text-center sticky left-0 w-10 bg-white z-10"><span class="text-[10px] font-black text-slate-300">${displayNum}</span></td>
                ${renderCell('classification', row.classification, false)}
                ${renderCell('category', row.category, false)}
                ${renderCell('article', row.article, false)}
                ${renderCell('description', row.description, false)}
                ${renderCell('brand', row.brand, false)}
                ${renderCell('model', row.model, false)}
                ${renderCell('serial_no', row.serial_no, false)}
                ${renderCell('unit_of_measurement', row.unit_of_measurement, false)}
                ${renderCell('acq_source', row.acq_source, false)}
                ${renderCell('mode_of_acquisition', row.mode_of_acquisition, false)}
                ${renderCell('source_personnel', row.source_personnel, false)}
                ${renderCell('personnel_position', row.personnel_position, false)}
                ${renderCell('asset_cost', row.asset_cost, false)}
                ${renderCell('quantity', row.quantity, false)}
                ${renderCell('estimated_useful_life', row.estimated_useful_life, false)}
                ${renderCell('acceptance_date', row.acceptance_date, false)}
                ${renderCell('remarks', row.remarks, false)}
            `;
            srcTbody.appendChild(srcTr);

            // Distribution Table Row
            const dstTr = document.createElement('tr');
            dstTr.className = 'xls-row group border-b border-slate-100';
            const costVal = parseFloat(row.asset_cost || 0);
            const qtyVal = parseInt(row.quantity || 0);
            const totalCost = (costVal * qtyVal).toFixed(2);
            dstTr.innerHTML = `
                <td class="xls-td text-center sticky left-0 w-10 bg-white z-10"><span class="text-[10px] font-black text-slate-300">${displayNum}</span></td>
                <td class="xls-td p-0 relative"><span class="xls-const w-full h-full flex items-center px-4">Region IX</span></td>
                <td class="xls-td p-0 relative"><span class="xls-const w-full h-full flex items-center px-4">Division of Zamboanga City</span></td>
                ${renderCell('school_type', row.school_type, false)}
                ${renderCell('school_id', row.school_id, false)}
                ${renderCell('office_school_name', row.office_school_name, false)}
                ${renderCell('custodian_first_name', row.custodian_first_name, false)}
                ${renderCell('custodian_middle_name', row.custodian_middle_name, false)}
                ${renderCell('custodian_last_name', row.custodian_last_name, false)}
                ${renderCell('custodian_position', row.custodian_position, false)}
                ${renderCell('custodian_contact_number', row.custodian_contact_number, false)}
                ${renderCell('nature_of_occupancy', row.nature_of_occupancy, false)}
                ${renderCell('location', row.location, false)}
                ${renderCell('property_number', row.property_number, false)}
                <td class="xls-td p-0 relative"><input type="text" class="xls-input edit-readonly text-right w-full h-full" value="${totalCost}" readonly tabindex="-1"></td>
                ${renderCell('acquisition_date', row.acquisition_date, false)}
            `;
            dstTbody.appendChild(dstTr);
        });

        const totalPages = Math.ceil(editAllData.length / editRowsPerPage) || 1;
        document.getElementById('editRowCountLabel').textContent = editAllData.length + " Rows (paired)";
        
        document.getElementById('editCurrentPage').textContent = editCurrentPage;
        document.getElementById('editTotalPages').textContent = totalPages;
        document.getElementById('editPrevBtn').disabled = editCurrentPage === 1;
        document.getElementById('editNextBtn').disabled = editCurrentPage === totalPages;
    }

    function syncEditCell(input) {
        const id = parseInt(input.getAttribute('data-id'));
        const col = input.getAttribute('data-col');
        const newVal = input.value;
        const row = editAllData.find(r => r.dist_id === id);
        if (row) {
            const oldVal = row[col] ?? '';
            if (String(oldVal).trim() !== String(newVal).trim()) {
                editUndoStack.push({ type: 'single', rowId: id, col: col, oldVal: oldVal, newVal: newVal });
                row[col] = newVal;
                editRedoStack = [];
                updateEditUndoBtn();
                renderEditTable(); 
            }
        }
    }

    function editPrevPage() { if (editCurrentPage > 1) { editCurrentPage--; renderEditTable(); } }
    function editNextPage() { const t = Math.ceil(editAllData.length/editRowsPerPage); if (editCurrentPage < t) { editCurrentPage++; renderEditTable(); } }

    function openEditBulkModal() {
        if(editAllData.length === 0) return Swal.fire('No Data', 'Load assets first.', 'info');
        const m = document.getElementById('editBulkModal');
        m.classList.remove('hidden');
        document.querySelectorAll('#editBulkModal input:not([id="editBulkFrom"]):not([id="editBulkTo"])').forEach(i => i.value = '');
        document.getElementById('ebRemarks').value = '';

        // Default From=1, To=total fetched rows
        const maxRows = editAllData.length;
        const fromInput = document.getElementById('editBulkFrom');
        const toInput   = document.getElementById('editBulkTo');
        fromInput.value = 1;
        fromInput.max   = maxRows;
        toInput.value   = maxRows;
        toInput.max     = maxRows;

        // Live warning if user exceeds max
        toInput.oninput = function() {
            const val = parseInt(this.value);
            if (val > maxRows) {
                this.style.color = '#ef4444';
            } else {
                this.style.color = '';
            }
        };
        
        setTimeout(() => {
            m.classList.remove('opacity-0');
            m.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }
    
    function closeEditBulkModal() {
        const m = document.getElementById('editBulkModal');
        m.classList.add('opacity-0');
        m.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => m.classList.add('hidden'), 300);
    }

    function applyEditBulk() {
        const from = parseInt(document.getElementById('editBulkFrom').value);
        const to = parseInt(document.getElementById('editBulkTo').value);

        const maxRows = editAllData.length;

        if (isNaN(from) || isNaN(to) || from < 1 || to < from) {
            return Swal.fire('Invalid Range', 'Enter a valid row range (From must be ≤ To).', 'error');
        }

        if (to > maxRows) {
            return Swal.fire({
                icon: 'warning',
                title: 'Exceeds Total Rows',
                html: `<b>To Row</b> cannot exceed <b>${maxRows}</b> (total fetched assets).<br>Please enter a value within range.`,
                confirmButtonColor: '#c00000',
                customClass: { popup: 'rounded-[2rem]' }
            });
        }

        if (from > maxRows) {
            return Swal.fire('Invalid Range', `From Row cannot exceed the total of ${maxRows} fetched assets.`, 'error');
        }

        const toLimit = Math.min(to, editAllData.length);
        
        const bulkMapping = {
            'ebClassification': 'classification',
            'ebCategory': 'category',
            'ebItem': 'article',
            'ebDescription': 'description',
            'ebBrand': 'brand',
            'ebModel': 'model',
            'ebSerialNo': 'serial_no',
            'ebUom': 'unit_of_measurement',
            'ebAcqSource': 'acq_source',
            'ebMode': 'mode_of_acquisition',
            'ebPersonnel': 'source_personnel',
            'ebPosition': 'personnel_position',
            'ebCost': 'asset_cost',
            'ebQty': 'quantity',
            'ebLife': 'estimated_useful_life',
            'ebDate1': 'acceptance_date',
            'ebRemarks': 'remarks',
            'ebSchoolType': 'school_type',
            'ebSchoolId': 'school_id',
            'ebSchoolName': 'office_school_name',
            'ebOccupancy': 'nature_of_occupancy',
            'ebLocation': 'location',
            'ebPropertyNo': 'property_number',
            'ebDate2': 'acquisition_date'
        };

        const activeUpdates = {};
        let hasUpdates = false;

        for (const [inputId, colKey] of Object.entries(bulkMapping)) {
            const val = document.getElementById(inputId).value;
            if (val !== "") {
                activeUpdates[colKey] = val;
                hasUpdates = true;
            }
        }

        if (!hasUpdates) {
            return Swal.fire('No Changes', 'You did not fill any fields to update.', 'info');
        }

        const previousStates = [];

        for (let i = from - 1; i < toLimit; i++) {
            const row = editAllData[i];
            const rowPreviousState = { rowId: row.dist_id, changes: [] };
            let rowChanged = false;

            for (const [col, newVal] of Object.entries(activeUpdates)) {
                const oldVal = row[col] ?? '';
                if (String(oldVal).trim() !== String(newVal).trim()) {
                    rowPreviousState.changes.push({ col: col, oldVal: oldVal });
                    row[col] = newVal;
                    rowChanged = true;
                }
            }

            if (rowChanged) {
                previousStates.push(rowPreviousState);
            }
        }

        if (previousStates.length > 0) {
            editUndoStack.push({ type: 'bulkMulti', states: previousStates });
            editRedoStack = [];
            updateEditUndoBtn();
            renderEditTable();
            Swal.fire({ icon: 'success', title: 'Bulk Edit Applied', text: `Updated ${previousStates.length} rows.`, timer: 1500, showConfirmButton: false });
        }

        closeEditBulkModal();
    }

    function editUndo() {
        if (editUndoStack.length === 0) return;
        const action = editUndoStack.pop();
        const redoStates = [];
        if (action.type === 'single') {
            const row = editAllData.find(r => r.dist_id === action.rowId);
            if (row) {
                redoStates.push({ rowId: row.dist_id, changes: [{ col: action.col, oldVal: row[action.col] }] });
                row[action.col] = action.oldVal;
            }
        } else if (action.type === 'bulkMulti') {
            action.states.forEach(state => {
                const row = editAllData.find(r => r.dist_id === state.rowId);
                if (row) {
                    const rs = { rowId: state.rowId, changes: [] };
                    state.changes.forEach(change => {
                        rs.changes.push({ col: change.col, oldVal: row[change.col] });
                        row[change.col] = change.oldVal;
                    });
                    redoStates.push(rs);
                }
            });
        }
        editRedoStack.push({ type: 'bulkMulti', states: redoStates });
        updateEditUndoBtn();
        renderEditTable();
    }

    function editRedo() {
        if (editRedoStack.length === 0) return;
        const action = editRedoStack.pop();
        const undoStates = [];
        action.states.forEach(state => {
            const row = editAllData.find(r => r.dist_id === state.rowId);
            if (row) {
                const us = { rowId: state.rowId, changes: [] };
                state.changes.forEach(change => {
                    us.changes.push({ col: change.col, oldVal: row[change.col] });
                    row[change.col] = change.oldVal;
                });
                undoStates.push(us);
            }
        });
        editUndoStack.push({ type: 'bulkMulti', states: undoStates });
        updateEditUndoBtn();
        renderEditTable();
    }

    function updateEditUndoBtn() {
        const uBtn = document.getElementById('editUndoBtn');
        const rBtn = document.getElementById('editRedoBtn');
        if (uBtn) uBtn.className = editUndoStack.length > 0 ? 'px-4 py-2 text-blue-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white transition-all active:scale-95 flex items-center gap-2' : 'px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest opacity-50 cursor-not-allowed flex items-center gap-2';
        if (rBtn) rBtn.className = editRedoStack.length > 0 ? 'px-4 py-2 text-blue-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white transition-all active:scale-95 flex items-center gap-2' : 'px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest opacity-50 cursor-not-allowed flex items-center gap-2';
    }

    function saveEditChanges() {
        try {
            if (typeof Swal === 'undefined') {
                alert('SweetAlert is not loaded. Please wait or refresh.');
                return;
            }

            const updates = [];
            editAllData.forEach(row => {
                const orig = editOriginalData.find(o => String(o.dist_id) === String(row.dist_id));
                if (!orig) return;
                
                const changes = {};
                let hasChanged = false;
                
                const keys = [
                    'classification', 'category', 'article', 'description', 'brand', 'model', 'serial_no',
                    'unit_of_measurement', 'acq_source', 'asset_cost', 'quantity', 'estimated_useful_life',
                    'property_number', 'location', 'nature_of_occupancy', 'mode_of_acquisition', 
                    'source_personnel', 'personnel_position', 'acceptance_date', 'remarks', 
                    'school_type', 'school_id', 'office_school_name', 'acquisition_date', 
                    'custodian_first_name', 'custodian_middle_name', 'custodian_last_name', 
                    'custodian_position', 'custodian_contact_number'
                ];

                keys.forEach(k => {
                    const val1 = String(row[k] ?? '').trim();
                    const val2 = String(orig[k] ?? '').trim();
                    
                    if (val1 !== val2) {
                        changes[k] = row[k];
                        hasChanged = true;
                    }
                });

                if (hasChanged) {
                    const payload = {
                        dist_id: row.dist_id,
                        src_id: row.src_id
                    };
                    
                    if (changes.hasOwnProperty('classification')) payload.classification = changes.classification;
                    if (changes.hasOwnProperty('category')) payload.category = changes.category;
                    if (changes.hasOwnProperty('article')) payload.article = changes.article;
                    if (changes.hasOwnProperty('description')) payload.description = changes.description;
                    if (changes.hasOwnProperty('brand')) payload.brand = changes.brand;
                    if (changes.hasOwnProperty('model')) payload.model = changes.model;
                    if (changes.hasOwnProperty('serial_no')) payload.serial_no = changes.serial_no;
                    if (changes.hasOwnProperty('unit_of_measurement')) payload.uom = changes.unit_of_measurement;
                    if (changes.hasOwnProperty('acq_source')) payload.acq_source = changes.acq_source;
                    if (changes.hasOwnProperty('asset_cost')) payload.cost = changes.asset_cost;
                    if (changes.hasOwnProperty('quantity')) payload.qty = changes.quantity;
                    if (changes.hasOwnProperty('estimated_useful_life')) payload.useful_life = changes.estimated_useful_life;
                    if (changes.hasOwnProperty('property_number')) payload.property_no = changes.property_number;
                    if (changes.hasOwnProperty('location')) payload.location = changes.location;
                    if (changes.hasOwnProperty('nature_of_occupancy')) payload.occupancy = changes.nature_of_occupancy;
                    if (changes.hasOwnProperty('mode_of_acquisition')) payload.mode = changes.mode_of_acquisition;
                    if (changes.hasOwnProperty('source_personnel')) payload.personnel = changes.source_personnel;
                    if (changes.hasOwnProperty('personnel_position')) payload.position = changes.personnel_position;
                    if (changes.hasOwnProperty('acceptance_date')) payload.acceptance_date = changes.acceptance_date;
                    if (changes.hasOwnProperty('remarks')) payload.remarks = changes.remarks;
                    if (changes.hasOwnProperty('school_type')) payload.school_type = changes.school_type;
                    if (changes.hasOwnProperty('school_id')) payload.school_id = changes.school_id;
                    if (changes.hasOwnProperty('office_school_name')) payload.office_school_name = changes.office_school_name;
                    if (changes.hasOwnProperty('acquisition_date')) payload.acquisition_date = changes.acquisition_date;
                    if (changes.hasOwnProperty('custodian_first_name')) payload.custodian_first_name = changes.custodian_first_name;
                    if (changes.hasOwnProperty('custodian_middle_name')) payload.custodian_middle_name = changes.custodian_middle_name;
                    if (changes.hasOwnProperty('custodian_last_name')) payload.custodian_last_name = changes.custodian_last_name;
                    if (changes.hasOwnProperty('custodian_position')) payload.custodian_position = changes.custodian_position;
                    if (changes.hasOwnProperty('custodian_contact_number')) payload.custodian_contact_number = changes.custodian_contact_number;

                    updates.push(payload);
                }
            });

            if (updates.length === 0) {
                return Swal.fire({
                    title: 'No Changes',
                    text: 'No records were modified.',
                    icon: 'info',
                    customClass: { popup: 'rounded-[2rem]' }
                });
            }

            Swal.fire({
                title: 'Save Changes?',
                text: `You are about to modify ${updates.length} records. This cannot be undone.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#c00000',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, Save Updates',
                cancelButtonText: 'Cancel',
                customClass: { popup: 'rounded-[2rem]' }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Saving...',
                        text: 'Please wait while we update the records.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch('{{ route("inventory.setup.updateBatch") }}', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json', 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ updates: updates })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Saved!',
                                text: data.message,
                                icon: 'success',
                                customClass: { popup: 'rounded-[2rem]' }
                            }).then(() => {
                                editOriginalData = JSON.parse(JSON.stringify(editAllData));
                                editUndoStack = [];
                                editRedoStack = [];
                                updateEditUndoBtn();
                                renderEditTable();
                                editFetchData();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'Failed to save',
                                icon: 'error',
                                customClass: { popup: 'rounded-[2rem]' }
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire({
                            title: 'Error',
                            text: 'Server error.',
                            icon: 'error',
                            customClass: { popup: 'rounded-[2rem]' }
                        });
                    });
                }
            });
        } catch (e) {
            console.error(e);
            alert('A JavaScript error occurred: ' + e.message);
        }
    }
</script>
```

## File: `resources/views/buildings/profile.blade.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $building->property_number }} | Building Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        deped: '#c00000',
                        deped_light: '#fef2f2',
                    }
                }
            }
        }
    </script>
    <style>
        /* === CSS Custom Properties for Light/Dark Theming === */
        :root {
            --bg-page:        #f8fafc;
            --bg-card:        #ffffff;
            --bg-secondary:   #f8fafc;
            --border-primary: #e2e8f0;
            --border-subtle:  #f1f5f9;
            --text-primary:   #0f172a;
            --text-secondary: #1e293b;
            --text-muted:     #64748b;
            --text-faint:     #94a3b8;
            --scrollbar-thumb: #cbd5e1;
            --timeline-line:  #e2e8f0;
        }
        html.dark {
            --bg-page:        #0f172a;
            --bg-card:        #1e293b;
            --bg-secondary:   #0f172a;
            --border-primary: #334155;
            --border-subtle:  #334155;
            --text-primary:   #f8fafc;
            --text-secondary: #e2e8f0;
            --text-muted:     #94a3b8;
            --text-faint:     #64748b;
            --scrollbar-thumb: #475569;
            --timeline-line:  #334155;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-page); color: var(--text-primary); }

        /* Adaptive Tailwind overrides */
        .bg-white     { background-color: var(--bg-card)      !important; }
        .bg-slate-50  { background-color: var(--bg-secondary) !important; }
        .bg-slate-100 { background-color: color-mix(in srgb, var(--bg-card) 70%, var(--border-primary)) !important; }
        .border-slate-200 { border-color: var(--border-primary) !important; }
        .border-slate-100 { border-color: var(--border-subtle)  !important; }
        .text-slate-900 { color: var(--text-primary)   !important; }
        .text-slate-800 { color: var(--text-secondary) !important; }
        .text-slate-700 { color: var(--text-muted)     !important; }
        .text-slate-600 { color: var(--text-muted)     !important; }
        .text-slate-500, .text-slate-400 { color: var(--text-faint) !important; }

        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: var(--scrollbar-thumb); border-radius: 10px; }
        [x-cloak] { display: none !important; }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        .timeline-line {
            position: absolute;
            left: 11px;
            top: 24px;
            bottom: 0;
            width: 2px;
            background: var(--timeline-line);
            z-index: 0;
        }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll p-4 lg:p-8" x-data="{ activeTab: 'specs', isEditing: false, showConfirmModal: false, showTransferModal: false, showReturnAmuModal: false, showImageFullscreen: false, showRemoveConfirmModal: false }">
        
        {{-- Global Header (Fixed/Sticky) --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 sticky top-0 z-50">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 bg-deped_light rounded-xl flex items-center justify-center border border-deped/20 shadow-sm shrink-0">
                    <svg class="w-6 h-6 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase italic">{{ $building->spec_description ?: $building->type_name }}</h1>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-200">{{ $building->property_number }}</span>
                        <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest bg-emerald-100 px-2 py-0.5 rounded-full flex items-center gap-1.5 shadow-sm">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Active / Occupied
                        </span>
                    </div>
                </div>
            </div>

            {{-- Actions Menu --}}
            <div class="flex items-center gap-3 shrink-0" x-data="{ open: false }">
                <button @click="isEditing = true" x-show="!isEditing" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                    <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Edit
                </button>
                <button @click="isEditing = false" x-show="isEditing" x-cloak class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-500 uppercase tracking-widest hover:border-slate-300 hover:text-slate-700 transition-all duration-300 shadow-sm flex items-center gap-2">
                    Cancel
                </button>
                <button @click="showConfirmModal = true" x-show="isEditing" x-cloak class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm shadow-emerald-600/30 hover:shadow-md flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    Save Changes
                </button>

                <a href="{{ route('register.building') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-grow pb-10">
            
            {{-- Left Sidebar: Building Identity Card --}}
            <aside class="lg:col-span-3 flex flex-col gap-6 z-40 relative">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col relative" x-data="{ isHoveringImage: false }">
                    <div class="aspect-square bg-slate-50 border-b border-slate-100 flex items-center justify-center p-6 relative group overflow-hidden" @mouseenter="isHoveringImage = true" @mouseleave="isHoveringImage = false">
                        <img src="{{ asset('images/building_placeholder.png') }}" alt="Building Photo" class="w-full h-full object-contain transition-transform duration-500 opacity-50 group-hover:scale-110">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4 pointer-events-none">
                            <button class="w-full py-2.5 bg-white/90 backdrop-blur-md rounded-lg text-xs font-black uppercase tracking-widest text-slate-800 hover:bg-white shadow-lg text-center cursor-pointer transition-all hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2 pointer-events-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span>Upload Photo</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-5 space-y-5">
                        <a href="{{ route('schools.profile', $building->school_id) }}" class="block bg-transparent border border-red-100 p-4 rounded-2xl shadow-sm relative overflow-hidden group hover:border-deped hover:shadow-md transition-all">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
                            <p class="text-[9px] font-black text-red-500 dark:text-red-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                School Assignment
                            </p>
                            <div class="flex items-center gap-3 pl-1">
                                <div class="w-10 h-10 rounded-full bg-white dark:bg-slate-800 border border-red-100 dark:border-red-900/50 flex items-center justify-center text-red-600 dark:text-red-400 font-black text-xs shrink-0 shadow-sm group-hover:scale-110 transition-transform">
                                    {{ strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $building->school_name), 0, 2)) ?: 'SC' }}
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase leading-tight group-hover:text-red-700 dark:group-hover:text-red-400 transition-colors">{{ $building->school_name }}</p>
                                    <p class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mt-0.5">School ID: {{ $building->school_identifier }}</p>
                                </div>
                            </div>
                        </a>

                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">District</p>
                            <div class="group flex items-start gap-2">
                                <svg class="w-4 h-4 text-deped shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <div>
                                    <p class="text-xs font-bold text-deped uppercase leading-tight">{{ $building->district_name ?? 'N/A' }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">DepEd Zamboanga City</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <div class="flex justify-between items-end mb-1.5">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Building Lifespan</p>
                                <p class="text-[10px] font-black text-slate-700">60%</p>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-red-600 to-amber-400 h-full rounded-full" style="width: 60%"></div>
                            </div>
                            <p class="text-[8px] font-bold text-slate-400 uppercase mt-1.5 text-right">Approx. 15 of 25 Years Remaining</p>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main Content Area --}}
            <div class="lg:col-span-9 flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                
                {{-- Tabs Header --}}
                <div class="flex border-b border-slate-200 bg-slate-50/50 px-2 pt-2">
                    <button @click="activeTab = 'specs'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'specs', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'specs'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Infrastructure Details
                    </button>
                    <button @click="activeTab = 'history'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'history', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'history'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Lifecycle & History
                    </button>
                    <button @click="activeTab = 'docs'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'docs', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'docs'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Blueprint & Documents
                    </button>
                </div>

                {{-- Tab Contents --}}
                <div class="p-6 lg:p-8 flex-grow overflow-y-auto custom-scroll bg-white">
                    
                    {{-- TAB 1: Infrastructure Details --}}
                    <form id="update-building-form" action="{{ route('buildings.update', $building->id) }}" method="POST" x-show="activeTab === 'specs'" class="animate-fade space-y-8">
                        @csrf
                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Technical Specifications
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-y-6 gap-x-8 bg-slate-50 rounded-xl p-6 border border-slate-100">
                                {{-- Searchable Classification --}}
                                <div x-data="{ open: false, search: '{{ $building->classification_name }}', options: @js($classifications) }" class="relative" @click.away="open = false">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Classification</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $building->classification_name }}</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="text" x-model="search" name="classification" @focus="open = true" @input="open = true" placeholder="Search or type new..." class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                        <div x-show="open" x-cloak class="absolute z-50 w-full mt-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto custom-scroll p-1">
                                            <template x-for="opt in options.filter(o => o.name.toLowerCase().includes(search.toLowerCase()))" :key="opt.id">
                                                <div @click="search = opt.name; open = false;" class="px-4 py-2.5 text-[10px] font-black text-slate-300 uppercase hover:bg-slate-700 hover:text-white rounded-lg cursor-pointer transition-colors" x-text="opt.name"></div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Searchable Type --}}
                                <div x-data="{ open: false, search: '{{ $building->type_name }}', options: @js($types) }" class="relative" @click.away="open = false">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Type / Structure</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $building->type_name }}</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="text" x-model="search" name="type_name" @focus="open = true" @input="open = true" placeholder="Search or type new..." class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                        <div x-show="open" x-cloak class="absolute z-50 w-full mt-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto custom-scroll p-1">
                                            <template x-for="opt in options.filter(o => o.name.toLowerCase().includes(search.toLowerCase()))" :key="opt.id">
                                                <div @click="search = opt.name; open = false;" class="px-4 py-2.5 text-[10px] font-black text-slate-300 uppercase hover:bg-slate-700 hover:text-white rounded-lg cursor-pointer transition-colors" x-text="opt.name"></div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Occupancy Nature Select --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Occupancy Nature</p>
                                    <p x-show="!isEditing" class="text-xs font-black text-deped mt-1 uppercase px-1">{{ $building->occupancy_nature ?: 'NOT SPECIFIED' }}</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <select name="occupancy_nature" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                            <option value="OWNED" {{ $building->occupancy_nature === 'OWNED' ? 'selected' : '' }}>OWNED</option>
                                            <option value="DONATED" {{ $building->occupancy_nature === 'DONATED' ? 'selected' : '' }}>DONATED</option>
                                            <option value="USUFRUCT" {{ $building->occupancy_nature === 'USUFRUCT' ? 'selected' : '' }}>USUFRUCT</option>
                                            <option value="LEASED" {{ $building->occupancy_nature === 'LEASED' ? 'selected' : '' }}>LEASED</option>
                                            <option value="NOT SPECIFIED" {{ !$building->occupancy_nature ? 'selected' : '' }}>NOT SPECIFIED</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Storeys --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Number of Storeys</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $building->storeys }} Storey(s)</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="number" name="storeys" min="1" value="{{ $building->storeys }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                    </div>
                                </div>

                                {{-- Classrooms --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Total Classrooms</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $building->classrooms }} Classroom(s)</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="number" name="classrooms" min="0" value="{{ $building->classrooms }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                    </div>
                                </div>

                                {{-- Property Number --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Property Number</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $building->property_number }}</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="text" name="property_number" value="{{ $building->property_number }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Procurement & Construction
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8 bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                                {{-- Date Constructed --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Date Constructed</p>
                                    <p x-show="!isEditing" class="text-sm font-black text-slate-800 mt-1 uppercase px-1">{{ $building->date_constructed ? \Carbon\Carbon::parse($building->date_constructed)->format('F d, Y') : 'N/A' }}</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="date" name="date_constructed" value="{{ $building->date_constructed ? \Carbon\Carbon::parse($building->date_constructed)->format('Y-m-d') : '' }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                    </div>
                                </div>

                                {{-- Acquisition Cost --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Acquisition Cost</p>
                                    <p x-show="!isEditing" class="text-lg font-black text-emerald-600 mt-0.5 tracking-tighter px-1">₱ {{ number_format($building->acquisition_cost, 2) }}</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="number" name="acquisition_cost" step="0.01" min="0" value="{{ $building->acquisition_cost }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                    </div>
                                </div>

                                {{-- Useful Life --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Estimated Useful Life</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $building->estimated_useful_life }} Year(s)</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="number" name="estimated_useful_life" min="0" value="{{ $building->estimated_useful_life }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                    </div>
                                </div>

                                {{-- Appraised Value --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Appraised Value</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">₱ {{ number_format($building->appraised_value, 2) }}</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="number" name="appraised_value" step="0.01" min="0" value="{{ $building->appraised_value }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Remarks / Notes
                            </h3>
                            <div class="bg-slate-50 rounded-xl p-6 border border-slate-100">
                                <p x-show="!isEditing" class="text-xs font-medium text-slate-600 leading-relaxed italic px-1">"{{ $building->remarks ?: 'No remarks recorded.' }}"</p>
                                <div x-show="isEditing" class="relative group" x-cloak>
                                    <textarea name="remarks" rows="3" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">{{ $building->remarks }}</textarea>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- TAB 2: Lifecycle & History --}}
                    <div x-show="activeTab === 'history'" class="animate-fade relative" x-cloak>
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Facility Timeline
                            </h3>
                        </div>

                        <div class="relative pl-3 max-w-3xl">
                            <div class="timeline-line"></div>
                            <div class="space-y-6">
                                @foreach($timeline as $event)
                                <div class="relative pl-8 group">
                                    <div class="absolute left-[-2px] top-1 w-6 h-6 rounded-full bg-white border-2 border-slate-300 flex items-center justify-center shadow-sm z-10">
                                        <div class="w-2 h-2 bg-slate-400 rounded-full"></div>
                                    </div>
                                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] font-black text-white uppercase tracking-widest bg-slate-500 px-2 py-0.5 rounded">{{ $event['type'] }}</span>
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $event['date'] }}</span>
                                            </div>
                                        </div>
                                        <p class="text-sm font-bold text-slate-800 uppercase mt-2">{{ $event['description'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: Blueprint & Documents --}}
                    <div x-show="activeTab === 'docs'" class="animate-fade space-y-6" x-cloak>
                        <div class="flex flex-col items-center justify-center py-12 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50">
                            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center text-slate-300 shadow-sm mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic mb-1">No blueprints or warranty certs uploaded</h3>
                            <button class="mt-4 px-6 py-2 bg-white border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-600 hover:text-deped hover:border-deped transition-all">Upload Document</button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
        
        {{-- Confirmation Modal --}}
        <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center">
            <div x-show="showConfirmModal" x-transition.opacity class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showConfirmModal = false"></div>
            <div x-show="showConfirmModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 relative z-10 border border-slate-100 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-amber-100 text-amber-500 rounded-full flex items-center justify-center mb-5 ring-8 ring-amber-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight mb-2">Save Changes?</h3>
                <p class="text-xs font-bold text-slate-500 mb-8 leading-relaxed">Confirm to update the building records. This action will be logged.</p>
                <div class="flex items-center gap-3 w-full">
                    <button @click="showConfirmModal = false" class="flex-1 py-3.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-widest transition-colors">Cancel</button>
                    <button @click="document.getElementById('update-building-form').submit();" class="flex-1 py-3.5 px-4 bg-deped hover:bg-red-800 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-deped/30 transition-all active:scale-95">Yes, Save</button>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
```

## File: `resources/views/partials/building-edit-step.blade.php`

```html
<div id="stepBuildingEdit" class="step-content">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-6">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase text-emerald-600">Infrastructure <span class="text-slate-900">Management</span></h2>
            <p class="text-slate-400 text-sm font-bold uppercase mt-1 tracking-widest leading-tight">Bulk update building and facility records</p>
        </div>
        <div class="flex items-center gap-4">
            <button onclick="toggleBldgFilters()" id="toggleBldgFilterBtn" class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-500 bg-white border border-slate-100 hover:border-emerald-600 transition-all flex items-center gap-2 active:scale-95 shadow-sm italic">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                Hide Filters
            </button>
        </div>
    </div>

    <!-- Filter Configuration -->
    <div id="bldgFilterSection" class="bg-white rounded-[2.5rem] shadow-lg border border-slate-100 p-8 mb-8 relative z-50 transition-all duration-300 origin-top">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 relative z-10">
            {{-- Row 1 --}}
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Classification</label>
                <select id="bldgFilterClass" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">All Classifications</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Office/School Type</label>
                <select id="bldgFilterCat" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">All Types</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Article</label>
                <select id="bldgFilterArticle" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">All Articles</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Cost Sorting</label>
                <select id="bldgFilterSort" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">Default (ID)</option>
                    <option value="low_to_high">Acquisition Cost: Low to High</option>
                    <option value="high_to_low">Acquisition Cost: High to Low</option>
                </select>
            </div>

            {{-- Row 2 --}}
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">School Name</label>
                <select id="bldgFilterSchool" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">All Schools</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Nature of Occupancy</label>
                <select id="bldgFilterOccupancy" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">All Status</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Date Constructed</label>
                <input type="date" id="bldgFilterDate" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Data Integrity (Empty Fields)</label>
                <select id="bldgFilterIntegrity" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">Show All Records</option>
                    <option value="office_type">Missing Office Type</option>
                    <option value="school_id">Missing School ID</option>
                    <option value="office_name">Missing School Name</option>
                    <option value="address">Missing Address</option>
                    <option value="article">Missing Article</option>
                    <option value="property_number">Missing Property No.</option>
                    <option value="acquisition_cost">Missing Cost</option>
                </select>
            </div>
        </div>

        <div class="mt-8 pt-8 border-t border-slate-50 flex justify-end gap-4">
            <button onclick="clearBldgFilters()" class="px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-all active:scale-95 italic">
                Clear Filters
            </button>
            <button onclick="bldgFetchData()" class="px-10 py-3 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-slate-200 hover:bg-emerald-600 transition-all active:scale-95 italic">
                Apply Configuration
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div id="bldgAssetTableCard" class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 flex flex-col min-h-0 overflow-hidden">
        <div class="px-8 py-5 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 bg-slate-800 rounded-xl flex items-center justify-center text-white text-xs font-black shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21" />
                    </svg>
                </div>
                <div class="flex bg-slate-100 rounded-xl p-1 gap-1">
                    <button id="tabEditBldgIdentity" onclick="switchEditBldgTab('identity')"
                        class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg bg-emerald-600 text-white shadow-sm transition-all">
                        Identity & Structure
                    </button>
                    <button id="tabEditBldgDetails" onclick="switchEditBldgTab('details')"
                        class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg text-slate-900 hover:text-slate-900 transition-all">
                        Registry & Value
                    </button>
                </div>
                <span id="editBldgTabLabel" class="hidden md:block text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Identity & Structure</span>
            </div>

            <div id="bldgAssetToolbar" class="flex items-center gap-3">
                <div class="flex items-center bg-slate-100 rounded-2xl p-1.5 mr-2">
                    <button onclick="bldgUndo()" id="bldgUndoBtn" class="px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white hover:text-emerald-600 transition-all active:scale-95 flex items-center gap-2 opacity-50 cursor-not-allowed group">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        Undo
                    </button>
                    <div class="w-[1px] bg-slate-200 h-3 self-center my-auto"></div>
                    <button onclick="bldgRedo()" id="bldgRedoBtn" class="px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white hover:text-emerald-600 transition-all active:scale-95 flex items-center gap-2 opacity-50 cursor-not-allowed group">
                        Redo
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"/></svg>
                    </button>
                </div>

                <button onclick="openBldgBulkModal()" class="px-5 py-2.5 bg-emerald-50 text-emerald-600 rounded-xl text-[9px] font-black uppercase tracking-widest flex items-center gap-2 shadow-sm hover:bg-emerald-100 transition-all active:scale-95 italic border border-emerald-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16.862 4.487l1.688-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                    Bulk Edit
                </button>

                <button onclick="saveBldgChanges()" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[9px] font-black uppercase tracking-widest flex items-center gap-2 shadow-lg hover:bg-emerald-600 transition-all active:scale-95 italic">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
        </div>

        {{-- ── Tab 1: Identity Table ── --}}
        <div id="panelEditBldgIdentity" class="flex-grow flex flex-col min-h-0">
            <div id="bldgIdentityScroll" class="xls-scroll-wrap custom-scroll overflow-x-auto overflow-y-auto transition-all duration-300" style="max-height: calc(100vh - 450px);">
                <table class="w-full border-collapse" style="min-width:1400px;">
                    <thead class="sticky top-0 bg-slate-50 z-20 shadow-sm">
                        <tr>
                            <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                            <th class="xls-th col-context" style="min-width:90px">REGION</th>
                            <th class="xls-th col-context" style="min-width:200px">DIVISION</th>
                            <th class="xls-th col-context" style="min-width:140px">OFFICE TYPE</th>
                            <th class="xls-th col-identity" style="min-width:100px">SCHOOL ID</th>
                            <th class="xls-th col-identity" style="min-width:210px">SCHOOL NAME</th>
                            <th class="xls-th col-context" style="min-width:180px">ADDRESS</th>
                            <th class="xls-th col-personnel text-right" style="min-width:80px">STOREYS</th>
                            <th class="xls-th col-personnel text-right" style="min-width:100px">CLASSROOMS</th>
                        </tr>
                    </thead>
                    <tbody id="bldgIdentityTbody"></tbody>
                </table>
            </div>
        </div>

        {{-- ── Tab 2: Details Table ── --}}
        <div id="panelEditBldgDetails" class="hidden flex-grow flex flex-col min-h-0">
            <div id="bldgDetailsScroll" class="xls-scroll-wrap custom-scroll overflow-x-auto overflow-y-auto transition-all duration-300" style="max-height: calc(100vh - 450px);">
                <table class="w-full border-collapse" style="min-width:2000px;">
                    <thead class="sticky top-0 bg-slate-50 z-20 shadow-sm">
                        <tr>
                            <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                            <th class="xls-th col-personnel" style="min-width:140px">ARTICLE</th>
                            <th class="xls-th col-personnel" style="min-width:200px">DESCRIPTION</th>
                            <th class="xls-th col-identity" style="min-width:140px">CLASSIFICATION</th>
                            <th class="xls-th col-context" style="min-width:160px">OCCUPANCY NATURE</th>
                            <th class="xls-th col-context" style="min-width:160px">LOCATION</th>
                            <th class="xls-th col-temporal" style="min-width:140px">DATE CONSTRUCTED</th>
                            <th class="xls-th col-temporal" style="min-width:140px">ACQUISITION DATE</th>
                            <th class="xls-th col-identity" style="min-width:150px">PROPERTY NO.</th>
                            <th class="xls-th col-financial text-right" style="min-width:140px">ACQUISITION COST (₱)</th>
                            <th class="xls-th col-temporal text-right" style="min-width:120px">USEFUL LIFE (YRS)</th>
                            <th class="xls-th col-financial text-right" style="min-width:140px">APPRAISED VALUE (₱)</th>
                            <th class="xls-th col-temporal" style="min-width:140px">APPRAISAL DATE</th>
                            <th class="xls-th col-status" style="min-width:200px">REMARKS</th>
                        </tr>
                    </thead>
                    <tbody id="bldgDetailsTbody"></tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-6">
                <p id="bldgRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                <div id="bldgPaginationControls" class="flex items-center gap-3 border-l border-slate-200 pl-6">
                    <button onclick="bldgPrevPage()" id="bldgPrevBtn" class="pg-btn">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                        Prev
                    </button>
                    <div class="flex items-center gap-2 px-4 py-2 bg-slate-900/40 dark:bg-slate-800/60 rounded-xl border border-slate-200/10 backdrop-blur-md">
                        <span id="bldgCurrentPageNum" class="text-[10px] font-black text-slate-700 dark:text-blue-400">1</span>
                        <span class="text-[10px] font-bold text-slate-400">/</span>
                        <span id="bldgTotalPages" class="text-[10px] font-black text-slate-400">1</span>
                    </div>
                    <button onclick="bldgNextPage()" id="bldgNextBtn" class="pg-btn">
                        Next
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> <!-- End stepBuildingEdit -->

<!-- Bulk Edit Modal -->
<div id="bldgBulkModal" class="fixed inset-0 z-[150] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeBldgBulkModal()"></div>
    <div class="bg-white dark:bg-[#141f33] border border-slate-200 dark:border-slate-800 rounded-[2rem] shadow-2xl w-[90vw] max-w-5xl max-h-[90vh] flex flex-col relative z-10 transform scale-95 transition-transform duration-300">
        
        {{-- Header --}}
        <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight italic text-emerald-600">Bulk Edit Rows</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Update specific columns for a range of rows</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-[#0a101d] px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">From Row #</label>
                    <input type="number" id="bldgBulkFrom" value="1" min="1" class="w-16 bg-transparent text-center font-black text-slate-800 dark:text-white outline-none">
                </div>
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-[#0a101d] px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">To Row #</label>
                    <input type="number" id="bldgBulkTo" value="1" min="1" class="w-20 bg-transparent text-center font-black text-slate-800 dark:text-white outline-none">
                </div>
                <button onclick="closeBldgBulkModal()" class="px-5 py-3 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">Cancel</button>
                <button onclick="applyBldgBulk()" class="px-6 py-3 rounded-xl text-sm font-black text-white bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-500/30 transition-all">Apply Bulk Edit</button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-8 overflow-y-auto custom-scroll flex-1 space-y-10">
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-emerald-500/20 text-emerald-600 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">1</div>
                    <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">Building Identity</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Office/School Type</label>
                        <input type="text" list="dl-bldg-type" id="bebOfficeType" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl bg-transparent" placeholder="-- Ignore --">
                    </div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">School ID</label><input type="text" id="bebSchoolId" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">School Name</label><input type="text" id="bebSchoolName" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore" oninput="
                        if(this.value.trim() && typeof detectItemSchoolType === 'function'){
                            const t = detectItemSchoolType(this.value);
                            if(t) document.getElementById('bebOfficeType').value = t;
                            if(typeof cleanSchoolNameForLocation === 'function') document.getElementById('bebLocation').value = cleanSchoolNameForLocation(this.value);
                        }
                    "></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Address</label><input type="text" id="bebAddress" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Storeys</label><input type="number" id="bebStoreys" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="1"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Classrooms</label><input type="number" id="bebClassrooms" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="1"></div>
                </div>
            </div>
            <div class="border-t border-slate-100 dark:border-slate-800"></div>
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-emerald-500/20 text-emerald-600 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">2</div>
                    <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">Building Details</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Article</label><input type="text" id="bebArticle" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Description</label><input type="text" id="bebDescription" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Classification</label><input type="text" id="bebClassification" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Nature of Occupancy</label><input type="text" id="bebOccupancy" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Location</label><input type="text" id="bebLocation" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Property Number</label><input type="text" id="bebPropertyNo" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Acquisition Cost (₱)</label><input type="number" id="bebAcqCost" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="0.01"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Useful Life (yrs)</label><input type="number" id="bebLife" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="1"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Date Constructed</label><input type="date" id="bebDateConstructed" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Acquisition Date</label><input type="date" id="bebAcqDate" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Appraised Value (₱)</label><input type="number" id="bebAppraisedValue" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="0.01"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Appraisal Date</label><input type="date" id="bebAppraisalDate" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                    <div class="relative col-span-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Remarks</label>
                        <select id="bebRemarks" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl bg-transparent">
                            <option value="">-- Ignore --</option>
                            <option value="Good Condition">Good Condition</option>
                            <option value="Needs Repair">Needs Repair</option>
                            <option value="Not Useable">Not Useable</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .update-badge {
        position: absolute; top: 3px; left: 3px; font-size: 8px; font-weight: 900; background: #3b82f6; color: white; padding: 1px 4px; border-radius: 4px; text-transform: uppercase; pointer-events: none; z-index: 10; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2); letter-spacing: 0.5px;
    }
    .edit-input {
        width: 100%; padding: 11px 14px; font-size: 11.5px; font-weight: 600; color: #334155; background: rgba(59, 130, 246, 0.03); border: 1px solid transparent; outline: none; box-sizing: border-box; line-height: 1.4; transition: all 0.2s; height: 100%; min-height: 40px;
    }
    .edit-input:focus {
        background: rgba(59, 130, 246, 0.05); border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }
    .edit-readonly {
        background: rgba(0,0,0,0.02) !important; color: #94a3b8 !important; cursor: not-allowed;
    }
</style>

<script>
    let bldgAllData = [];
    let bldgOriginalData = [];
    let bldgUndoStack = [];
    let bldgRedoStack = [];
    let bldgPageNum = 1;
    const bldgRowsPerPage = 50;

    function switchEditBldgTab(tab) {
        const identPanel = document.getElementById('panelEditBldgIdentity');
        const detPanel   = document.getElementById('panelEditBldgDetails');
        const tabIdent   = document.getElementById('tabEditBldgIdentity');
        const tabDet     = document.getElementById('tabEditBldgDetails');
        const label      = document.getElementById('editBldgTabLabel');
        const ON  = 'px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg bg-emerald-600 text-white shadow-sm transition-all';
        const OFF = 'px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg text-slate-900 hover:text-slate-900 transition-all';
        if (tab === 'identity') {
            identPanel.classList.remove('hidden');
            detPanel.classList.add('hidden');
            tabIdent.className = ON; tabDet.className = OFF;
            label.textContent = 'Identity & Structure';
        } else {
            identPanel.classList.add('hidden');
            detPanel.classList.remove('hidden');
            tabIdent.className = OFF; tabDet.className = ON;
            label.textContent = 'Registry & Value';
        }
    }

    function toggleBldgFilters() {
        const section = document.getElementById('bldgFilterSection');
        const btn = document.getElementById('toggleBldgFilterBtn');
        if (section.classList.contains('hidden')) {
            section.classList.remove('hidden');
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Hide Filters`;
        } else {
            section.classList.add('hidden');
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Show Filters`;
        }
    }

    function initBldgEdit() {
        fetch('{{ route("api.buildings.filters") }}')
            .then(res => res.json())
            .then(data => {
                populateBldgSelect('bldgFilterClass', data.classifications || []);
                populateBldgSelect('bldgFilterCat', data.office_types || []);
                populateBldgSelect('bldgFilterArticle', data.articles || []);
                populateBldgSelect('bldgFilterSchool', data.schools || []);
                populateBldgSelect('bldgFilterOccupancy', data.occupancies || []);
            });
    }

    function populateBldgSelect(id, options) {
        const sel = document.getElementById(id);
        if (!sel) return;
        const first = sel.options[0];
        sel.innerHTML = '';
        sel.appendChild(first);
        options.forEach(opt => {
            const el = document.createElement('option');
            el.value = opt; el.textContent = opt;
            sel.appendChild(el);
        });
    }

    function clearBldgFilters() {
        ['bldgFilterClass', 'bldgFilterCat', 'bldgFilterArticle', 'bldgFilterSort', 'bldgFilterSchool', 'bldgFilterOccupancy', 'bldgFilterDate', 'bldgFilterIntegrity'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        bldgFetchData();
    }

    function bldgFetchData() {
        const filters = {
            classification: (document.getElementById('bldgFilterClass')||{}).value || '',
            office_type:    (document.getElementById('bldgFilterCat')||{}).value || '',
            article:        (document.getElementById('bldgFilterArticle')||{}).value || '',
            school:         (document.getElementById('bldgFilterSchool')||{}).value || '',
            occupancy:      (document.getElementById('bldgFilterOccupancy')||{}).value || '',
            date:           (document.getElementById('bldgFilterDate')||{}).value || '',
            emptyCol:       (document.getElementById('bldgFilterIntegrity')||{}).value || '',
            sortCost:       (document.getElementById('bldgFilterSort')||{}).value || '',
        };

        const loader = document.getElementById('bldgAssetLoading');
        if (loader) loader.classList.remove('hidden');

        fetch('{{ route("api.buildings.edit_preview") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ filters: filters })
        })
        .then(res => res.json())
        .then(data => {
            bldgAllData = data.rows || [];
            bldgOriginalData = JSON.parse(JSON.stringify(bldgAllData));
            bldgPageNum = 1;
            bldgUndoStack = [];
            bldgRedoStack = [];
            updateBldgUndoBtn();
            renderBldgTable();
            if (bldgAllData.length === 0) {
                Swal.fire({ title: 'No Buildings Found', text: 'No records match your current filter configuration.', icon: 'info', customClass: { popup: 'rounded-[2rem]' } });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({ title: 'Error', text: 'Failed to load building data.', icon: 'error', customClass: { popup: 'rounded-[2rem]' } });
        })
        .finally(() => {
            if (loader) loader.classList.add('hidden');
        });
    }

    function renderBldgTable() {
        const tbodyIdent = document.getElementById('bldgIdentityTbody');
        const tbodyDet   = document.getElementById('bldgDetailsTbody');
        if (!tbodyIdent || !tbodyDet) return;
        tbodyIdent.innerHTML = ''; tbodyDet.innerHTML = '';

        if (bldgAllData.length === 0) {
            document.getElementById('bldgRowCountLabel').textContent = "0 Rows";
            document.getElementById('bldgCurrentPageNum').textContent = 1;
            document.getElementById('bldgTotalPages').textContent = 1;
            return;
        }

        const start = (bldgPageNum - 1) * bldgRowsPerPage;
        const pageData = bldgAllData.slice(start, start + bldgRowsPerPage);

        pageData.forEach((row, idx) => {
            const displayNum = start + idx + 1;
            const orig = bldgOriginalData.find(o => String(o.id) === String(row.id)) || {};

            const renderCell = (col, val, readonly, list = '') => {
                const v1 = String(val ?? '').trim();
                const v2 = String(orig[col] ?? '').trim();
                const changed = v1 !== v2;
                const badge = changed ? `<span class="update-badge">Update</span>` : '';
                const safe = (val ?? '').toString().replace(/"/g, '&quot;');
                if (readonly) return `<td class="xls-td p-0 relative"><input type="text" class="xls-input edit-readonly w-full h-full" value="${safe}" readonly tabindex="-1">${badge}</td>`;
                if (col === 'remarks') return `<td class="xls-td p-0 relative"><select data-id="${row.id}" data-col="${col}" onchange="syncBldgCell(this)" class="xls-input w-full h-full bg-transparent"><option value="Good Condition" ${val==='Good Condition'?'selected':''}>Good Condition</option><option value="Needs Repair" ${val==='Needs Repair'?'selected':''}>Needs Repair</option><option value="Not Useable" ${val==='Not Useable'?'selected':''}>Not Useable</option></select>${badge}</td>`;
                return `<td class="xls-td p-0 relative"><input type="text" data-id="${row.id}" data-col="${col}" value="${safe}" onchange="syncBldgCell(this)" autocomplete="off" ${list ? `list="${list}"` : ''} class="xls-input w-full h-full bg-transparent">${badge}</td>`;
            };

            const trI = document.createElement('tr');
            trI.className = 'xls-row group border-b border-slate-100';
            trI.innerHTML = `
                <td class="xls-td text-center sticky left-0 w-10 bg-white z-10"><span class="text-[10px] font-black text-slate-300">${displayNum}</span></td>
                <td class="xls-td col-context p-0 relative"><span class="xls-const w-full h-full flex items-center px-4">Region IX</span></td>
                <td class="xls-td col-context p-0 relative"><span class="xls-const w-full h-full flex items-center px-4">Division of Zamboanga City</span></td>
                ${renderCell('office_type', row.office_type, false, 'dl-bldg-type')}
                ${renderCell('school_id', row.school_id, false)}
                ${renderCell('office_name', row.office_name, false)}
                ${renderCell('address', row.address, false)}
                ${renderCell('storeys', row.storeys, false)}
                ${renderCell('classrooms', row.classrooms, false)}
            `;
            tbodyIdent.appendChild(trI);

            const trD = document.createElement('tr');
            trD.className = 'xls-row group border-b border-slate-100';
            trD.innerHTML = `
                <td class="xls-td text-center sticky left-0 w-10 bg-white z-10"><span class="text-[10px] font-black text-slate-300">${displayNum}</span></td>
                ${renderCell('article', row.article, false)}
                ${renderCell('description', row.description, false)}
                ${renderCell('classification', row.classification, false, 'dl-bldg-class')}
                ${renderCell('occupancy_nature', row.occupancy_nature, false, 'dl-bldg-occupancy')}
                ${renderCell('location', row.location, false)}
                ${renderCell('date_constructed', row.date_constructed, false)}
                ${renderCell('acquisition_date', row.acquisition_date, false)}
                ${renderCell('property_number', row.property_number, false)}
                ${renderCell('acquisition_cost', row.acquisition_cost, false)}
                ${renderCell('estimated_useful_life', row.estimated_useful_life, false)}
                ${renderCell('appraised_value', row.appraised_value, false)}
                ${renderCell('appraisal_date', row.appraisal_date, false)}
                ${renderCell('remarks', row.remarks, false)}
            `;
            tbodyDet.appendChild(trD);
        });

        const totalPages = Math.ceil(bldgAllData.length / bldgRowsPerPage) || 1;
        document.getElementById('bldgRowCountLabel').textContent = bldgAllData.length + " Rows (paired)";
        document.getElementById('bldgCurrentPageNum').textContent = bldgPageNum;
        document.getElementById('bldgTotalPages').textContent = totalPages;
        document.getElementById('bldgPrevBtn').disabled = bldgPageNum === 1;
        document.getElementById('bldgNextBtn').disabled = bldgPageNum === totalPages;
    }

    function syncBldgCell(input) {
        const id = input.getAttribute('data-id');
        const col = input.getAttribute('data-col');
        const newVal = input.value;
        const row = bldgAllData.find(r => String(r.id) === String(id));
        if (row) {
            const oldVal = row[col] ?? '';
            if (String(oldVal).trim() !== String(newVal).trim()) {
                bldgUndoStack.push({ type: 'single', rowId: id, col: col, oldVal: oldVal, newVal: newVal });
                row[col] = newVal;

                // Targeted DOM Update Function (No focus loss)
                const updateDOM = (column, newValue) => {
                    const el = document.querySelector(`input[data-id="${id}"][data-col="${column}"]`);
                    if (el && document.activeElement !== el) el.value = newValue;
                };

                if (col === 'office_name') {
                    const trimmed = newVal.trim();
                    if (trimmed !== '') {
                        if (typeof detectItemSchoolType === 'function') {
                            const detected = detectItemSchoolType(trimmed);
                            if (detected) { row['office_type'] = detected; updateDOM('office_type', detected); }
                        }
                        if (typeof cleanSchoolNameForLocation === 'function') {
                            const loc = cleanSchoolNameForLocation(trimmed);
                            row['location'] = loc; updateDOM('location', loc);
                        }
                    }
                }
                
                bldgRedoStack = [];
                updateBldgUndoBtn();
                // We update badges visually without full re-render
                const td = input.closest('td');
                if (td && !td.querySelector('.update-badge')) {
                    const badge = document.createElement('span'); badge.className = 'update-badge'; badge.textContent = 'Update';
                    td.appendChild(badge);
                }
            }
        }
    }

    function bldgPrevPage() { if (bldgPageNum > 1) { bldgPageNum--; renderBldgTable(); } }
    function bldgNextPage() { const t = Math.ceil(bldgAllData.length/bldgRowsPerPage); if (bldgPageNum < t) { bldgPageNum++; renderBldgTable(); } }

    function openBldgBulkModal() {
        if(bldgAllData.length === 0) return Swal.fire('No Data', 'Load assets first.', 'info');
        const m = document.getElementById('bldgBulkModal');
        m.classList.remove('hidden');
        document.querySelectorAll('#bldgBulkModal input:not([id="bldgBulkFrom"]):not([id="bldgBulkTo"])').forEach(i => i.value = '');
        const br = document.getElementById('bebRemarks'); if(br) br.value = '';

        const maxRows = bldgAllData.length;
        const fromInput = document.getElementById('bldgBulkFrom');
        const toInput   = document.getElementById('bldgBulkTo');
        fromInput.value = 1;
        fromInput.max   = maxRows;
        toInput.value   = maxRows;
        toInput.max     = maxRows;

        setTimeout(() => {
            m.classList.remove('opacity-0');
            m.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }
    
    function closeBldgBulkModal() {
        const m = document.getElementById('bldgBulkModal');
        m.classList.add('opacity-0');
        m.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => m.classList.add('hidden'), 300);
    }

    function applyBldgBulk() {
        const from = parseInt(document.getElementById('bldgBulkFrom').value);
        const to = parseInt(document.getElementById('bldgBulkTo').value);
        const maxRows = bldgAllData.length;

        if (isNaN(from) || isNaN(to) || from < 1 || to < from || to > maxRows) return Swal.fire('Error', 'Invalid Range', 'error');

        const bulkMapping = {
            'bebOfficeType': 'office_type', 'bebSchoolId': 'school_id', 'bebSchoolName': 'office_name',
            'bebAddress': 'address', 'bebStoreys': 'storeys', 'bebClassrooms': 'classrooms',
            'bebArticle': 'article', 'bebDescription': 'description', 'bebClassification': 'classification',
            'bebOccupancy': 'occupancy_nature', 'bebLocation': 'location', 'bebDateConstructed': 'date_constructed',
            'bebAcqDate': 'acquisition_date', 'bebPropertyNo': 'property_number', 'bebAcqCost': 'acquisition_cost',
            'bebLife': 'estimated_useful_life', 'bebAppraisedValue': 'appraised_value', 'bebAppraisalDate': 'appraisal_date', 'bebRemarks': 'remarks'
        };

        const updates = {}; let has = false;
        for (const [id, col] of Object.entries(bulkMapping)) {
            const v = document.getElementById(id).value;
            if (v !== "") { updates[col] = v; has = true; }
        }
        if (!has) return closeBldgBulkModal();

        const previousStates = [];
        for (let i = from - 1; i < to; i++) {
            const row = bldgAllData[i];
            const rowPrev = { rowId: row.id, changes: [] };
            let changed = false;
            for (const [col, newVal] of Object.entries(updates)) {
                if (String(row[col] ?? '').trim() !== String(newVal).trim()) {
                    rowPrev.changes.push({ col: col, oldVal: row[col] });
                    row[col] = newVal; changed = true;
                }
            }
            if (changed) previousStates.push(rowPrev);
        }

        if (previousStates.length > 0) {
            bldgUndoStack.push({ type: 'bulkMulti', states: previousStates });
            bldgRedoStack = []; updateBldgUndoBtn(); renderBldgTable();
        }
        closeBldgBulkModal();
    }

    function bldgUndo() {
        if (bldgUndoStack.length === 0) return;
        const action = bldgUndoStack.pop();
        const redoStates = [];
        if (action.type === 'single') {
            const row = bldgAllData.find(r => String(r.id) === String(action.rowId));
            if (row) {
                redoStates.push({ rowId: row.id, changes: [{ col: action.col, oldVal: row[action.col] }] });
                row[action.col] = action.oldVal;
            }
        } else if (action.type === 'bulkMulti') {
            action.states.forEach(state => {
                const row = bldgAllData.find(r => String(r.id) === String(state.rowId));
                if (row) {
                    const rs = { rowId: state.rowId, changes: [] };
                    state.changes.forEach(change => {
                        rs.changes.push({ col: change.col, oldVal: row[change.col] });
                        row[change.col] = change.oldVal;
                    });
                    redoStates.push(rs);
                }
            });
        }
        bldgRedoStack.push({ type: 'bulkMulti', states: redoStates });
        updateBldgUndoBtn(); renderBldgTable();
    }

    function bldgRedo() {
        if (bldgRedoStack.length === 0) return;
        const action = bldgRedoStack.pop();
        const undoStates = [];
        action.states.forEach(state => {
            const row = bldgAllData.find(r => String(r.id) === String(state.rowId));
            if (row) {
                const us = { rowId: state.rowId, changes: [] };
                state.changes.forEach(change => {
                    us.changes.push({ col: change.col, oldVal: row[change.col] });
                    row[change.col] = change.oldVal;
                });
                undoStates.push(us);
            }
        });
        bldgUndoStack.push({ type: 'bulkMulti', states: undoStates });
        updateBldgUndoBtn(); renderBldgTable();
    }

    function updateBldgUndoBtn() {
        const uBtn = document.getElementById('bldgUndoBtn'), rBtn = document.getElementById('bldgRedoBtn');
        if (uBtn) uBtn.className = bldgUndoStack.length > 0 ? 'px-4 py-2 text-emerald-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white transition-all active:scale-95 flex items-center gap-2' : 'px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest opacity-50 cursor-not-allowed flex items-center gap-2';
        if (rBtn) rBtn.className = bldgRedoStack.length > 0 ? 'px-4 py-2 text-emerald-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white transition-all active:scale-95 flex items-center gap-2' : 'px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest opacity-50 cursor-not-allowed flex items-center gap-2';
    }

    function saveBldgChanges() {
        const updates = [];
        bldgAllData.forEach(row => {
            const orig = bldgOriginalData.find(o => String(o.id) === String(row.id));
            if (!orig) return;
            const changes = {}; let has = false;
            ['office_type', 'school_id', 'office_name', 'address', 'storeys', 'classrooms', 'article', 'description', 'classification', 'occupancy_nature', 'location', 'date_constructed', 'acquisition_date', 'property_number', 'acquisition_cost', 'estimated_useful_life', 'appraised_value', 'appraisal_date', 'remarks'].forEach(k => {
                if (String(row[k] ?? '').trim() !== String(orig[k] ?? '').trim()) { changes[k] = row[k]; has = true; }
            });
            if (has) updates.push({ id: row.id, ...changes });
        });

        if (updates.length === 0) return Swal.fire('No Changes', 'No records were modified.', 'info');
        Swal.fire({ title: 'Save Changes?', text: `Modify ${updates.length} records?`, icon: 'question', showCancelButton: true, confirmButtonColor: '#10b981' }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Saving...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                fetch('{{ route("api.buildings.updateBatch") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ updates: updates })
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        Swal.fire('Saved!', data.message, 'success').then(() => {
                            bldgOriginalData = JSON.parse(JSON.stringify(bldgAllData));
                            bldgUndoStack = []; bldgRedoStack = []; updateBldgUndoBtn(); renderBldgTable();
                        });
                    } else Swal.fire('Error', data.message, 'error');
                }).catch(() => Swal.fire('Error', 'Server error.', 'error'));
            }
        });
    }
</script>
```

