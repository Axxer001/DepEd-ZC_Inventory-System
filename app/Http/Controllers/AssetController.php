<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        $inventory = $this->buildInventoryData();
        return view('view-assets', compact('inventory'));
    }

    private function buildInventoryData()
    {
        $inventory = [];

        // Universal icon for all categories
        $defaultIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>';

        // 1. Fetch ALL categories and calculate total master quantity across items
        $allCategories = \Illuminate\Support\Facades\DB::table('categories')
            ->leftJoin('items', 'categories.id', '=', 'items.category_id')
            ->select('categories.id', 'categories.name', \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(items.master_quantity), 0) as total_assets'))
            ->groupBy('categories.id', 'categories.name')
            ->get();
            
        foreach ($allCategories as $cat) {
            $inventory[$cat->name] = [
                'icon' => $defaultIcon,
                'total_assets' => (int) $cat->total_assets,
                'items' => [] 
            ];
        }

        // 2. Fetch ALL items with their distributed quantities
        $allItems = \Illuminate\Support\Facades\DB::table('items')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin(\Illuminate\Support\Facades\DB::raw('(SELECT item_id, COALESCE(SUM(quantity), 0) as distributed_quantity FROM ownerships GROUP BY item_id) as dist'), 'items.id', '=', 'dist.item_id')
            ->select('items.id', 'items.name as item_name', 'categories.name as category_name', 'items.master_quantity', \Illuminate\Support\Facades\DB::raw('COALESCE(dist.distributed_quantity, 0) as distributed_quantity'))
            ->get();
            
        // 2b. Fetch sub-item available quantities grouped by item for "in_warehouse" calculation
        $subItemStocks = \Illuminate\Support\Facades\DB::table('sub_items')
            ->select('item_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as available_qty'))
            ->groupBy('item_id')
            ->pluck('available_qty', 'item_id');

        foreach ($allItems as $item) {
            $catName = $item->category_name;
            $itemName = $item->item_name;

            if (!isset($inventory[$catName]['items'][$itemName])) {
                $inventory[$catName]['items'][$itemName] = [
                    'master_quantity' => (int) $item->master_quantity,
                    'distributed_assets' => (int) $item->distributed_quantity,
                    'in_warehouse' => (int) ($subItemStocks[$item->id] ?? 0),
                    'sub_items' => []
                ];
            }
        }

        // 3. Pre-fill ALL sub-items to ensure they appear even if not distributed
        $allSubItems = \Illuminate\Support\Facades\DB::table('sub_items')
            ->join('items', 'sub_items.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select('sub_items.name as sub_item_name', 'items.name as item_name', 'categories.name as category_name')
            ->get();
            
        foreach ($allSubItems as $sub) {
            $catName = $sub->category_name;
            $itemName = $sub->item_name;
            $subName = $sub->sub_item_name;
            
            if (isset($inventory[$catName]['items'][$itemName])) {
                $inventory[$catName]['items'][$itemName]['sub_items'][$subName] = [];
            }
        }

        // 4. Query the ownerships with all relationships to fill schools and quantities
        $records = \Illuminate\Support\Facades\DB::table('ownerships')
            ->join('schools', 'ownerships.school_id', '=', 'schools.id')
            ->join('items', 'ownerships.item_id', '=', 'items.id')
            ->leftJoin('sub_items', 'ownerships.sub_item_id', '=', 'sub_items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select(
                'schools.name as school_name',
                'categories.name as category_name',
                'items.name as item_name',
                'sub_items.name as sub_item_name',
                'ownerships.quantity',
                'ownerships.condition'
            )
            ->get();

        foreach ($records as $row) {
            $cat = $row->category_name;
            $item = $row->item_name;
            $sub = $row->sub_item_name ?? 'General / Default';

            if (!isset($inventory[$cat]['items'][$item]['sub_items'][$sub])) {
                $inventory[$cat]['items'][$item]['sub_items'][$sub] = [];
            }
            
            // Check if school already exists for this subitem to accumulate quantity
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
                    'status' => $row->condition ?? 'Serviceable'
                ];
            }
        }

        return $inventory;
    }

    // Temporary method para sa dynamic categories (Mock response)
    public function getCategoriesBySchool($schoolId)
    {
        // Kunwari ito ang sagot ng database depende sa School ID
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
        // 1. Fetch all categories and quadrants for filter dropdowns
        $categories = \Illuminate\Support\Facades\DB::table('categories')->orderBy('name')->pluck('name');
        $quadrants = \Illuminate\Support\Facades\DB::table('quadrants')->orderBy('name')->get(['id', 'name']);

        // 2. Fetch all items with category info (Distinct strictly)
        $allItems = \Illuminate\Support\Facades\DB::table('items')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select('items.id', 'items.name', 'items.master_quantity', 'categories.name as category')
            ->orderBy('items.name')
            ->distinct()
            ->get();

        // 3. Fetch all sub-items grouped by item
        $allSubItems = \Illuminate\Support\Facades\DB::table('sub_items')
            ->select('id', 'name', 'item_id', 'quantity')
            ->orderBy('name')
            ->get()
            ->groupBy('item_id');

        // 4. Fetch all ownership records with school, district, quadrant info
        $allOwnerships = \Illuminate\Support\Facades\DB::table('ownerships')
            ->join('schools', 'ownerships.school_id', '=', 'schools.id')
            ->join('districts', 'schools.district_id', '=', 'districts.id')
            ->join('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
            ->leftJoin('sub_items', 'ownerships.sub_item_id', '=', 'sub_items.id')
            ->select(
                'ownerships.item_id',
                'schools.name as school_name',
                'districts.name as district_name',
                'quadrants.name as quadrant_name',
                'sub_items.name as sub_item_name',
                'ownerships.quantity'
            )
            ->get()
            ->groupBy('item_id');

        // 5. Build the inventory array for the frontend
        $inventory = [];
        foreach ($allItems as $item) {
            $specs = [];
            if (isset($allSubItems[$item->id])) {
                foreach ($allSubItems[$item->id] as $sub) {
                    $specs[] = ['name' => $sub->name, 'qty' => (int) $sub->quantity];
                }
            }

            // Build distribution: aggregate by school
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

    public function history() {
        $records = \Illuminate\Support\Facades\DB::table('ownerships')
            ->join('items', 'ownerships.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('sub_items', 'ownerships.sub_item_id', '=', 'sub_items.id')
            ->join('schools', 'ownerships.school_id', '=', 'schools.id')
            ->join('districts', 'schools.district_id', '=', 'districts.id')
            ->select(
                'ownerships.id',
                'items.name as item_name',
                'sub_items.name as sub_item_name',
                'categories.name as category',
                'schools.name as school',
                'districts.name as district',
                'ownerships.quantity as qty',
                'ownerships.created_at as distributed_at'
            )
            ->orderByDesc('ownerships.created_at')
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
        $records = \Illuminate\Support\Facades\DB::table('ownerships')
            ->join('items', 'ownerships.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('sub_items', 'ownerships.sub_item_id', '=', 'sub_items.id')
            ->where('ownerships.school_id', $id)
            ->select(
                'categories.name as category_name',
                'items.name as item_name',
                'sub_items.name as sub_item_name',
                'ownerships.quantity'
            )
            ->orderBy('categories.name')
            ->orderBy('items.name')
            ->orderBy('sub_items.name')
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