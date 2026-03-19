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

        // Define icons
        $icons = [
            'ICT Equipment' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" /></svg>',
            'Furniture' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967A8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987A8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966A8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987A8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>',
            'Science Kits' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v1.244c0 .892-.506 1.707-1.3 2.11L3.571 9.094c-.803.414-1.321 1.24-1.321 2.138v5.127c0 .937.545 1.79 1.403 2.179l5.42 2.454a2.25 2.25 0 001.754 0l5.42-2.454c.858-.389 1.403-1.242 1.403-2.179v-5.127c0-.898-.518-1.724-1.321-2.138L11.3 6.458a2.25 2.25 0 01-1.3-2.11V3.104c0-.422.355-.758.75-.758h.5c.395 0 .75.336.75.758v1.244c0 .892.506 1.707 1.3 2.11l4.879 2.54c.803.414 1.321 1.24 1.321 2.138v5.127c0 .937-.545 1.79-1.403 2.179l-5.42 2.454a2.25 2.25 0 01-1.754 0l-5.42-2.454c-.858-.389-1.403-1.242-1.403-2.179v-5.127c0-.898.518-1.724 1.321-2.138l4.879-2.54a2.25 2.25 0 001.3-2.11V3.104c0-.422-.355-.758-.75-.758h-.5c-.395 0-.75.336-.75.758z" /></svg>',
            'Sports Equip.' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252A8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983A8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" /></svg>',
            'default' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>'
        ];

        // 1. Fetch ALL categories and calculate total master quantity across items
        $allCategories = \Illuminate\Support\Facades\DB::table('categories')
            ->leftJoin('items', 'categories.id', '=', 'items.category_id')
            ->select('categories.id', 'categories.name', \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(items.master_quantity), 0) as total_assets'))
            ->groupBy('categories.id', 'categories.name')
            ->get();
            
        foreach ($allCategories as $cat) {
            $inventory[$cat->name] = [
                'icon' => $icons[$cat->name] ?? $icons['default'],
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
            
        foreach ($allItems as $item) {
            $catName = $item->category_name;
            $itemName = $item->item_name;

            if (!isset($inventory[$catName]['items'][$itemName])) {
                $inventory[$catName]['items'][$itemName] = [
                    'master_quantity' => (int) $item->master_quantity,
                    'distributed_assets' => (int) $item->distributed_quantity,
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
                'ownerships.quantity'
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
                    'status' => 'Serviceable'
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
        // Dahil ang dummy data ay nasa mismong blade file mo (gamit ang @php),
        // kailangan lang natin i-return yung view.
        return view('assets.view-all');
    }

    public function explorer()
    {
        $inventory = $this->buildInventoryData();
        return view('assets.asset-explorer', compact('inventory'));
    }

    public function history() {
    return view('assets.asset-history'); 
}

}