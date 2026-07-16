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
            ->leftJoin('asset_sources', function ($join) {
                $join->on('items.id', '=', 'asset_sources.item_id')
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('asset_assignments')
                            ->whereColumn('asset_assignments.asset_source_id', 'asset_sources.id');
                    });
            })
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
            ->leftJoin(DB::raw('(SELECT item_id, SUM(quantity) as sourced_qty FROM asset_sources WHERE EXISTS (SELECT 1 FROM asset_assignments WHERE asset_assignments.asset_source_id = asset_sources.id) GROUP BY item_id) as src'), 'items.id', '=', 'src.item_id')
            ->leftJoin(DB::raw('(SELECT asrc.item_id, COUNT(ad.id) as distributed_qty FROM asset_assignments ad JOIN asset_sources asrc ON ad.asset_source_id = asrc.id WHERE (ad.employee_id IS NOT NULL OR ad.school_id IS NOT NULL OR ad.office_id IS NOT NULL) GROUP BY asrc.item_id) as dist'), 'items.id', '=', 'dist.item_id')
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
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('asset_assignments')
                    ->whereColumn('asset_assignments.asset_source_id', 'asset_sources.id');
            })
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

        $records = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
            ->leftJoin('offices as o_cus', 'e.office_id', '=', 'o_cus.id')
            ->leftJoin('schools as s_cus', 'e.school_id', '=', 's_cus.id')
            ->leftJoin('offices as o_dir', 'ad.office_id', '=', 'o_dir.id')
            ->leftJoin('schools as s_dir', 'ad.school_id', '=', 's_dir.id')
            ->where(function($q) {
                $q->whereNotNull('ad.employee_id')
                  ->orWhereNotNull('ad.school_id')
                  ->orWhereNotNull('ad.office_id');
            })
            ->select(
                DB::raw('COALESCE(s_dir.name, o_dir.name, s_cus.name, o_cus.name, "Warehouse") as school_name'),
                'categories.name as category_name',
                'items.name as item_name',
                DB::raw('COALESCE(asrc.description, items.name) as sub_item_name'),
                DB::raw('1 as quantity'),
                'asrc.condition as status'
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
                    'status' => $row->status ?: 'Good Condition'
                ];
            }
        }

        return $inventory;
    }

    public function getCategoriesBySchool($schoolId)
    {
        $categories = DB::table('asset_assignments')
            ->leftJoin('employees', 'asset_assignments.employee_id', '=', 'employees.id')
            ->join('asset_sources', 'asset_assignments.asset_source_id', '=', 'asset_sources.id')
            ->join('items', 'asset_sources.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->where(function ($query) use ($schoolId) {
                $query->where('employees.school_id', $schoolId)
                      ->orWhere('asset_assignments.school_id', $schoolId);
            })
            ->distinct()
            ->pluck('categories.name');

        return response()->json($categories);
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
            ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
            ->leftJoin('offices', 'e.office_id', '=', 'offices.id')
            ->leftJoin('schools', 'e.school_id', '=', 'schools.id')
            ->select(
                'ad.id',
                'items.name as item_name',
                DB::raw('COALESCE(asrc.description, "General") as sub_item_name'),
                'categories.name as category',
                DB::raw('COALESCE(schools.name, offices.name, "Warehouse") as school'),
                DB::raw("'Assigned' as district"),
                DB::raw('1 as qty'),
                'ad.acquisition_date as distributed_at'
            )
            ->orderByDesc('ad.acquisition_date')
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
            ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
            ->leftJoin('offices', 'e.office_id', '=', 'offices.id')
            ->leftJoin('schools', 'e.school_id', '=', 'schools.id')
            ->leftJoin('procurement_modes as pm', 'asrc.procurement_mode_id', '=', 'pm.id')
            ->select(
                'ad.id',
                'ad.property_number',
                'asrc.condition',
                'ad.acquisition_date',
                'asrc.acceptance_date',
                DB::raw('COALESCE(asrc.description, items.name) as description'),
                'asrc.asset_cost',
                'asrc.quantity',
                'pm.name as mode_of_acquisition',
                'acquisition_sources.name as source_name',
                'items.name as item_name',
                'categories.name as category_name',
                DB::raw('COALESCE(schools.name, offices.name, "Warehouse") as school_name')
            )
            ->orderByDesc('ad.acquisition_date')
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
            ->leftJoin('acquisition_sources', 'asrc.acquisition_source_id', '=', 'acquisition_sources.id')
            ->leftJoin('suppliers', 'asrc.supplier_id', '=', 'suppliers.id')
            ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
            ->leftJoin('offices', function($join) {
                $join->on('e.office_id', '=', 'offices.id')
                     ->orOn('ad.office_id', '=', 'offices.id');
            })
            ->leftJoin('schools', function($join) {
                $join->on('e.school_id', '=', 'schools.id')
                     ->orOn('ad.school_id', '=', 'schools.id');
            })
            ->leftJoin('procurement_modes as pm', 'asrc.procurement_mode_id', '=', 'pm.id')
            ->select(
                'ad.id',
                'ad.property_number',
                'ad.serial_number',
                'ad.photo_path',
                'e.office_id',
                'asrc.condition',
                DB::raw("NULL as nature_of_occupancy"),
                'ad.acquisition_date',
                'ad.employee_id',
                DB::raw("'Region IX' as region"),
                DB::raw("'Division of Zamboanga City' as division"),
                DB::raw('COALESCE(schools.name, offices.name, "Warehouse") as office_school_name'),
                'offices.name as office_name',
                'schools.name as school_name',
                'asrc.id as asset_source_id',
                'asrc.acceptance_date',
                'asrc.description',
                'asrc.asset_cost',
                'asrc.quantity',
                'asrc.estimated_useful_life',
                'asrc.warranty',
                'pm.name as mode_of_acquisition',
                'acquisition_sources.name as source_name',
                DB::raw('COALESCE(asrc.contact_person, acquisition_sources.contact_person) as source_personnel'),
                'suppliers.name as supplier_name',
                DB::raw('COALESCE(asrc.supplier_service_center, suppliers.service_center) as supplier_service_center'),
                'acquisition_sources.id as acquisition_source_id',
                'items.name as item_name',
                'items.id as item_id',
                'categories.name as category_name',
                'categories.id as category_id',
                'classifications.name as classification_name',
                'classifications.id as classification_id',
                'e.first_name as custodian_first',
                'e.middle_name as custodian_middle',
                'e.last_name as custodian_last',
                'e.position as custodian_position',
                'e.employee_id as employee_id_code',
                DB::raw('NULL as custodian_contact'),
                'ad.created_at',
                'ad.origin_system_type',
                'ad.registered_by_school_id',
                'ad.school_id'
            )
            ->where('ad.id', $id)
            ->first();

        if (!$asset) {
            abort(404, 'Asset not found');
        }

        $user = auth()->user();
        if ($user && $user->isSchoolSystem()) {
            $isOwnSchoolEmployee = $asset->employee_id && DB::table('employees')->where('id', $asset->employee_id)->where('school_id', $user->school_id)->exists();
            if ($asset->school_id !== $user->school_id && !$isOwnSchoolEmployee) {
                abort(403, 'Unauthorized action.');
            }
        }

        $classifications = DB::table('classifications')->orderBy('name')->get();
        $categories = DB::table('categories')->orderBy('name')->get();
        $items = DB::table('items')->orderBy('name')->get();
        $acquisitionSources = DB::table('acquisition_sources')->orderBy('name')->get();
        $employees = DB::table('employees as e')
            ->whereNull('e.deleted_at')
            ->where('e.status', 'Active')
            ->leftJoin('schools as s', 'e.school_id', '=', 's.id')
            ->leftJoin('offices as o', 'e.office_id', '=', 'o.id')
            ->select('e.*', DB::raw('COALESCE(s.name, o.name) as location_name'))
            ->orderBy('e.first_name')
            ->get()
            ->map(function($e) {
                $e->full_name = trim($e->first_name . ' ' . ($e->middle_name ? $e->middle_name . ' ' : '') . $e->last_name);
                return $e;
            });
        $schools = DB::table('schools')->orderBy('name')->get();
        $offices = DB::table('offices')->orderBy('name')->get();

        // Generate timeline data
        $mode = $asset->mode_of_acquisition ?: 'procured';
        $supplier = $asset->supplier_name ?: 'Supplier';
        $timeline = [];
        
        // Fetch transfer history with office/school names
        // Supports both custodian-based and direct school/office locations.
        $transfers = DB::table('asset_transfers')
            ->leftJoin('users', 'asset_transfers.authorized_by', '=', 'users.id')
            ->leftJoin('employees as to_emp', 'asset_transfers.to_custodian_id', '=', 'to_emp.id')
            ->leftJoin('employees as from_emp', 'asset_transfers.from_custodian_id', '=', 'from_emp.id')
            ->leftJoin('offices as from_emp_off', 'from_emp.office_id', '=', 'from_emp_off.id')
            ->leftJoin('schools as from_emp_sch', 'from_emp.school_id', '=', 'from_emp_sch.id')
            ->leftJoin('offices as to_emp_off', 'to_emp.office_id', '=', 'to_emp_off.id')
            ->leftJoin('schools as to_emp_sch', 'to_emp.school_id', '=', 'to_emp_sch.id')
            ->leftJoin('schools as direct_from_sch', 'asset_transfers.from_school_id', '=', 'direct_from_sch.id')
            ->leftJoin('schools as direct_to_sch', 'asset_transfers.to_school_id', '=', 'direct_to_sch.id')
            ->leftJoin('offices as direct_from_off', 'asset_transfers.from_office_id', '=', 'direct_from_off.id')
            ->leftJoin('offices as direct_to_off', 'asset_transfers.to_office_id', '=', 'direct_to_off.id')
            ->where('asset_assignment_id', $id)
            ->select(
                'asset_transfers.*',
                'users.name as user_name',
                'to_emp.first_name', 'to_emp.last_name',
                DB::raw('COALESCE(direct_from_sch.name, direct_from_off.name, from_emp_sch.name, from_emp_off.name) as from_school_name'),
                DB::raw('COALESCE(direct_to_sch.name, direct_to_off.name, to_emp_sch.name, to_emp_off.name) as to_school_name')
            )
            ->orderBy('asset_transfers.transfer_date', 'desc')
            ->orderBy('asset_transfers.created_at', 'desc')
            ->get();

        // Sort transfers so that 'Initial Distribution' is always ordered logically (as the start of its custody chain)
        // even if it has a future or conflicting transfer_date.
        $transfers = $transfers->sort(function ($a, $b) {
            if ($a->transfer_type === 'Initial Distribution' && $b->transfer_type !== 'Initial Distribution') {
                if ($a->id < $b->id) {
                    return 1; // $a is older, so it goes after $b (descending)
                }
            }
            if ($b->transfer_type === 'Initial Distribution' && $a->transfer_type !== 'Initial Distribution') {
                if ($b->id < $a->id) {
                    return -1; // $b is older, so it goes after $a (descending)
                }
            }
            
            $dateA = $a->transfer_date ?? '0000-00-00';
            $dateB = $b->transfer_date ?? '0000-00-00';
            if ($dateA !== $dateB) {
                return strcmp($dateB, $dateA);
            }
            return $b->id <=> $a->id;
        })->values();
 
        if ($transfers->isEmpty() && !empty($asset->office_school_name) && $asset->office_school_name !== 'Warehouse') {
            $timeline[] = [
                'date' => $asset->acquisition_date ?? 'N/A',
                'type' => 'Transfer',
                'user' => 'Property Officer',
                'description' => 'Now assigned at/to ' . $asset->office_school_name
            ];
        } else {
            foreach ($transfers as $t) {
                $fromName = $t->from_school_name ?? 'Warehouse';
                $toName = $t->to_school_name ?? 'Warehouse';
 
                if ($t->transfer_type === 'Return') {
                    $desc = 'Returned from ' . $fromName . ' to AMU / Warehouse.';
                    if ($t->remarks) $desc .= ' Reason: ' . $t->remarks;
                } elseif ($t->transfer_type === 'Return to Supplier') {
                    $desc = 'Returned from ' . $fromName . ' to Supplier: ' . ($asset->source_name ?? 'Supplier') . '.';
                    if ($t->remarks) $desc .= ' Reason: ' . $t->remarks;
                } elseif ($t->transfer_type === 'Initial Distribution') {
                    $empName = trim(($t->first_name ?? '') . ' ' . ($t->last_name ?? ''));
                    if ($empName) {
                        $desc = 'Now assigned at/to ' . $empName;
                        if ($toName && $toName !== 'Warehouse') {
                            $desc .= ' (' . $toName . ')';
                        }
                    } else {
                        $desc = 'Now assigned at/to ' . $toName;
                    }
                } else {
                    $desc = 'Transferred from ' . $fromName . ' to ' . $toName;
                    $empName = trim(($t->first_name ?? '') . ' ' . ($t->last_name ?? ''));
                    if ($empName) $desc .= ' (Employee: ' . $empName . ')';
                    if ($t->transfer_type === 'Temporary Borrow' && $t->return_date) {
                        $desc .= '. Borrowed until: ' . \Carbon\Carbon::parse($t->return_date)->format('F d, Y');
                    }
                }
 
                $timeline[] = [
                    'date' => $t->transfer_date ? \Carbon\Carbon::parse($t->transfer_date)->format('Y-m-d') : 'N/A',
                    'type' => in_array($t->transfer_type, ['Temporary Borrow', 'Return', 'Return to Supplier', 'Initial Distribution']) ? $t->transfer_type : 'Transfer',
                    'user' => $t->user_name ?? 'Property Officer',
                    'description' => $desc
                ];
            }
        }

        // Append initial registration and delivery at the very bottom (since they are the oldest events)
        $timeline[] = [
            'date' => $asset->acceptance_date ?? 'N/A',
            'type' => 'Delivery',
            'user' => 'System Admin',
            'description' => "Delivered from {$supplier} to Asset Management Unit"
        ];
        $timeline[] = [
            'date' => $asset->acceptance_date ?? 'N/A',
            'type' => 'Procurement',
            'user' => 'System Admin',
            'description' => "Asset officially {$mode} and registered into the database from DEPED CENTRAL OFFICE"
        ];

        $documents = DB::table('asset_documents')->where('asset_distribution_id', $id)->orderByDesc('created_at')->get();

        $latestTransfer = DB::table('asset_transfers')
            ->where('asset_assignment_id', $id)
            ->orderByDesc('transfer_date')
            ->orderByDesc('created_at')
            ->first();
        if ($latestTransfer && $latestTransfer->transfer_type === 'Return to Supplier' && !$asset->employee_id) {
            $asset->office_school_name = $asset->source_name;
            $asset->is_in_source = true;
        } else {
            $asset->is_in_source = false;
        }

        return view('assets.profile', [
            'asset' => $asset,
            'timeline' => $timeline,
            'documents' => $documents,
            'classifications' => $classifications,
            'categories' => $categories,
            'items' => $items,
            'acquisitionSources' => $acquisitionSources,
            'employees' => $employees,
            'schools'   => $schools,
            'offices'   => $offices,
            'supplierHasServiceCenter' => !empty($asset->supplier_service_center),
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->approved) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'condition' => 'required|string|in:Good Condition,Needs Repair,Unserviceable',
            'property_number' => 'nullable|string|max:255',
            'asset_cost' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:1',
            'acquisition_date' => 'nullable|date',
        ]);

        $asset = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->select('ad.*', 'asrc.id as asset_source_id', 'items.category_id', 'asrc.quantity', 'asrc.unit_of_measurement', 'asrc.asset_cost', 'asrc.condition')
            ->where('ad.id', $id)
            ->first();

        if (!$asset) {
            return back()->with('error', 'Asset not found');
        }

        DB::transaction(function () use ($id, $asset, $validated, $request) {
            // Resolve Item
            $itemName = strtoupper(trim($validated['item_name']));
            if (!$itemName) $itemName = 'UNKNOWN ITEM';

            $item = DB::table('items')
                ->where('name', $itemName)
                ->where('category_id', $asset->category_id)
                ->first();
                
            $finalItemId = $item ? $item->id : DB::table('items')->insertGetId([
                'category_id' => $asset->category_id,
                'name' => $itemName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update Asset Source
            $sourceUpdates = [
                'item_id' => $finalItemId,
                'description' => $validated['description'],
                'condition' => $validated['condition'],
                'asset_cost' => $validated['asset_cost'] ?? 0.00,
                'updated_at' => now(),
            ];
            
            if (empty($asset->quantity) && isset($validated['quantity'])) {
                $sourceUpdates['quantity'] = $validated['quantity'];
                $finalQty = (int)$validated['quantity'];
            } else {
                $finalQty = (int)($asset->quantity ?? 1);
            }

            DB::table('asset_sources')->where('id', $asset->asset_source_id)->update($sourceUpdates);

            // Update Asset Assignment
            $assignmentUpdates = [
                'acquisition_cost' => (float)($validated['asset_cost'] ?? 0.00) * $finalQty,
            ];
            if (empty($asset->property_number) && isset($validated['property_number'])) {
                $assignmentUpdates['property_number'] = $validated['property_number'];
            }
            if (empty($asset->acquisition_date) && isset($validated['acquisition_date'])) {
                $assignmentUpdates['acquisition_date'] = $validated['acquisition_date'];
            }

            $assignmentUpdates['updated_at'] = now();
            DB::table('asset_assignments')->where('id', $id)->update($assignmentUpdates);
            
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            
            // Log the change
            DB::table('system_logs')->insert([
                'user' => $user ? $user->name : 'System',
                'action_type' => 'UPDATE',
                'module' => 'Assets',
                'activity' => 'Updated specifications for asset ID ' . $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $propNoStr = $asset->property_number ? '[' . $asset->property_number . '] ' : '';
            $empName = 'Unassigned';
            if ($asset->employee_id) {
                $empName = DB::table('employees')->where('id', $asset->employee_id)->value(DB::raw("CONCAT(first_name, ' ', last_name)")) ?: 'Unassigned';
            }
            $qty = $asset->quantity ?? 1;
            $uom = $asset->unit_of_measurement ?? 'Unit';

            $detailedMessage = "Edited {$qty} {$uom} {$propNoStr}{$itemName} assigned to {$empName}.";

            $schoolId = $asset->school_id;
            if (!$schoolId && $asset->employee_id) {
                $schoolId = DB::table('employees')->where('id', $asset->employee_id)->value('school_id');
            }
            $admins = \App\Models\User::getNotificationRecipients($schoolId);
            $dummyAsset = (object)[
                'title' => 'Asset Updated',
                'message' => 'An asset has been updated.',
                'detailed_message' => $detailedMessage
            ];
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AssetUpdatedNotification($dummyAsset));
            }
        });

        return back()->with('success', 'Asset specifications updated successfully!');
    }

    public function transfer(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->approved) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'school_db_id' => 'nullable|integer',
            'is_office' => 'nullable|boolean',
            'transfer_date' => 'nullable|date',
            'transfer_type' => 'nullable|string|max:255',
            'condition' => 'required|string|in:Good Condition,Needs Repair,Unserviceable',
            'return_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $asset = DB::table('asset_assignments')->where('id', $id)->first();
        if (!$asset) {
            return back()->with('error', 'Asset not found');
        }

        $user = auth()->user();
        if ($user && $user->isSchoolSystem()) {
            if ($asset->school_id !== $user->school_id) {
                $isOwnSchoolEmployee = $asset->employee_id && DB::table('employees')->where('id', $asset->employee_id)->where('school_id', $user->school_id)->exists();
                if ($asset->school_id !== $user->school_id && !$isOwnSchoolEmployee) {
                    abort(403, 'Unauthorized action.');
                }
            }

            if (empty($validated['employee_id'])) {
                return back()->with('error', 'School accounts must assign assets to an employee.');
            }
            $targetEmployee = DB::table('employees')->where('id', $validated['employee_id'])->first();
            if (!$targetEmployee || $targetEmployee->school_id !== $user->school_id) {
                return back()->with('error', 'Cannot transfer asset: employee must belong to your school.');
            }
            $validated['school_db_id'] = null;
            $validated['is_office'] = null;
        }

        $transferId = DB::transaction(function () use ($id, $asset, $validated, $request) {
            // Resolve current office_id/school_id from the previous assignment/employee
            $currentOfficeId = $asset->office_id;
            $currentSchoolId = $asset->school_id;
            if ($asset->employee_id) {
                $currentEmployee = DB::table('employees')->where('id', $asset->employee_id)->first();
                if ($currentEmployee) {
                    $currentOfficeId = $currentOfficeId ?? $currentEmployee->office_id;
                    $currentSchoolId = $currentSchoolId ?? $currentEmployee->school_id;
                }
            }

            // Update Asset Assignment
            $updateData = [
                'employee_id' => $validated['employee_id'] ?? null,
                'school_id' => null,
                'office_id' => null,
                'acquisition_date' => $validated['transfer_date'] ?? now()->toDateString(),
                'updated_at' => now(),
            ];

            if (empty($validated['employee_id']) && !empty($validated['school_db_id'])) {
                if (!empty($validated['is_office'])) {
                    $updateData['office_id'] = $validated['school_db_id'];
                } else {
                    $updateData['school_id'] = $validated['school_db_id'];
                }
            }

            DB::table('asset_assignments')->where('id', $id)->update($updateData);

            // Update Asset Source Condition
            DB::table('asset_sources')->where('id', $asset->asset_source_id)->update([
                'condition' => $validated['condition'],
                'updated_at' => now(),
            ]);

            // Resolve target details for transfer log
            $targetEmployee = null;
            $toOfficeId = null;
            $toSchoolId = null;
            $toCustodianId = null;
            $toName = 'Directly to School/Office';

            if (!empty($validated['employee_id'])) {
                $targetEmployee = DB::table('employees')->where('id', $validated['employee_id'])->first();
                $toOfficeId = $targetEmployee->office_id;
                $toSchoolId = $targetEmployee->school_id;
                $toCustodianId = $validated['employee_id'];
                $toName = ($targetEmployee->first_name . ' ' . $targetEmployee->last_name);
            } else {
                if (!empty($validated['school_db_id'])) {
                    if (!empty($validated['is_office'])) {
                        $toOfficeId = $validated['school_db_id'];
                        $toName = DB::table('offices')->where('id', $validated['school_db_id'])->value('name') ?? 'Office';
                    } else {
                        $toSchoolId = $validated['school_db_id'];
                        $toName = DB::table('schools')->where('id', $validated['school_db_id'])->value('name') ?? 'School';
                    }
                }
            }

            // Log Transfer
            $tid = DB::table('asset_transfers')->insertGetId([
                'asset_assignment_id' => $id,
                'from_office_id' => $currentOfficeId,
                'to_office_id' => $toOfficeId,
                'from_school_id' => $currentSchoolId,
                'to_school_id' => $toSchoolId,
                'from_custodian_id' => $asset->employee_id,
                'to_custodian_id' => $toCustodianId,
                'transfer_date' => $validated['transfer_date'] ?? now(),
                'return_date' => $validated['return_date'] ?? null,
                'transfer_type' => $validated['transfer_type'] ?? 'Permanent Reassignment',
                'remarks' => $validated['remarks'] ?? null,
                'authorized_by' => Auth::id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $source = DB::table('asset_sources')->where('id', $asset->asset_source_id)->first();
            $itemName = DB::table('items')->where('id', $source->item_id)->value('name');
            $uom = $source->unit_of_measurement ?? 'Unit';
            $qty = $source->quantity ?? 1;
            $propNoStr = $asset->property_number ? '[' . $asset->property_number . '] ' : '';
            $detailedMessage = "Transferred {$qty} {$uom} {$propNoStr}{$itemName} to {$toName}.";

            $schoolIds = collect([
                $currentSchoolId,
                $toSchoolId
            ])->filter()->unique()->toArray();

            $admins = collect();
            foreach ($schoolIds as $sid) {
                $admins = $admins->merge(\App\Models\User::getNotificationRecipients($sid));
            }
            if (empty($schoolIds)) {
                $admins = $admins->merge(\App\Models\User::getNotificationRecipients(null));
            }
            $admins = $admins->unique('id');

            $dummyAsset = (object)[
                'title' => 'Asset Transferred',
                'message' => 'An asset has been transferred.',
                'detailed_message' => $detailedMessage
            ];
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AssetTransferNotification($dummyAsset));
            }

            return $tid;
        });

        // Flash document download if transfer is employee to employee
        $cost = DB::table('asset_sources')->where('id', $asset->asset_source_id)->value('asset_cost') ?? 0;
        if (!empty($asset->employee_id) && !empty($validated['employee_id'])) {
            $targetEmployee = DB::table('employees')->where('id', $validated['employee_id'])->first();
            $toName = $targetEmployee ? trim($targetEmployee->first_name . ' ' . $targetEmployee->last_name) : 'Custodian';
            $docType = ($cost > 49999) ? 'PTR' : 'ITR';
            
            session()->flash('download_docs', [
                [
                    'recipient_name' => $toName,
                    'recipient_type' => 'employee',
                    'school_type'    => null,
                    'cost_threshold' => ($cost > 49999) ? 'high' : 'low',
                    'doc_type'       => $docType,
                    'asset_count'    => 1,
                    'assignment_id'  => $id,
                    'transfer_id'    => $transferId,
                ]
            ]);
        }

        return back()->with('success', 'Asset successfully transferred!');
    }

    public function returnAmu(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->approved) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'return_date' => 'required|date',
            'condition' => 'required|string|in:Good Condition,Needs Repair,Unserviceable',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $asset = DB::table('asset_assignments')->where('id', $id)->first();
        if (!$asset) {
            return back()->with('error', 'Asset not found');
        }

        $transferId = DB::transaction(function () use ($id, $asset, $validated) {
            // Resolve current office_id/school_id from the previous assignment/employee
            $currentOfficeId = $asset->office_id;
            $currentSchoolId = $asset->school_id;
            if ($asset->employee_id) {
                $currentEmployee = DB::table('employees')->where('id', $asset->employee_id)->first();
                if ($currentEmployee) {
                    $currentOfficeId = $currentOfficeId ?? $currentEmployee->office_id;
                    $currentSchoolId = $currentSchoolId ?? $currentEmployee->school_id;
                }
            }

            // Log the return
            $tid = DB::table('asset_transfers')->insertGetId([
                'asset_assignment_id' => $id,
                'from_office_id' => $currentOfficeId,
                'to_office_id' => null,
                'from_school_id' => $currentSchoolId,
                'to_school_id' => null,
                'from_custodian_id' => $asset->employee_id,
                'to_custodian_id' => null,
                'transfer_date' => $validated['return_date'],
                'transfer_type' => 'Return',
                'remarks' => $validated['remarks'],
                'authorized_by' => Auth::id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $source = DB::table('asset_sources')->where('id', $asset->asset_source_id)->first();

            // Nullify employee_id, school_id, and office_id to place back in Warehouse
            DB::table('asset_assignments')->where('id', $id)->update([
                'employee_id' => null,
                'school_id' => null,
                'office_id' => null,
                'acquisition_date' => $source->acceptance_date ?? now()->toDateString(),
                'updated_at' => now(),
            ]);

            // Update condition in asset_sources
            DB::table('asset_sources')->where('id', $asset->asset_source_id)->update([
                'condition' => $validated['condition'],
                'updated_at' => now(),
            ]);

            $itemName = DB::table('items')->where('id', $source->item_id)->value('name');
            $uom = $source->unit_of_measurement ?? 'Unit';
            $qty = $source->quantity ?? 1;
            $propNoStr = $asset->property_number ? '[' . $asset->property_number . '] ' : '';
            $detailedMessage = "Returned {$qty} {$uom} {$propNoStr}{$itemName} to AMU / Warehouse.";

            $admins = \App\Models\User::getNotificationRecipients($currentSchoolId);
            $dummyAsset = (object)[
                'title' => 'Asset Returned',
                'message' => 'An asset has been returned to AMU.',
                'detailed_message' => $detailedMessage
            ];
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AssetReturnedNotification($dummyAsset));
            }

            return $tid;
        });

        // Flash document download for return to AMU
        $cost = DB::table('asset_sources')->where('id', $asset->asset_source_id)->value('asset_cost') ?? 0;
        $recipientName = 'Warehouse';
        $recipientType = 'warehouse';
        if ($asset->employee_id) {
            $emp = DB::table('employees')->where('id', $asset->employee_id)->first();
            $recipientName = $emp ? trim($emp->first_name . ' ' . $emp->last_name) : 'Custodian';
            $recipientType = 'employee';
        } elseif ($asset->school_id) {
            $recipientName = DB::table('schools')->where('id', $asset->school_id)->value('name') ?? 'School';
            $recipientType = 'school';
        } elseif ($asset->office_id) {
            $recipientName = DB::table('offices')->where('id', $asset->office_id)->value('name') ?? 'Office';
            $recipientType = 'office';
        }

        $docType = ($cost > 49999) ? 'RRPPE' : 'RRSP';

        session()->flash('download_docs', [
            [
                'recipient_name' => $recipientName,
                'recipient_type' => $recipientType,
                'school_type'    => null,
                'cost_threshold' => ($cost > 49999) ? 'high' : 'low',
                'doc_type'       => $docType,
                'asset_count'    => 1,
                'assignment_id'  => $id,
                'transfer_id'    => $transferId,
            ]
        ]);

        return redirect()->route('assets.profile', $id)->with('success', 'Asset successfully returned to AMU / Warehouse!');
    }

    public function returnSource(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->approved) {
            abort(403, 'Unauthorized action.');
        }

        $user = auth()->user();
        if ($user && $user->isSchoolSystem()) {
            abort(403, 'Unauthorized action. School accounts cannot return assets to supplier.');
        }

        $validated = $request->validate([
            'return_date'          => 'required|date',
            'condition'            => 'required|string|in:Good Condition,Needs Repair,Unserviceable',
            'remarks'              => 'nullable|string|max:1000',
            'expected_return_date' => 'nullable|date|after_or_equal:return_date',
        ]);

        $asset = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('acquisition_sources', 'asrc.acquisition_source_id', '=', 'acquisition_sources.id')
            ->leftJoin('suppliers', 'asrc.supplier_id', '=', 'suppliers.id')
            ->where('ad.id', $id)
            ->select(
                'ad.*',
                'asrc.warranty', 'asrc.acceptance_date', 'asrc.supplier_id',
                'acquisition_sources.name as source_name',
                'suppliers.name as supplier_name',
                'suppliers.service_center',
                'asrc.id as asset_source_id'
            )
            ->first();

        if (!$asset) {
            return back()->with('error', 'Asset not found');
        }

        // Warranty validation: CANNOT be returned to source if warranty is expired OR has no warranty, AND condition is Needs Repair
        $warrantyMonths = $asset->warranty ?? 0;
        $startDate      = $asset->acceptance_date ? \Carbon\Carbon::parse($asset->acceptance_date) : null;
        $hasNoWarranty  = ($warrantyMonths <= 0 || !$startDate);
        $isExpired      = false;
        if (!$hasNoWarranty && $startDate) {
            $warrantyEndDate = $startDate->copy()->addMonths($warrantyMonths);
            $isExpired       = now()->greaterThanOrEqualTo($warrantyEndDate);
        }

        if (($hasNoWarranty || $isExpired) && $validated['condition'] === 'Needs Repair') {
            return back()->with('error', 'Unable to initiate Return to Supplier: This item requires repair, but its warranty has expired or is unavailable.');
        }

        // Qualify for repair tracking?
        $qualifiesForService = (
            $validated['condition'] === 'Needs Repair' &&
            !empty($asset->service_center) &&
            !$hasNoWarranty &&
            !$isExpired
        );

        if ($qualifiesForService && empty($validated['expected_return_date'])) {
            return back()
                ->withErrors(['expected_return_date' => 'Expected Return Date is required when sending an asset for repair to a service center.'])
                ->withInput();
        }

        // Capture previous custodian before nullifying
        $previousCustodianId = $asset->employee_id;

        DB::transaction(function () use ($id, $asset, $validated, $qualifiesForService, $previousCustodianId) {
            // Resolve current office_id from the employee
            $currentOfficeId = null;
            if ($asset->employee_id) {
                $currentEmployee = DB::table('employees')->where('id', $asset->employee_id)->first();
                if ($currentEmployee) {
                    $currentOfficeId = $currentEmployee->office_id;
                }
            }

            // Log the return to source transfer
            DB::table('asset_transfers')->insert([
                'asset_assignment_id' => $id,
                'from_office_id'      => $currentOfficeId,
                'to_office_id'        => null,
                'from_custodian_id'   => $asset->employee_id,
                'to_custodian_id'     => null,
                'transfer_date'       => $validated['return_date'],
                'transfer_type'       => 'Return to Supplier',
                'remarks'             => $validated['remarks'],
                'authorized_by'       => Auth::id() ?? 1,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            // Nullify employee_id on asset_assignments
            DB::table('asset_assignments')->where('id', $id)->update([
                'employee_id' => null,
                'updated_at'  => now(),
            ]);

            // Update condition in asset_sources
            DB::table('asset_sources')->where('id', $asset->asset_source_id)->update([
                'condition'  => $validated['condition'],
                'updated_at' => now(),
            ]);

            // Create repair tracking record if eligible
            if ($qualifiesForService) {
                DB::table('asset_services')->insert([
                    'asset_source_id'       => $asset->asset_source_id,
                    'asset_assignment_id'   => $id,
                    'supplier_id'           => $asset->supplier_id,
                    'previous_custodian_id' => $previousCustodianId,
                    'expected_return_date'  => $validated['expected_return_date'],
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }

            $source   = DB::table('asset_sources')->where('id', $asset->asset_source_id)->first();
            $itemName = DB::table('items')->where('id', $source->item_id)->value('name');
            $uom      = $source->unit_of_measurement ?? 'Unit';
            $qty      = $source->quantity ?? 1;
            $propNoStr  = $asset->property_number ? '[' . $asset->property_number . '] ' : '';
            $supName    = $asset->supplier_name ?? $asset->source_name ?? 'Supplier';
            $detailedMessage = "Returned {$qty} {$uom} {$propNoStr}{$itemName} to Supplier: {$supName}.";
            if ($qualifiesForService) {
                $detailedMessage .= " Repair expected by: " . \Carbon\Carbon::parse($validated['expected_return_date'])->format('M d, Y') . ".";
            }

            $schoolId = $asset->school_id;
            if (!$schoolId && $asset->employee_id) {
                $schoolId = DB::table('employees')->where('id', $asset->employee_id)->value('school_id');
            }
            $admins = \App\Models\User::getNotificationRecipients($schoolId);
            $dummyAsset = (object)[
                'title'            => 'Asset Returned to Supplier',
                'message'          => "An asset has been returned to its supplier: {$supName}.",
                'detailed_message' => $detailedMessage,
            ];
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AssetReturnedNotification($dummyAsset));
            }
        });

        $successMsg = 'Asset successfully returned to Supplier!';
        if ($qualifiesForService) {
            $successMsg .= ' It has been added to Asset Service for repair tracking.';
        }

        return redirect()->route('assets.profile', $id)->with('success', $successMsg);
    }

    public function getSchoolAssets($id)
    {
        $records = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
            ->where(function ($query) use ($id) {
                $query->where('e.school_id', $id)
                      ->orWhere('ad.school_id', $id);
            })
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

    public function destroy($id)
    {
        $assignment = \App\Models\AssetAssignment::findOrFail($id);
        $user = auth()->user();

        // Access check
        if ($user->isSchoolSystem()) {
            if ($assignment->school_id !== $user->school_id) {
                $isOwnSchoolEmployee = $assignment->employee_id && DB::table('employees')->where('id', $assignment->employee_id)->where('school_id', $user->school_id)->exists();
                if ($assignment->school_id !== $user->school_id && !$isOwnSchoolEmployee) {
                    abort(403, 'Unauthorized action.');
                }
            }

            // Must be self-registered
            if ($assignment->origin_system_type !== 'school' || $assignment->registered_by_school_id !== $user->school_id) {
                abort(403, 'Unauthorized action.');
            }

            // Limited deletion window: same-day only (created today)
            if (!$assignment->created_at->isToday()) {
                return back()->with('error', 'Same-day deletion window has expired for this asset.');
            }

            // Check if transferred
            $hasTransfers = DB::table('asset_transfers')->where('asset_assignment_id', $assignment->id)->exists();
            if ($hasTransfers) {
                return back()->with('error', 'Cannot delete asset: asset has transfer history.');
            }

            // Check if serviced
            $hasServices = DB::table('asset_services')->where('asset_assignment_id', $assignment->id)->exists();
            if ($hasServices) {
                return back()->with('error', 'Cannot delete asset: asset has repair/service history.');
            }
        }

        $assignment->delete();

        // Log the action to system_logs
        DB::table('system_logs')->insert([
            'user' => $user ? $user->name : 'System',
            'action_type' => 'Delete',
            'module' => 'Assets',
            'activity' => "Asset assignment ID {$assignment->id} (Property: {$assignment->property_number}) was soft-deleted.",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('assets.view')->with('success', 'Asset successfully archived.');
    }
}
