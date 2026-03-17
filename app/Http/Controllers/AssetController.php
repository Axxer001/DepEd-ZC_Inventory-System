<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        $inventory = [];

        // Define some default icons for known categories, and a fallback icon
        $icons = [
            'ICT Equipment' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" /></svg>',
            'Furniture' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>',
            'Science Kits' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v1.244c0 .892-.506 1.707-1.3 2.11L3.571 9.094c-.803.414-1.321 1.24-1.321 2.138v5.127c0 .937.545 1.79 1.403 2.179l5.42 2.454a2.25 2.25 0 001.754 0l5.42-2.454c.858-.389 1.403-1.242 1.403-2.179v-5.127c0-.898-.518-1.724-1.321-2.138L11.3 6.458a2.25 2.25 0 01-1.3-2.11V3.104c0-.422.355-.758.75-.758h.5c.395 0 .75.336.75.758v1.244c0 .892.506 1.707 1.3 2.11l4.879 2.54c.803.414 1.321 1.24 1.321 2.138v5.127c0 .937-.545 1.79-1.403 2.179l-5.42 2.454a2.25 2.25 0 01-1.754 0l-5.42-2.454c-.858-.389-1.403-1.242-1.403-2.179v-5.127c0-.898.518-1.724 1.321-2.138l4.879-2.54a2.25 2.25 0 001.3-2.11V3.104c0-.422-.355-.758-.75-.758h-.5c-.395 0-.75.336-.75.758z" /></svg>',
            'Sports Equip.' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" /></svg>',
            'default' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>'
        ];

        // 1. Fetch ALL categories to ensure they appear even if empty
        $allCategories = \Illuminate\Support\Facades\DB::table('categories')->get();
        foreach ($allCategories as $cat) {
            $inventory[$cat->name] = [
                'icon' => $icons[$cat->name] ?? $icons['default'],
                'items' => (object)[] // Force empty object so AlpineJS sees {} instead of []
            ];
        }

        // 2. Fetch ALL items to ensure they appear under their categories
        $allItems = \Illuminate\Support\Facades\DB::table('items')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select('items.name as item_name', 'categories.name as category_name')
            ->get();
            
        foreach ($allItems as $item) {
            $catName = $item->category_name;
            $itemName = $item->item_name;

            // Convert back to array if it was cast to object
            if (is_object($inventory[$catName]['items'])) {
                $inventory[$catName]['items'] = [];
            }
            if (!isset($inventory[$catName]['items'][$itemName])) {
                $inventory[$catName]['items'][$itemName] = (object)[];
            }
        }

        // 3. Query the ownerships with all relationships
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
            // If sub_item_id is null, group as 'General'
            $sub = $row->sub_item_name ?? 'General / Default';

            if (is_object($inventory[$cat]['items'])) {
                $inventory[$cat]['items'] = [];
            }
            if (is_object($inventory[$cat]['items'][$item])) {
                $inventory[$cat]['items'][$item] = [];
            }

            if (!isset($inventory[$cat]['items'][$item][$sub])) {
                $inventory[$cat]['items'][$item][$sub] = [];
            }
            
            // Check if school already exists for this subitem to accumulate quantity
            $existingSchoolIndex = null;
            foreach ($inventory[$cat]['items'][$item][$sub] as $index => $schoolEntry) {
                if ($schoolEntry['name'] === $row->school_name) {
                    $existingSchoolIndex = $index;
                    break;
                }
            }

            if ($existingSchoolIndex !== null) {
                $inventory[$cat]['items'][$item][$sub][$existingSchoolIndex]['qty'] += $row->quantity;
            } else {
                $inventory[$cat]['items'][$item][$sub][] = [
                    'name' => $row->school_name,
                    'qty' => $row->quantity,
                    'status' => 'Serviceable' // Defaulting to serviceable for now
                ];
            }
        }

        return view('view-assets', compact('inventory'));
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
}