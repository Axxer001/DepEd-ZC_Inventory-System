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
            ->leftJoin(DB::raw('(SELECT asrc.item_id, COUNT(ad.id) as distributed_qty FROM asset_assignments ad JOIN asset_sources asrc ON ad.asset_source_id = asrc.id GROUP BY asrc.item_id) as dist'), 'items.id', '=', 'dist.item_id')
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
            ->leftJoin('offices', 'ad.office_id', '=', 'offices.id')
            ->leftJoin('schools', 'offices.school_id', '=', 'schools.id')
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
            ->leftJoin('offices', 'ad.office_id', '=', 'offices.id')
            ->leftJoin('schools', 'offices.school_id', '=', 'schools.id')
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
            ->leftJoin('offices', 'ad.office_id', '=', 'offices.id')
            ->leftJoin('schools', 'offices.school_id', '=', 'schools.id')
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
            ->leftJoin('offices', 'ad.office_id', '=', 'offices.id')
            ->leftJoin('schools', 'offices.school_id', '=', 'schools.id')
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
                'asrc.mode_of_acquisition',
                'acquisition_sources.name as source_name',
                'items.name as item_name',
                'categories.name as category_name',
                DB::raw('COALESCE(schools.name, offices.name, ad.location) as school_name')
            )
            ->orderByDesc('ad.created_at')
            ->get();

        $mappedAssets = $assets->map(function ($a) {
            return [
                'id' => $a->id,
                'property_number' => $a->property_number ?? 'N/A',
                'item_name' => $a->item_name,
                'description' => $a->description,
                'category_name' => $a->category_name,
                'school_name' => $a->school_name,
                'source_name' => $a->source_name,
                'mode_of_acquisition' => $a->mode_of_acquisition,
                'cost' => (float) $a->asset_cost,
                'acceptance_date' => $a->acceptance_date,
                'acquisition_date' => $a->acquisition_date,
            ];
        });

        return view('assets.lifecycle', [
            'assetsJson' => json_encode($mappedAssets->values())
        ]);
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
            ->leftJoin('offices', 'ad.office_id', '=', 'offices.id')
            ->leftJoin('schools', 'offices.school_id', '=', 'schools.id')
            ->select(
                'ad.id',
                'ad.property_number',
                'ad.photo_path',
                'ad.office_id',
                'ad.condition',
                'ad.nature_of_occupancy',
                'ad.location',
                'ad.acquisition_date',
                'ad.custodian_id',
                DB::raw('COALESCE(schools.name, offices.name, ad.location) as office_school_name'),
                'offices.name as office_name',
                'schools.name as school_name',
                'asrc.id as asset_source_id',
                'asrc.acceptance_date',
                'asrc.description',
                'asrc.asset_cost',
                'asrc.quantity',
                'asrc.mode_of_acquisition',
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
            ],
            [
                'date' => $asset->acquisition_date ?? 'N/A',
                'type' => 'Transfer',
                'user' => 'Property Officer',
                'description' => 'Deployed and assigned to ' . ($asset->office_school_name ?? 'Unknown')
            ]
        ];

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

        DB::transaction(function () use ($id, $asset, $validated) {

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
            $custodianInput = $validated['custodian_id'] ?? null;
            $finalCustodianId = $asset->custodian_id;

            if ($custodianInput) {
                if (is_numeric($custodianInput) && DB::table('custodians')->where('id', $custodianInput)->exists()) {
                    $finalCustodianId = $custodianInput;
                    DB::table('custodians')->where('id', $finalCustodianId)->update([
                        'position' => $validated['custodian_position'] ?? null,
                        'contact_number' => $validated['custodian_contact'] ?? null,
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
            }

            // 5. Update Asset Assignment
            $distUpdate = ['updated_at' => now()];
            if (array_key_exists('property_number', $validated)) $distUpdate['property_number'] = $validated['property_number'];
            if ($finalCustodianId) $distUpdate['custodian_id'] = $finalCustodianId;

            DB::table('asset_assignments')->where('id', $id)->update($distUpdate);

            // 6. Update Asset Source (Description, Item link, etc.)
            DB::table('asset_sources')->where('id', $asset->asset_source_id)->update([
                'item_id' => $finalItemId,
                'description' => $validated['description'],
                'quantity' => $validated['quantity'],
                'asset_cost' => $validated['asset_cost'],
                'acquisition_source_id' => $validated['acquisition_source_id'],
                'mode_of_acquisition' => $validated['mode_of_acquisition'],
                'updated_at' => now(),
            ]);
            
            // Log the change
            DB::table('system_logs')->insert([
                'user' => Auth::user()->name ?? 'System',
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
            'remarks' => 'nullable|string|max:1000',
        ]);

        $asset = DB::table('asset_assignments')->where('id', $id)->first();
        if (!$asset) {
            return back()->with('error', 'Asset not found');
        }

        DB::transaction(function () use ($id, $asset, $validated) {
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

            // Update Asset Assignment
            DB::table('asset_assignments')->where('id', $id)->update([
                'office_school_type' => $validated['office_school_type'] ?? '',
                'school_id' => $validated['school_id'] ?? '',
                'nature_of_occupancy' => $validated['nature_of_occupancy'] ?? '',
                'location' => $validated['location'] ?? '',
                'custodian_id' => $finalCustodianId ?: $asset->custodian_id,
                'updated_at' => now(),
            ]);

            // Log Transfer
            DB::table('asset_transfers')->insert([
                'asset_assignment_id' => $id,
                'from_custodian_id' => $asset->custodian_id,
                'to_custodian_id' => $finalCustodianId,
                'transfer_date' => $validated['transfer_date'] ?? now(),
                'transfer_type' => $validated['transfer_type'] ?? 'Permanent',
                'remarks' => $validated['remarks'] ?? null,
                'authorized_by' => Auth::id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Asset successfully transferred!');
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
            ->where('ad.school_id', $school->school_id)
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
        $doc = DB::table('asset_documents')->where('id', $docId)->first();
        if ($doc) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->file_path);
            DB::table('asset_documents')->where('id', $docId)->delete();
            return back()->with('success', 'Document removed successfully!');
        }
        return back()->with('error', 'Document not found.');
    }
}
