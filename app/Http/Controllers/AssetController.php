<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function index()
    {
        $inventory = $this->buildInventoryData();
        return view('view-assets', compact('inventory'));
    }

    /**
     * Build inventory hierarchy from the new schema:
     * classifications -> categories -> items -> asset_sources (descriptions)
     * with distribution data from asset_distributions
     */
    private function buildInventoryData()
    {
        $inventory = [];

        $defaultIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>';

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
            ->leftJoin(DB::raw('(SELECT asrc.item_id, COUNT(ad.id) as distributed_qty FROM asset_distributions ad JOIN asset_sources asrc ON ad.asset_source_id = asrc.id GROUP BY asrc.item_id) as dist'), 'items.id', '=', 'dist.item_id')
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
        $records = DB::table('asset_distributions as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select(
                'ad.office_school_name as school_name',
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

    public function viewAll()
    {
        $categories = DB::table('categories')->orderBy('name')->pluck('name');
        $quadrants = DB::table('quadrants')->orderBy('name')->get(['id', 'name']);

        // Fetch all items with sourced quantity (replaces master_quantity)
        $allItems = DB::table('items')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin(DB::raw('(SELECT item_id, SUM(quantity) as total_qty FROM asset_sources GROUP BY item_id) as src'), 'items.id', '=', 'src.item_id')
            ->select('items.id', 'items.name', DB::raw('COALESCE(src.total_qty, 0) as master_quantity'), 'categories.name as category')
            ->orderBy('items.name')
            ->distinct()
            ->get();

        // Fetch asset_sources grouped by item (replaces sub_items)
        $allSubItems = DB::table('asset_sources')
            ->select('id', DB::raw('COALESCE(description, "General") as name'), 'item_id', 'quantity')
            ->orderBy('item_id')
            ->get()
            ->groupBy('item_id');

        // Fetch distributions (replaces ownerships)
        $allOwnerships = DB::table('asset_distributions as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->leftJoin('schools', DB::raw('CAST(ad.school_id AS CHAR)'), '=', 'schools.school_id')
            ->leftJoin('districts', 'schools.district_id', '=', 'districts.id')
            ->leftJoin('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
            ->select(
                'asrc.item_id',
                'ad.office_school_name as school_name',
                DB::raw("COALESCE(districts.name, 'N/A') as district_name"),
                DB::raw("COALESCE(quadrants.name, 'N/A') as quadrant_name"),
                DB::raw("COALESCE(asrc.description, 'General') as sub_item_name"),
                DB::raw('1 as quantity')
            )
            ->get()
            ->groupBy('item_id');

        $inventory = [];
        foreach ($allItems as $item) {
            $specs = [];
            if (isset($allSubItems[$item->id])) {
                foreach ($allSubItems[$item->id] as $sub) {
                    $specs[] = ['name' => $sub->name, 'qty' => (int) $sub->quantity];
                }
            }

            $distribution = [];
            if (isset($allOwnerships[$item->id])) {
                $schoolAgg = [];
                foreach ($allOwnerships[$item->id] as $own) {
                    $schoolKey = $own->school_name;
                    if (!isset($schoolAgg[$schoolKey])) {
                        $schoolAgg[$schoolKey] = [
                            'school' => $own->school_name,
                            'district' => $own->district_name,
                            'quadrant' => $own->quadrant_name,
                            'qty' => 0,
                        ];
                    }
                    $schoolAgg[$schoolKey]['qty'] += (int) $own->quantity;
                }
                $distribution = array_values($schoolAgg);
            }

            $inventory[] = [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category,
                'master_quantity' => (int) $item->master_quantity,
                'specs' => $specs,
                'distribution' => $distribution,
            ];
        }

        return view('assets.view-all', [
            'inventoryJson' => json_encode($inventory),
            'categoriesJson' => json_encode($categories->values()),
            'quadrantsJson' => json_encode($quadrants->pluck('name')->values()),
        ]);
    }

    public function explorer()
    {
        $inventory = $this->buildInventoryData();
        return view('assets.asset-explorer', compact('inventory'));
    }

    public function history()
    {
        // Show distribution history from asset_distributions
        $records = DB::table('asset_distributions as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select(
                'ad.id',
                'items.name as item_name',
                DB::raw('COALESCE(asrc.description, "General") as sub_item_name'),
                'categories.name as category',
                'ad.office_school_name as school',
                DB::raw("'Distributed' as district"),
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

    public function getSchoolAssets($id)
    {
        // Find the school and get its school_id string
        $school = DB::table('schools')->where('id', $id)->first();
        if (!$school) {
            return response()->json(['success' => false, 'assets' => []]);
        }

        $records = DB::table('asset_distributions as ad')
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
}