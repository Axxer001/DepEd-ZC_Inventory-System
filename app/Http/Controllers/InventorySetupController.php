<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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
use App\Services\GeminiService;

class InventorySetupController extends Controller
{
    /**
     * Build in-memory lookup cache for bulk operations.
     */
    private function buildLookupCache(): array
    {
        return [
            'classifications'     => Classification::pluck('id', 'name')->toArray(),
            'categories'          => Category::get()->groupBy('classification_id')->map(fn($items) => $items->keyBy('name')),
            'items'               => Item::get()->groupBy('category_id')->map(fn($items) => $items->keyBy('name')),
            'acquisition_sources' => AcquisitionSource::pluck('id', 'name')->toArray(),
            'procurement_modes'   => ProcurementMode::pluck('id', 'name')->toArray(),
            'employees'           => Employee::where('status', 'Active')->get()->keyBy(function($e) {
                                        return strtolower(trim("{$e->first_name} {$e->last_name}"));
                                    }),
            'schools'             => School::pluck('id', 'school_id')->toArray(),
            'offices'             => Office::pluck('id', 'office_id')->toArray(),
        ];
    }

    /**
     * MODULE 1: MASTER REGISTRY — Register or update an item in the master list.
     *
     * This method now uses the new schema:
     * - acquisition_sources (replaces stakeholders)
     * - asset_sources (replaces sub_items)
     * - No more master_quantity on items (derived from SUM of asset_sources)
     * - No more asset_transactions
     */
    public function getDropdownData(): \Illuminate\Http\JsonResponse
    {
        $classifications = \App\Models\Classification::orderBy('name')->get(['id', 'name']);
        $categories      = \App\Models\Category::orderBy('name')->get(['id', 'name', 'classification_id']);

        return response()->json(compact('classifications', 'categories'));
    }

    public function storeItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'classification_id' => 'required|exists:classifications,id',
            'category_id'       => 'required|exists:categories,id',
            'item_name'         => 'required|string|max:255',
            'condition'         => 'required|in:Good Condition,Needs Repair,Unserviceable',
            'description'       => 'nullable|string',
            'uom'               => 'nullable|string|max:255',
            'asset_cost'        => 'required|numeric|min:0',
            'quantity'          => 'required|integer|min:1',
            'useful_life'       => 'nullable|integer|min:0',
            'acceptance_date'   => 'nullable|date',
            'acquisition_source_id' => 'required|exists:acquisition_sources,id',
            'procurement_mode_id'   => 'nullable|exists:procurement_modes,id',
        ]);

        $userName = Auth::user() ? Auth::user()->name : 'System';

        // Resolve origin tracking (mirrors storeBuilding pattern)
        $user = Auth::user();
        $originSystemType = 'main';
        $registeredBySchoolId = null;
        if ($user && $user->isSchoolSystem()) {
            if (empty($user->school_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is not linked to a school. Contact an administrator.'
                ], 422);
            }
            $originSystemType = 'school';
            $registeredBySchoolId = $user->school_id;
        }

        try {
            DB::beginTransaction();

            // 1. Ensure Category belongs to Classification
            $category = Category::where('id', $validated['category_id'])
                ->where('classification_id', $validated['classification_id'])
                ->firstOrFail();

            // 2. Resolve Item
            $item = Item::firstOrCreate(
                ['name' => trim($validated['item_name']), 'category_id' => $category->id]
            );

            // 3. Create Asset Source
            $assetSource = AssetSource::create([
                'item_id'                => $item->id,
                'acquisition_source_id'  => $validated['acquisition_source_id'],
                'procurement_mode_id'    => $validated['procurement_mode_id'] ?? null,
                'description'            => $validated['description'] ?? null,
                'unit_of_measurement'    => $validated['uom'] ?? 'Unit',
                'asset_cost'             => $validated['asset_cost'],
                'quantity'               => $validated['quantity'],
                'estimated_useful_life'  => $validated['useful_life'] ?? 0,
                'warranty'               => $validated['warranty'] ?? null,
                'acceptance_date'        => $validated['acceptance_date'] ?? now(),
                'condition'              => $validated['condition'],
            ]);

            // 4. Create Asset Assignment (In Property and Supply Unit / AMU)
            AssetAssignment::create([
                'asset_source_id'         => $assetSource->id,
                'employee_id'             => null,
                'school_id'               => null,
                'office_id'               => \App\Models\Office::psuId(),
                'property_number'         => null,
                'acquisition_cost'        => $validated['asset_cost'] * $validated['quantity'],
                'acquisition_date'        => $validated['acceptance_date'] ?? now(),
                'origin_system_type'      => $originSystemType,
                'registered_by_school_id' => $registeredBySchoolId,
            ]);

            DB::table('system_logs')->insert([
                'user' => $userName,
                'activity' => "Registered asset: {$validated['item_name']}",
                'module' => 'Assets',
                'action_type' => 'Create',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $admins = \App\Models\User::getNotificationRecipients(null);
            $dummyAsset = (object)['description' => $validated['item_name']];
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AssetAddedNotification($dummyAsset));
            }

            DB::commit();

            \App\Http\Controllers\DashboardController::notifyUpdate($registeredBySchoolId);

            return response()->json([
                'success' => true, 
                'message' => "Asset '{$validated['item_name']}' registered successfully."
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Failed to register asset: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * MODULE 2: BATCH REGISTRATION — Securely parse and insert bulk-generated table rows.
     *
     * Pipeline:
     *  1. Gemini sanitizes/normalises the raw rows (runs before any DB work).
     *  2. A single buildLookupCache() call loads all reference data into RAM.
     *  3. Row loop resolves every FK from memory — zero N+1 queries.
     *  4. Employees are match-only: unresolved names skip the row and report an error.
     *  5. condition is stored on asset_sources (ENUM), not asset_assignments.
     */
    public function storeBatch(Request $request)
    {
        $payload = $request->validate([
            'rows'    => 'required|array|min:1',
            'skip_ai' => 'nullable|boolean',
        ]);

        // ── Step 1: Gemini sanitation (before transaction) ─────────────────────
        if (!empty($payload['skip_ai'])) {
            $rows = $payload['rows'];
        } else {
            $gemini = new \App\Services\GeminiService();
            $rows   = $gemini->sanitizeRows($payload['rows'], 'manual_batch');
        }

        /** @var \App\Models\User|null $user */
        $user     = \Illuminate\Support\Facades\Auth::user();
        $userName = $user?->name ?? 'System';

        // Resolve origin tracking once — same value for every row in the batch
        $originSystemType = 'main';
        $registeredBySchoolId = null;
        if ($user && $user->isSchoolSystem()) {
            if (empty($user->school_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is not linked to a school. Contact an administrator.'
                ], 422);
            }
            $originSystemType = 'school';
            $registeredBySchoolId = $user->school_id;
        }

        $catCache = [];
        foreach (DB::table('categories')->get() as $cat) {
            $catCache[$cat->classification_id . '_' . strtolower(trim($cat->name))] = $cat->id;
        }

        $itemCache = [];
        foreach (DB::table('items')->get() as $item) {
            $itemCache[$item->category_id . '_' . strtolower(trim($item->name))] = $item->id;
        }

        $acqContactCache = [];
        foreach (DB::table('acquisition_sources')->select('id', 'contact_person', 'contact_position')->get() as $src) {
            $acqContactCache[$src->id] = [
                'contact_person' => $src->contact_person,
                'contact_position' => $src->contact_position,
            ];
        }

        $supplierContactCache = [];
        foreach (DB::table('suppliers')->select('id', 'supplier_personnel', 'contact_number', 'contact_email', 'service_center')->get() as $sup) {
            $supplierContactCache[$sup->id] = [
                'supplier_personnel' => $sup->supplier_personnel,
                'contact_number' => $sup->contact_number,
                'contact_email' => $sup->contact_email,
                'service_center' => $sup->service_center,
            ];
        }

        $cache = [
            'acq_source' => array_change_key_case(DB::table('acquisition_sources')->pluck('id', 'name')->toArray(), CASE_LOWER),
            'acq_contact'=> $acqContactCache,
            'class'      => array_change_key_case(DB::table('classifications')->pluck('id', 'name')->toArray(), CASE_LOWER),
            'cat_composite'  => $catCache,
            'item_composite' => $itemCache,
            'mode'       => array_change_key_case(DB::table('procurement_modes')->pluck('id', 'name')->toArray(), CASE_LOWER),
            'supplier'   => array_change_key_case(DB::table('suppliers')->pluck('id', 'name')->toArray(), CASE_LOWER),
            'supplier_contact' => $supplierContactCache,
        ];

        $conditionMap = [
            'good'           => 'Good Condition',
            'good condition' => 'Good Condition',
            'serviceable'    => 'Good Condition',
            'needs repair'   => 'Needs Repair',
            'repair'         => 'Needs Repair',
            'minor repair'   => 'Needs Repair',
            'major repair'   => 'Needs Repair',
            'unserviceable'  => 'Unserviceable',
            'condemned'      => 'Unserviceable',   // legacy PIF value
            'not useable'    => 'Unserviceable',
        ];

        $errors   = [];
        $inserted = 0;

        try {
            DB::beginTransaction();

            $addedDetails = [];

            foreach ($rows as $rowIndex => $row) {
                $rowNum = $rowIndex + 1;

                // Normalize keys to lowercase with spaces
                $normalizedRow = [];
                foreach ($row as $key => $val) {
                    $normalizedKey = strtolower(str_replace(['_', '-'], ' ', trim($key)));
                    $normalizedRow[$normalizedKey] = $val;
                }

                // ── Resolve Classification → Category → Item ─────────────────
                $className = trim($normalizedRow['classification'] ?? '');
                if (empty($className)) {
                    $errors[] = "Row {$rowNum}: Classification is required.";
                    continue;
                }
                $classId   = $cache['class'][strtolower($className)] ?? null;
                if (!$classId) {
                    $errors[] = "Row {$rowNum}: Classification '{$className}' does not exist. Please register it first.";
                    continue;
                }

                $catName = trim($normalizedRow['category'] ?? '');
                if (empty($catName)) {
                    $errors[] = "Row {$rowNum}: Category is required.";
                    continue;
                }
                $catKey  = $classId . '_' . strtolower($catName);
                $catId   = $cache['cat_composite'][$catKey] ?? null;
                if (!$catId) {
                    $errors[] = "Row {$rowNum}: Category '{$catName}' does not exist under Classification '{$className}'. Please register it first.";
                    continue;
                }

                $itemName = trim($normalizedRow['item'] ?? 'Unknown Item');
                $itemKey  = $catId . '_' . strtolower($itemName);
                $itemId   = $cache['item_composite'][$itemKey] ?? null;
                if (!$itemId) {
                    $itemId = DB::table('items')->insertGetId([
                        'category_id' => $catId,
                        'name'        => $itemName,
                        'created_at'  => now(),
                        'updated_at'  => now()
                    ]);
                    $cache['item_composite'][$itemKey] = $itemId;
                }

                // ── Resolve Procurement Mode (lookup only, cannot create) ───
                $modeName = trim($normalizedRow['mode'] ?? '');
                $modeId = null;
                if ($modeName) {
                    $modeId = $cache['mode'][strtolower($modeName)] ?? null;
                    if (!$modeId) {
                        $errors[] = "Row {$rowNum}: Mode of Acquisition '{$modeName}' does not exist. Only 'PROCUREMENT', 'TRANSFER', and 'DONATION' are allowed.";
                        continue;
                    }
                }

                // ── Resolve Acquisition Source (lookup only) ─────────────────
                $acqSourceName = trim($normalizedRow['source'] ?? '');
                if ($acqSourceName === '') {
                    $acqSourceName = 'N/A';
                }
                $acqSourceId = $cache['acq_source'][strtolower($acqSourceName)] ?? null;
                if (!$acqSourceId) {
                    if (strtolower($acqSourceName) === 'n/a') {
                        $acqSourceId = DB::table('acquisition_sources')->insertGetId([
                            'name' => 'N/A',
                            'source_type' => 'Internal',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $cache['acq_source']['n/a'] = $acqSourceId;
                    } else {
                        $errors[] = "Row {$rowNum}: Source of Acquisition '{$acqSourceName}' does not exist. Please create it in Source Management first.";
                        continue;
                    }
                }

                // ── Resolve Supplier (lookup only) ───────────────────────────
                $supplierName = trim($normalizedRow['supplier'] ?? '');
                if ($supplierName === '') {
                    $supplierName = 'N/A';
                }
                $supplierId = $cache['supplier'][strtolower($supplierName)] ?? null;
                if (!$supplierId) {
                    if (strtolower($supplierName) === 'n/a') {
                        $supplierId = DB::table('suppliers')->insertGetId([
                            'name' => 'N/A',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $cache['supplier']['n/a'] = $supplierId;
                    }
                }

                // ── Assets go to Warehouse (AMU) by default ──────────────────
                $employeeId = null;
                $employeeName = "Unassigned";

                // ── Map Condition to ENUM (stored on asset_sources) ──────────
                $rawCondition = strtolower(trim($normalizedRow['condition'] ?? ''));
                $condition    = $conditionMap[$rawCondition] ?? 'Good Condition';

                $personnel = !empty($normalizedRow['personnel']) ? trim($normalizedRow['personnel']) : null;
                $position = !empty($normalizedRow['position']) ? trim($normalizedRow['position']) : null;

                if ($acqSourceId && isset($cache['acq_contact'][$acqSourceId])) {
                    if (empty($personnel)) {
                        $personnel = $cache['acq_contact'][$acqSourceId]['contact_person'];
                    }
                    if (empty($position)) {
                        $position = $cache['acq_contact'][$acqSourceId]['contact_position'];
                    }
                }

                $suppPersonnel = !empty($normalizedRow['supplier personnel']) ? trim($normalizedRow['supplier personnel']) : null;
                $suppServiceCenter = !empty($normalizedRow['service center']) ? trim($normalizedRow['service center']) : null;
                $suppContactNumber = null;
                $suppContactEmail = null;

                if ($supplierId && isset($cache['supplier_contact'][$supplierId])) {
                    if (empty($suppPersonnel)) {
                        $suppPersonnel = $cache['supplier_contact'][$supplierId]['supplier_personnel'];
                    }
                    if (empty($suppServiceCenter)) {
                        $suppServiceCenter = $cache['supplier_contact'][$supplierId]['service_center'];
                    }
                    $suppContactNumber = $cache['supplier_contact'][$supplierId]['contact_number'];
                    $suppContactEmail = $cache['supplier_contact'][$supplierId]['contact_email'];
                }

                // 1. Resolve UOM / Unit
                $uom = 'Unit';
                foreach (['uom', 'unit', 'unit of measurement'] as $k) {
                    if (isset($normalizedRow[$k]) && $normalizedRow[$k] !== '') {
                        $uom = trim($normalizedRow[$k]);
                        break;
                    }
                }

                // 2. Resolve Qty / Quantity
                $qty = 1;
                foreach (['qty', 'quantity'] as $k) {
                    if (isset($normalizedRow[$k]) && $normalizedRow[$k] !== '') {
                        $cleanQty = preg_replace('/[^0-9]/', '', $normalizedRow[$k]);
                        if ($cleanQty !== '') {
                            $qty = (int) $cleanQty;
                        }
                        break;
                    }
                }

                // 3. Resolve Cost / Price
                $cost = 0.0;
                foreach (['cost', 'price', 'asset cost'] as $k) {
                    if (isset($normalizedRow[$k]) && $normalizedRow[$k] !== '') {
                        $cleanCost = preg_replace('/[^0-9.]/', '', str_replace(',', '', $normalizedRow[$k]));
                        if ($cleanCost !== '') {
                            $cost = (float) $cleanCost;
                        }
                        break;
                    }
                }

                // 4. Resolve Warranty
                $warranty = null;
                foreach (['warranty', 'warranty period'] as $k) {
                    if (isset($normalizedRow[$k]) && $normalizedRow[$k] !== '') {
                        $cleanWarranty = preg_replace('/[^0-9]/', '', $normalizedRow[$k]);
                        if ($cleanWarranty !== '') {
                            $warranty = (int) $cleanWarranty;
                        }
                        break;
                    }
                }

                // 5. Resolve Useful Life
                $usefulLife = 0;
                foreach (['useful life', 'useful_life', 'useful-life', 'estimated useful life'] as $k) {
                    if (isset($normalizedRow[$k]) && $normalizedRow[$k] !== '') {
                        $cleanLife = preg_replace('/[^0-9]/', '', $normalizedRow[$k]);
                        if ($cleanLife !== '') {
                            $usefulLife = (int) $cleanLife;
                        }
                        break;
                    }
                }

                // 6. Resolve Acceptance Date
                $acceptanceDate = now()->toDateString();
                foreach (['acceptance date', 'acceptance_date', 'acceptance-date', 'date'] as $k) {
                    if (!empty($normalizedRow[$k])) {
                        $acceptanceDate = trim($normalizedRow[$k]);
                        break;
                    }
                }

                // ── Insert asset_sources ─────────────────────────────────────
                $assetSourceId = DB::table('asset_sources')->insertGetId([
                    'item_id'                => $itemId,
                    'acquisition_source_id'  => $acqSourceId,
                    'supplier_id'            => $supplierId,
                    'procurement_mode_id'    => $modeId,
                    'description'            => $normalizedRow['description'] ?? null,
                    'unit_of_measurement'    => $uom,
                    'asset_cost'             => $cost,
                    'quantity'               => $qty,
                    'estimated_useful_life'  => $usefulLife,
                    'warranty'               => $warranty,
                    'acceptance_date'        => $acceptanceDate,
                    'condition'              => $condition,
                    'equipment'              => ($cost <= 49999 ? 'SEE' : 'PPE'),
                    'contact_person'         => $personnel,
                    'contact_position'       => $position,
                    'supplier_personnel'     => $suppPersonnel,
                    'supplier_contact_number'=> $suppContactNumber,
                    'supplier_contact_email' => $suppContactEmail,
                    'supplier_service_center'=> $suppServiceCenter,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);

                // ── Insert asset_assignments (Property and Supply Unit / AMU) ──────
                DB::table('asset_assignments')->insert([
                    'asset_source_id'         => $assetSourceId,
                    'employee_id'             => null,
                    'school_id'               => null,
                    'office_id'               => \App\Models\Office::psuId(),
                    'property_number'         => null,
                    'acquisition_cost'        => $cost * $qty,
                    'acquisition_date'        => $acceptanceDate,
                    'origin_system_type'      => $originSystemType,
                    'registered_by_school_id' => $registeredBySchoolId,
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ]);

                $addedDetails[] = "Added " . $qty . " " . $uom . " {$itemName} to Property and Supply Unit (AMU)";

                $inserted++;
            }

            if (count($errors) > 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Registration failed due to validation errors:<br><br>' . implode('<br>', $errors)
                ], 422);
            }

            DB::table('system_logs')->insert([
                'user'        => $userName,
                'activity'    => "Batch Registration: {$inserted} assets inserted",
                'module'      => 'Assets',
                'action_type' => 'Create',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            if ($inserted > 0) {
                $detailedMsg = '';
                if (count($addedDetails) > 10) {
                    $sliced = array_slice($addedDetails, 0, 10);
                    $more = count($addedDetails) - 10;
                    $sliced[] = "... and {$more} more item(s).";
                    $detailedMsg = implode('<br><br>', $sliced);
                } else {
                    $detailedMsg = implode('<br><br>', $addedDetails);
                }

                $admins = \App\Models\User::getNotificationRecipients(null);
                $dummyAsset = (object)[
                    'title' => 'Assets Registered',
                    'message' => "Successfully registered {$inserted} item(s).",
                    'detailed_message' => $detailedMsg
                ];
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\AssetAddedNotification($dummyAsset));
                }
            }
            DB::commit();

            \App\Http\Controllers\DashboardController::notifyUpdate($registeredBySchoolId);

            return response()->json([
                'success' => true,
                'message' => "Successfully registered {$inserted} item(s).",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('[storeBatch] Failure: ' . $e->getMessage());
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
                $schoolId = null;
                $originSystemType = 'main';
                $registeredBySchoolId = null;
                $user = auth()->user();
                if ($user && $user->isSchoolSystem()) {
                    $schoolId = $user->school_id;
                    $originSystemType = 'school';
                    $registeredBySchoolId = $user->school_id;
                }
                
                // At least require an article or description to consider the row valid
                $article = trim($row['article'] ?? '');
                $description = trim($row['description'] ?? '');
                $propertyNo = trim($row['property_number'] ?? '');
                
                if (empty($article) && empty($description) && empty($propertyNo)) continue;


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
                    'origin_system_type' => $originSystemType,
                    'registered_by_school_id' => $registeredBySchoolId,
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

                $admins = \App\Models\User::getNotificationRecipients($schoolId);
                $dummyAsset = (object)[
                    'title' => 'Buildings Registered',
                    'message' => "Successfully registered {$inserted} building(s).",
                    'detailed_message' => "Registered {$inserted} building(s) via manual entry."
                ];
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\AssetAddedNotification($dummyAsset));
                }
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
    public function updateBatch(Request $request): JsonResponse
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.dist_id' => 'required|integer',
            'updates.*.src_id' => 'required|integer',
        ]);

        $updates = $request->input('updates');
        $userName = Auth::user() ? Auth::user()->name : 'System';
        $updateCount = 0;

        DB::beginTransaction();
        try {
            // Section 8.3: Field Mapping
            $sourceFieldsMap = [
                'description'     => 'description',
                'uom'             => 'unit_of_measurement',
                'cost'            => 'asset_cost',
                'qty'             => 'quantity',
                'useful_life'     => 'estimated_useful_life',
                'acceptance_date' => 'acceptance_date',
                'condition'       => 'condition',
            ];

            $assignmentFieldsMap = [
                'property_no'      => 'property_number',
                'acquisition_cost' => 'acquisition_cost',
                'acquisition_date' => 'acquisition_date',
                'employee_id'      => 'employee_id',
            ];

            foreach ($updates as $data) {
                // 1. Update Asset Source
                $srcUpdates = [];
                foreach ($sourceFieldsMap as $requestKey => $dbCol) {
                    if (array_key_exists($requestKey, $data)) {
                        $value = $data[$requestKey];
                        if ($dbCol === 'condition') {
                            $conditionMap = [
                                'good'           => 'Good Condition',
                                'good condition' => 'Good Condition',
                                'serviceable'    => 'Good Condition',
                                'needs repair'   => 'Needs Repair',
                                'repair'         => 'Needs Repair',
                                'unserviceable'  => 'Unserviceable',
                                'condemned'      => 'Unserviceable',
                                'not useable'    => 'Unserviceable',
                            ];
                            $value = $conditionMap[strtolower(trim($value))] ?? 'Good Condition';
                        }
                        $srcUpdates[$dbCol] = $value;
                    }
                }

                // Handle classification/category/item hierarchy changes
                if (isset($data['classification']) || isset($data['category']) || isset($data['article'])) {
                    $currentSource = DB::table('asset_sources')
                        ->join('items', 'asset_sources.item_id', '=', 'items.id')
                        ->join('categories', 'items.category_id', '=', 'categories.id')
                        ->join('classifications', 'categories.classification_id', '=', 'classifications.id')
                        ->where('asset_sources.id', $data['src_id'])
                        ->select('classifications.name as class_name', 'categories.name as cat_name', 'items.name as item_name')
                        ->first();

                    $className = trim($data['classification'] ?? ($currentSource ? $currentSource->class_name : 'Unclassified'));
                    $catName = trim($data['category'] ?? ($currentSource ? $currentSource->cat_name : 'General'));
                    $itemName = trim($data['article'] ?? ($currentSource ? $currentSource->item_name : 'Unknown Item'));

                    $classId = DB::table('classifications')->where('name', $className)->value('id')
                        ?? DB::table('classifications')->insertGetId(['name' => $className, 'created_at' => now(), 'updated_at' => now()]);

                    $catId = DB::table('categories')->where('classification_id', $classId)->where('name', $catName)->value('id')
                        ?? DB::table('categories')->insertGetId(['classification_id' => $classId, 'name' => $catName, 'created_at' => now(), 'updated_at' => now()]);

                    $itemId = DB::table('items')->where('category_id', $catId)->where('name', $itemName)->value('id')
                        ?? DB::table('items')->insertGetId(['category_id' => $catId, 'name' => $itemName, 'created_at' => now(), 'updated_at' => now()]);

                    $srcUpdates['item_id'] = $itemId;
                }

                // Handle Acquisition Source
                if (isset($data['acq_source'])) {
                    $acqSourceName = trim($data['acq_source']);
                    if (!empty($acqSourceName)) {
                        DB::table('acquisition_sources')->updateOrInsert(
                            ['name' => $acqSourceName],
                            ['source_type' => 'Internal', 'updated_at' => now()]
                        );
                        $acqSourceId = DB::table('acquisition_sources')->where('name', $acqSourceName)->value('id');
                        $srcUpdates['acquisition_source_id'] = $acqSourceId;
                    }
                }

                // Handle Mode of Acquisition
                if (isset($data['mode'])) {
                    $modeName = trim($data['mode']);
                    if (!empty($modeName)) {
                        $modeId = DB::table('procurement_modes')->where('name', $modeName)->value('id')
                            ?? DB::table('procurement_modes')->insertGetId(['name' => $modeName, 'created_at' => now(), 'updated_at' => now()]);
                        $srcUpdates['procurement_mode_id'] = $modeId;
                    }
                }

                // Handle Source Personnel and Position
                if (isset($data['personnel']) || isset($data['position'])) {
                    $acqSourceId = $srcUpdates['acquisition_source_id'] ?? null;
                    if ($acqSourceId === null) {
                        $source = DB::table('asset_sources')->where('id', $data['src_id'])->first();
                        $acqSourceId = $source ? $source->acquisition_source_id : null;
                    }

                    if ($acqSourceId) {
                        $currentSourceRec = DB::table('acquisition_sources')->where('id', $acqSourceId)->first();

                        $contactName = trim($data['personnel'] ?? ($currentSourceRec ? $currentSourceRec->contact_person : ''));
                        $contactPos = trim($data['position'] ?? ($currentSourceRec ? $currentSourceRec->contact_position : ''));

                        if (!empty($contactName)) {
                            DB::table('acquisition_sources')->where('id', $acqSourceId)->update([
                                'contact_person' => $contactName,
                                'contact_position' => $contactPos ?: null,
                                'updated_at' => now()
                            ]);
                        }
                    }
                }

                // Handle condition remarks mapping
                if (isset($data['remarks'])) {
                    $conditionMap = [
                        'good'           => 'Good Condition',
                        'good condition' => 'Good Condition',
                        'serviceable'    => 'Good Condition',
                        'needs repair'   => 'Needs Repair',
                        'repair'         => 'Needs Repair',
                        'unserviceable'  => 'Unserviceable',
                        'condemned'      => 'Unserviceable',
                        'not useable'    => 'Unserviceable',
                    ];
                    $srcUpdates['condition'] = $conditionMap[strtolower(trim($data['remarks']))] ?? 'Good Condition';
                }

                // Handle hierarchy changes if provided (Item resolution fallback)
                if (isset($data['item_id'])) {
                    $srcUpdates['item_id'] = $data['item_id'];
                }

                // Get quantity to check if it's bulk/multiple
                $quantity = null;
                if (isset($srcUpdates['quantity'])) {
                    $quantity = (int)$srcUpdates['quantity'];
                } else {
                    $currentSource = DB::table('asset_sources')->where('id', $data['src_id'])->first();
                    if ($currentSource) {
                        $quantity = (int)$currentSource->quantity;
                    }
                }

                if (!empty($srcUpdates)) {
                    DB::table('asset_sources')->where('id', $data['src_id'])->update($srcUpdates);
                }

                // 2. Update Asset Assignment
                $distUpdates = [];
                foreach ($assignmentFieldsMap as $requestKey => $dbCol) {
                    if (array_key_exists($requestKey, $data)) {
                        $distUpdates[$dbCol] = $data[$requestKey];
                    }
                }

                // Resolve employee_id from code to DB primary key if present
                if (array_key_exists('employee_id', $distUpdates)) {
                    $empVal = $distUpdates['employee_id'];
                    if (!empty($empVal)) {
                        $resolvedId = DB::table('employees')
                            ->where('employee_id', $empVal)
                            ->orWhere('id', $empVal)
                            ->value('id');
                        $distUpdates['employee_id'] = $resolvedId;
                    } else {
                        $distUpdates['employee_id'] = null;
                    }
                }

                // Handle Location (School/Office) association with Employee
                if (isset($data['school_id'])) {
                    $employeeId = $distUpdates['employee_id'] ?? null;
                    if ($employeeId === null) {
                        $assignment = DB::table('asset_assignments')->where('id', $data['dist_id'])->first();
                        $employeeId = $assignment ? $assignment->employee_id : null;
                    }

                    if ($employeeId) {
                        $school = DB::table('schools')->where('school_id', $data['school_id'])->first();
                        if ($school) {
                            DB::table('employees')->where('id', $employeeId)->update([
                                'school_id' => $school->id,
                                'office_id' => null,
                            ]);
                        } else {
                            $office = DB::table('offices')
                                ->where('office_id', $data['school_id'])
                                ->orWhere('id', $data['school_id'])
                                ->first();
                            if ($office) {
                                DB::table('employees')->where('id', $employeeId)->update([
                                    'school_id' => null,
                                    'office_id' => $office->id,
                                ]);
                            }
                        }
                    }
                }

                // Recompute acquisition_cost if cost or qty changes
                if (isset($srcUpdates['asset_cost']) || isset($srcUpdates['quantity'])) {
                    $currentSource = DB::table('asset_sources')->where('id', $data['src_id'])->first();
                    $costVal = isset($srcUpdates['asset_cost']) ? (float)$srcUpdates['asset_cost'] : ($currentSource ? (float)$currentSource->asset_cost : 0.0);
                    $qtyVal = isset($srcUpdates['quantity']) ? (int)$srcUpdates['quantity'] : ($currentSource ? (int)$currentSource->quantity : 1);
                    
                    $distUpdates['acquisition_cost'] = $costVal * $qtyVal;
                }

                // If quantity > 1, property number must be NULL
                if ($quantity !== null && $quantity > 1) {
                    $distUpdates['property_number'] = null;
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

            if ($updateCount > 0) {
                $schoolId = Auth::check() && Auth::user()->isSchoolSystem() ? Auth::user()->school_id : null;
                $admins = \App\Models\User::getNotificationRecipients($schoolId);
                $dummyAsset = (object)[
                    'title' => 'Bulk Edit Applied',
                    'message' => "Successfully updated {$updateCount} asset records.",
                    'detailed_message' => "A bulk edit operation updated {$updateCount} asset record(s)."
                ];
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\AssetUpdatedNotification($dummyAsset));
                }
            }

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

                $directCols = ['office_type', 'address', 'region', 'division', 'location', 'occupancy_nature', 'date_constructed', 'acquisition_date', 'property_number', 'acquisition_cost', 'estimated_useful_life', 'remarks', 'appraised_value', 'appraisal_date'];
                
                foreach ($directCols as $col) {
                    if (array_key_exists($col, $update)) {
                        $recordData[$col] = $update[$col];
                    }
                }

                if (array_key_exists('school_id', $update)) {
                    $schoolVal = trim($update['school_id']);
                    if (!empty($schoolVal)) {
                        $resolvedSchoolId = DB::table('schools')
                            ->where('school_id', $schoolVal)
                            ->orWhere('id', $schoolVal)
                            ->value('id');
                        $recordData['school_id'] = $resolvedSchoolId;
                    } else {
                        $recordData['school_id'] = null;
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
    /**
     * Fetch all unassigned assets (in AMU/Warehouse)
     */
    public function getUnassignedAssets(Request $request)
    {
        $user = auth()->user();
        $query = DB::table('asset_assignments')
            ->join('asset_sources', 'asset_assignments.asset_source_id', '=', 'asset_sources.id')
            ->join('items', 'asset_sources.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->join('classifications', 'categories.classification_id', '=', 'classifications.id')
            ->whereNull('asset_assignments.employee_id')
            ->where('asset_sources.condition', '!=', 'Archived');

        $psuId = \App\Models\Office::psuId();

        if ($user && $user->isSchoolSystem()) {
            $schoolId = $user->school_id;
            $query->where(function ($q) use ($schoolId) {
                $q->where(function ($q2) use ($schoolId) {
                    // Self-registered by this school, not yet pushed to school/office
                    $q2->where('asset_assignments.registered_by_school_id', $schoolId)
                       ->whereNull('asset_assignments.school_id');
                })->orWhere('asset_assignments.school_id', $schoolId); // Assigned to this school by main system
            })->whereNull('asset_assignments.office_id');
        } else {
            // Main system: assets sitting in PSU
            $query->whereNull('asset_assignments.school_id')
                  ->where('asset_assignments.office_id', $psuId);
        }

        $query->select(
            'asset_assignments.id as assignment_id',
            'asset_assignments.property_number',
            'asset_assignments.serial_number',
            'classifications.name as classification',
            'categories.name as category',
            'items.name as item_name',
            'asset_sources.description as sub_item_name',
            'asset_sources.quantity',
            'asset_sources.unit_of_measurement as uom',
            'asset_sources.asset_cost',
            'asset_sources.condition',
            'asset_sources.acceptance_date',
            'categories.id as category_id',
            'categories.see_category_code',
            'categories.ppe_category_code',
            'asset_sources.equipment',
            'asset_sources.id as asset_source_id'
        );

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('items.name', 'like', "%{$search}%")
                  ->orWhere('asset_sources.description', 'like', "%{$search}%");
            });
        }

        $query->orderBy('asset_assignments.id', 'asc');

        $assets = $query->get()->map(function($asset) {
            if (empty($asset->property_number)) {
                $order = DB::table('asset_sources')
                    ->join('items', 'asset_sources.item_id', '=', 'items.id')
                    ->where('items.category_id', $asset->category_id)
                    ->where('asset_sources.id', '<=', $asset->asset_source_id)
                    ->count();

                // Base type: SEE (semi-expendable) vs PPE (property, plant & equipment)
                $baseType = $asset->equipment ?: ($asset->asset_cost <= 49999 ? 'SEE' : 'PPE');
                $year = date('Y');

                if ($baseType === 'PPE') {
                    // PPE: unit cost > 49,999
                    $label  = 'PPE';
                    $shCode = trim($asset->ppe_category_code ?? '0000');
                } else {
                    // SEE splits by unit cost into Low Value / High Value semi-expendable property
                    // SPLV: unit cost <= 5,000 | SPHV: unit cost > 5,000 (and <= 49,999)
                    $label  = ((float)$asset->asset_cost <= 5000) ? 'SPLV' : 'SPHV';
                    $shCode = trim($asset->see_category_code ?? '0000');
                }

                if (strlen($shCode) < 4) {
                    $shCode = str_pad($shCode, 4, '0', STR_PAD_LEFT);
                }
                $shCodePart1 = substr($shCode, 0, 2);
                $shCodePart2 = substr($shCode, 2, 2);
                $orderStr = str_pad($order, 4, '0', STR_PAD_LEFT);

                // Base property number: "{EQ} {YEAR}-{CODE_PART1}-{CODE_PART2}-{ORDER_NO}"
                // The "-{school_id/office_id}" suffix is appended client-side (see
                // assign-asset-step_blade.php) once a recipient is selected, since the
                // recipient isn't known yet while the asset is still unassigned/in warehouse.
                $asset->property_number = "{$label} {$year}-{$shCodePart1}-{$shCodePart2}-{$orderStr}";
            }
            return $asset;
        });

        return response()->json(['assets' => $assets]);
    }

    /**
     * Assign a single asset from the warehouse
     */
    public function assignItem(Request $request)
    {
        $validated = $request->validate([
            'assignment_id'    => 'required|exists:asset_assignments,id',
            'employee_id'      => 'required|exists:employees,id',
            'property_number'  => 'nullable|string|max:255',
            'serial_number'    => 'nullable|string|max:255',
            'acquisition_date' => 'nullable|date',
        ]);

        $user = auth()->user();
        if ($user && $user->isSchoolSystem()) {
            $targetEmployee = DB::table('employees')->where('id', $validated['employee_id'])->first();
            if (!$targetEmployee || $targetEmployee->school_id !== $user->school_id) {
                return response()->json(['success' => false, 'message' => 'Employee does not belong to your school.'], 403);
            }
            $assignment = DB::table('asset_assignments')->where('id', $validated['assignment_id'])->first();
            $belongsToSchool = $assignment && (
                $assignment->school_id === $user->school_id
                || ($assignment->school_id === null && $assignment->registered_by_school_id === $user->school_id)
                || DB::table('employees')->where('id', $assignment->employee_id)->where('school_id', $user->school_id)->exists()
            );
            if (!$belongsToSchool) {
                return response()->json(['success' => false, 'message' => 'Asset assignment does not belong to your school.'], 403);
            }
        }

        try {
            DB::beginTransaction();

            $assignment = DB::table('asset_assignments')->where('id', $validated['assignment_id'])->first();
            
            if ($assignment->employee_id !== null) {
                return response()->json(['success' => false, 'message' => 'Asset is already assigned.'], 400);
            }

            DB::table('asset_assignments')->where('id', $validated['assignment_id'])->update([
                'employee_id'      => $validated['employee_id'],
                'school_id'        => null,
                'office_id'        => null,
                'property_number'  => $validated['property_number'] ?? null,
                'serial_number'    => $validated['serial_number'] ?? null,
                'acquisition_date' => $validated['acquisition_date'] ?? now()->toDateString(),
                'updated_at'       => now(),
            ]);

            $targetEmployee = DB::table('employees')->where('id', $validated['employee_id'])->first();

            // Fetch acquisition details for the custom history message
            $assetDetails = DB::table('asset_assignments as ad')
                ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
                ->leftJoin('suppliers as sup', 'asrc.supplier_id', '=', 'sup.id')
                ->leftJoin('procurement_modes as pm', 'asrc.procurement_mode_id', '=', 'pm.id')
                ->where('ad.id', $validated['assignment_id'])
                ->select('pm.name as mode_of_acquisition', 'sup.name as supplier_name')
                ->first();

            $mode = $assetDetails->mode_of_acquisition ?? 'procured';
            $supplier = $assetDetails->supplier_name ?? 'Supplier';
            $recipientName = trim($targetEmployee->first_name . ' ' . $targetEmployee->last_name);

            $cost = DB::table('asset_assignments as ad')
                ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
                ->where('ad.id', $validated['assignment_id'])
                ->value('asrc.asset_cost') ?? 0;

            $docType = ($cost > 49999) ? 'PAR' : 'ICS';

            $historyRemarks = "Asset officially {$mode} and registered into the database from DEPED CENTRAL OFFICE, then Delivered from {$supplier} to Asset Management Unit. Now assigned at/to {$recipientName}";

            $transferId = DB::table('asset_transfers')->insertGetId([
                'asset_assignment_id' => $validated['assignment_id'],
                'from_office_id'      => null,
                'to_office_id'        => $targetEmployee->office_id ?? null,
                'to_school_id'        => $targetEmployee->school_id ?? null,
                'from_custodian_id'   => null,
                'to_custodian_id'     => $validated['employee_id'],
                'transfer_date'       => $validated['acquisition_date'] ?? now()->toDateString(),
                'transfer_type'       => 'Initial Distribution',
                'remarks'             => $historyRemarks,
                'authorized_by'       => Auth::id() ?? 1,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);



            $userName = Auth::user() ? Auth::user()->name : 'System';
            DB::table('system_logs')->insert([
                'user'        => $userName,
                'activity'    => "Assigned asset ID {$validated['assignment_id']} to employee ID {$validated['employee_id']}",
                'module'      => 'Assets',
                'action_type' => 'Update',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::commit();

            session()->flash('download_docs', [
                [
                    'recipient_name' => $recipientName,
                    'doc_type'       => $docType,
                    'assignment_id'  => $validated['assignment_id'],
                    'transfer_id'    => $transferId,
                ]
            ]);

            // Dispatch notification
            $item = DB::table('asset_assignments')
                ->join('asset_sources', 'asset_assignments.asset_source_id', '=', 'asset_sources.id')
                ->join('items', 'asset_sources.item_id', '=', 'items.id')
                ->where('asset_assignments.id', $validated['assignment_id'])
                ->select('items.name as item_name')
                ->first();
            $itemTitle = $item ? $item->item_name : 'Asset';
            $propNum = $validated['property_number'] ?? 'No Property Number';

            $notificationData = [
                'title' => 'Asset Assigned',
                'message' => "Asset {$itemTitle} ({$propNum}) assigned to {$recipientName}.",
                'detailed_message' => "Asset: {$itemTitle}\nProperty Number: {$propNum}\nRecipient: {$recipientName}\nHistory:\n{$historyRemarks}",
                'type' => 'asset_assigned'
            ];

            $schoolId = DB::table('employees')->where('id', $validated['employee_id'])->value('school_id');
            $usersToNotify = \App\Models\User::getNotificationRecipients($schoolId);
            foreach ($usersToNotify as $user) {
                $user->notify(new \App\Notifications\AssetAssignedNotification($notificationData));
            }

            \App\Http\Controllers\DashboardController::notifyUpdate($schoolId);

            return response()->json(['success' => true, 'message' => 'Asset assigned successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to assign asset: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Assign multiple assets via batch update
     */
    public function assignBatch(Request $request)
    {
        $validated = $request->validate([
            'assignments'                    => 'required|array|min:1',
            'assignments.*.assignment_id'    => 'required|exists:asset_assignments,id',
            'assignments.*.employee_id'      => 'nullable|exists:employees,id',
            'assignments.*.property_number'  => 'nullable|string|max:255',
            'assignments.*.serial_number'    => 'nullable|string|max:255',
            'assignments.*.acquisition_date' => 'nullable|date',
            'assignments.*.school_name'      => 'nullable|string',
            'assignments.*.school_id'        => 'nullable|string',
            'assignments.*.school_db_id'     => 'nullable|integer',
            'assignments.*.is_office'        => 'nullable|boolean',
            'assignments.*.school_type'      => 'nullable|string',
            'assignments.*.location'         => 'nullable|string',
            'assignments.*.asset_cost'       => 'nullable',
            'assignments.*.quantity'         => 'nullable',
        ]);

        $user = auth()->user();
        if ($user && $user->isSchoolSystem()) {
            foreach ($validated['assignments'] as $key => $data) {
                // Ensure target assignment belongs to their school
                $assignment = DB::table('asset_assignments')->where('id', $data['assignment_id'])->first();
                $belongsToSchool = $assignment && (
                    $assignment->school_id === $user->school_id
                    || ($assignment->school_id === null && $assignment->registered_by_school_id === $user->school_id)
                    || DB::table('employees')->where('id', $assignment->employee_id)->where('school_id', $user->school_id)->exists()
                );
                if (!$belongsToSchool) {
                    return response()->json(['success' => false, 'message' => 'Asset assignment does not belong to your school.'], 403);
                }

                // Must specify employee_id, and employee must belong to their school
                if (empty($data['employee_id'])) {
                    return response()->json(['success' => false, 'message' => 'School accounts must assign assets to employees.'], 403);
                }
                $targetEmployee = DB::table('employees')->where('id', $data['employee_id'])->first();
                if (!$targetEmployee || $targetEmployee->school_id !== $user->school_id) {
                    return response()->json(['success' => false, 'message' => 'Employee must belong to your school.'], 403);
                }

                // Force school_db_id and is_office to be null for safety
                $validated['assignments'][$key]['school_db_id'] = null;
                $validated['assignments'][$key]['is_office'] = null;
            }
        }

        try {
            DB::beginTransaction();

            $count = 0;
            $groupedNotification = [];
            $docsToDownload = [];

            foreach ($validated['assignments'] as $data) {
                $assignment = DB::table('asset_assignments')->where('id', $data['assignment_id'])->first();
                if ($assignment->employee_id !== null) {
                    continue; // Skip if already assigned to an employee
                }

                if (!empty($data['employee_id']) || !empty($data['school_db_id'])) {
                    $updateData = [
                        'property_number'  => $data['property_number'] ?? null,
                        'serial_number'    => $data['serial_number'] ?? null,
                        'acquisition_date' => $data['acquisition_date'] ?? now()->toDateString(),
                        'updated_at'       => now(),
                    ];

                    if (!empty($data['employee_id'])) {
                        $updateData['employee_id'] = $data['employee_id'];
                        $updateData['school_id']   = null;
                        $updateData['office_id']   = null;
                    } else {
                        if (isset($data['is_office']) && $data['is_office']) {
                            $updateData['office_id'] = $data['school_db_id'];
                        } else {
                            $updateData['school_id'] = $data['school_db_id'];
                        }
                    }

                    DB::table('asset_assignments')->where('id', $data['assignment_id'])->update($updateData);

                    $toOfficeId = null;
                    $toSchoolId = null;
                    if (!empty($data['school_db_id'])) {
                        if (isset($data['is_office']) && $data['is_office']) {
                            $toOfficeId = $data['school_db_id'];
                        } else {
                            $toSchoolId = $data['school_db_id'];
                        }
                    } elseif (!empty($data['employee_id'])) {
                        $targetEmployee = DB::table('employees')->where('id', $data['employee_id'])->first();
                        if ($targetEmployee) {
                            $toOfficeId = $targetEmployee->office_id;
                            $toSchoolId = $targetEmployee->school_id;
                        }
                    }

                    // Fetch acquisition details for the custom history message
                    $assetDetails = DB::table('asset_assignments as ad')
                        ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
                        ->leftJoin('suppliers as sup', 'asrc.supplier_id', '=', 'sup.id')
                        ->leftJoin('procurement_modes as pm', 'asrc.procurement_mode_id', '=', 'pm.id')
                        ->where('ad.id', $data['assignment_id'])
                        ->select('pm.name as mode_of_acquisition', 'sup.name as supplier_name')
                        ->first();

                    $mode = $assetDetails->mode_of_acquisition ?? 'procured';
                    $supplier = $assetDetails->supplier_name ?? 'Supplier';

                    $recipientName = 'Warehouse';
                    $recipientKey = '';
                    $recipientType = 'warehouse';
                    $schoolType = null;
                    
                    if (!empty($data['employee_id'])) {
                        $recipientKey = 'emp_' . $data['employee_id'];
                        $emp = DB::table('employees')->where('id', $data['employee_id'])->first();
                        $recipientName = $emp ? trim($emp->first_name . ' ' . $emp->last_name) : 'Custodian';
                        $recipientType = 'employee';
                    } elseif (!empty($data['school_db_id'])) {
                        if (isset($data['is_office']) && $data['is_office']) {
                            $recipientKey = 'off_' . $data['school_db_id'];
                            $recipientName = DB::table('offices')->where('id', $data['school_db_id'])->value('name') ?? 'Office';
                            $recipientType = 'office';
                        } else {
                            $recipientKey = 'sch_' . $data['school_db_id'];
                            $sch = DB::table('schools')->where('id', $data['school_db_id'])->first();
                            $recipientName = $sch ? $sch->name : 'School';
                            $schoolType = $sch ? $sch->type : null;
                            $recipientType = 'school';
                        }
                    }

                    $cost = DB::table('asset_assignments as ad')
                        ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
                        ->where('ad.id', $data['assignment_id'])
                        ->value('asrc.asset_cost') ?? 0;

                    $threshold = ($cost > 49999) ? 'high' : 'low';
                    $groupKey = $recipientType . '_' . ($data['employee_id'] ?? $data['school_db_id']) . '_' . $threshold;

                    $historyRemarks = "Asset officially {$mode} and registered into the database from DEPED CENTRAL OFFICE, then Delivered from {$supplier} to Asset Management Unit. Now assigned at/to {$recipientName}";

                    $transferId = DB::table('asset_transfers')->insertGetId([
                        'asset_assignment_id' => $data['assignment_id'],
                        'from_office_id'      => null,
                        'to_office_id'        => $toOfficeId,
                        'to_school_id'        => $toSchoolId,
                        'from_custodian_id'   => null,
                        'to_custodian_id'     => $data['employee_id'] ?? null,
                        'transfer_date'       => $data['acquisition_date'] ?? now()->toDateString(),
                        'transfer_type'       => 'Initial Distribution',
                        'remarks'             => $historyRemarks,
                        'authorized_by'       => Auth::id() ?? 1,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);

                    if ($recipientType !== 'warehouse') {
                        if (!isset($docsToDownload[$groupKey])) {
                            $docsToDownload[$groupKey] = [
                                'recipient_name' => $recipientName,
                                'recipient_type' => $recipientType,
                                'school_type'    => $schoolType,
                                'cost_threshold' => $threshold,
                                'doc_type'       => $this->resolveDocType($recipientType, $schoolType, $cost),
                                'asset_count'    => 0,
                                'assignment_id'  => $data['assignment_id'],
                                'transfer_id'    => $transferId,
                            ];
                        }
                        $docsToDownload[$groupKey]['asset_count']++;
                    }

                    // Gather item metadata for notifications
                    $item = DB::table('asset_assignments')
                        ->join('asset_sources', 'asset_assignments.asset_source_id', '=', 'asset_sources.id')
                        ->join('items', 'asset_sources.item_id', '=', 'items.id')
                        ->where('asset_assignments.id', $data['assignment_id'])
                        ->select('items.name as item_name')
                        ->first();
                    $itemName = $item ? $item->item_name : 'Asset';
                    $propNum = $data['property_number'] ?? 'No Property Number';

                    if ($recipientKey) {
                        $groupedNotification[$recipientKey]['name'] = $recipientName;
                        $groupedNotification[$recipientKey]['items'][] = [
                            'name' => $itemName,
                            'prop' => $propNum,
                            'history' => $historyRemarks
                        ];
                    }

                    // Update employee's location if provided
                    if (!empty($data['employee_id']) && !empty($data['school_db_id'])) {
                        if (isset($data['is_office']) && $data['is_office']) {
                            DB::table('employees')->where('id', $data['employee_id'])->update([
                                'office_id'  => $data['school_db_id'],
                                'school_id'  => null,
                                'updated_at' => now(),
                            ]);
                        } else {
                            DB::table('employees')->where('id', $data['employee_id'])->update([
                                'school_id'  => $data['school_db_id'],
                                'office_id'  => null,
                                'updated_at' => now(),
                            ]);
                        }
                    }
                    $count++;
                }
            }

            $userName = Auth::user() ? Auth::user()->name : 'System';
            DB::table('system_logs')->insert([
                'user'        => $userName,
                'activity'    => "Batch Assignment: Assigned {$count} asset(s).",
                'module'      => 'Assets',
                'action_type' => 'Update',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::commit();

            if (!empty($docsToDownload)) {
                session()->flash('download_docs', array_values($docsToDownload));
            }

            // Dispatch aggregated or individual notifications
            foreach ($groupedNotification as $key => $group) {
                $recipientName = $group['name'];
                $itemCount = count($group['items']);

                // Resolve school_id from recipient key
                $schoolId = null;
                $parts = explode('_', $key);
                if (count($parts) === 2) {
                    $recipientType = $parts[0];
                    $recipientId = (int)$parts[1];
                    if ($recipientType === 'sch') {
                        $schoolId = $recipientId;
                    } elseif ($recipientType === 'emp') {
                        $schoolId = DB::table('employees')->where('id', $recipientId)->value('school_id');
                    }
                }
                
                $usersToNotify = \App\Models\User::getNotificationRecipients($schoolId);

                if ($itemCount > 10) {
                    $detailedList = [];
                    foreach ($group['items'] as $it) {
                        $detailedList[] = "- {$it['name']} (Property No. {$it['prop']})";
                    }
                    $detailedMessage = "Batch Assignment details for {$recipientName}:\n\n" . implode("\n", $detailedList);

                    $notificationData = [
                        'title' => 'Batch Assets Deployed',
                        'message' => "Successfully deployed a batch of {$itemCount} assets to {$recipientName}.",
                        'detailed_message' => $detailedMessage,
                        'type' => 'asset_assigned'
                    ];

                    foreach ($usersToNotify as $user) {
                        $user->notify(new \App\Notifications\AssetAssignedNotification($notificationData));
                    }
                } else {
                    foreach ($group['items'] as $it) {
                        $notificationData = [
                            'title' => 'Asset Deployed',
                            'message' => "Asset {$it['name']} (Property No. {$it['prop']}) assigned to {$recipientName}.",
                            'detailed_message' => "Asset: {$it['name']}\nProperty Number: {$it['prop']}\nRecipient: {$recipientName}\nHistory:\n{$it['history']}",
                            'type' => 'asset_assigned'
                        ];

                        foreach ($usersToNotify as $user) {
                            $user->notify(new \App\Notifications\AssetAssignedNotification($notificationData));
                        }
                    }
                }
            }



            \App\Http\Controllers\DashboardController::notifyUpdate();

            return response()->json(['success' => true, 'message' => "Successfully assigned {$count} asset(s)."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to assign assets in batch: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Fetch all floating/unassigned buildings (school_id is null)
     */
    public function getUnassignedBuildings(Request $request)
    {
        $query = DB::table('building_records')
            ->leftJoin('building_specs', 'building_records.building_spec_id', '=', 'building_specs.id')
            ->leftJoin('building_types', 'building_specs.building_type_id', '=', 'building_types.id')
            ->leftJoin('building_classifications', 'building_types.building_classification_id', '=', 'building_classifications.id')
            ->whereNull('building_records.school_id')
            ->select(
                'building_records.id as assignment_id',
                'building_records.property_number',
                'building_types.name as item_name',
                'building_specs.description as sub_item_name',
                'building_classifications.name as classification',
                'building_records.acquisition_cost as asset_cost',
                'building_records.remarks as condition',
                'building_records.acquisition_date as acceptance_date'
            );

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('building_types.name', 'like', "%{$search}%")
                  ->orWhere('building_specs.description', 'like', "%{$search}%")
                  ->orWhere('building_records.property_number', 'like', "%{$search}%");
            });
        }

        return response()->json(['assets' => $query->get()]);
    }

    /**
     * Assign multiple buildings to a school
     */
    public function assignBuildingBatch(Request $request)
    {
        $validated = $request->validate([
            'assignments'                    => 'required|array|min:1',
            'assignments.*.assignment_id'    => 'required|exists:building_records,id',
            'assignments.*.school_id'        => 'required|exists:schools,id',
            'assignments.*.property_number'  => 'nullable|string|max:255',
            'assignments.*.acquisition_date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $count = 0;
            foreach ($validated['assignments'] as $data) {
                $assignment = DB::table('building_records')->where('id', $data['assignment_id'])->first();
                if ($assignment->school_id !== null) continue; // Skip if already assigned

                DB::table('building_records')->where('id', $data['assignment_id'])->update([
                    'school_id'        => $data['school_id'],
                    'property_number'  => $data['property_number'] ?? null,
                    'acquisition_date' => $data['acquisition_date'] ?? now()->toDateString(),
                    'updated_at'       => now(),
                ]);
                $count++;
            }

            $userName = Auth::user() ? Auth::user()->name : 'System';
            DB::table('system_logs')->insert([
                'user'        => $userName,
                'activity'    => "Batch Assignment: Assigned {$count} building(s).",
                'module'      => 'Buildings',
                'action_type' => 'Update',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => "Successfully assigned {$count} building(s)."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to assign buildings in batch: ' . $e->getMessage()], 500);
        }
    }

    private function resolveDocType($recipientType, $schoolType, $cost)
    {
        $isImplementingUnit = ($recipientType === 'school' && $schoolType && (stripos($schoolType, 'IMPLEMENTING UNIT') !== false));
        $isGreaterThan49k = ($cost > 49999);

        if ($recipientType === 'school') {
            if ($isImplementingUnit) {
                return $isGreaterThan49k ? 'PTR' : 'ITR';
            } else {
                return $isGreaterThan49k ? 'PAR' : 'ICS';
            }
        } else {
            // Employee or Office
            return $isGreaterThan49k ? 'PAR' : 'ICS';
        }
    }
}