<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventorySetupController extends Controller
{
    public function storeSchool(Request $request)
    {
        $request->validate([
            'school_id' => 'required|string|unique:schools,school_id',
            'name' => 'required|string|max:255',
            'district_id' => 'required|exists:districts,id',
        ]);

        DB::table('schools')->insert([
            'school_id' => $request->school_id,
            'name' => $request->name,
            'district_id' => $request->district_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "Successfully added '{$request->name}' in the system.");
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $existingCategoryId = $request->existing_category_id;
        $categoryName = trim($request->name);

        if ($existingCategoryId) {
            // Verify existing
            $existingCategory = DB::table('categories')->where('id', $existingCategoryId)->first();
            if (!$existingCategory) {
                return back()->withErrors(['name' => 'The selected category does not exist.']);
            }
            return back()->with('success', "Category '{$existingCategory->name}' already exists — no changes made.");
        } else {
            // Check for duplicate case-insensitively
            $duplicate = DB::table('categories')
                ->whereRaw('LOWER(name) = ?', [strtolower($categoryName)])
                ->first();

            if ($duplicate) {
                return back()->withErrors(['name' => "The category '{$categoryName}' already exists in the system. Please use the dropdown to select it instead."])->withInput();
            } else {
                DB::table('categories')->insert([
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

                return back()->with('success', "Category '{$categoryName}' has been added successfully.");
            }
        }
    }

    /**
     * MODULE 1: MASTER REGISTRY — Register or update an item in the master list.
     */
    public function storeItem(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'category_name' => 'nullable|string|max:255',
            'item_name' => 'required|string|max:255',
            'master_quantity' => 'required|integer|min:1',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $existingItemId = $request->existing_item_id;
        $itemName = trim($request->item_name);
        $masterQty = (int) $request->master_quantity;

        $categoryId = $request->category_id;
        $categoryName = trim($request->category_name);
        $messages = [];

        // Resolve category
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

        // Resolve item
        if ($existingItemId) {
            $existingItem = DB::table('items')->where('id', $existingItemId)->first();
            if (!$existingItem) {
                return back()->withErrors(['item_name' => 'The selected item does not exist.']);
            }
            $itemId = $existingItem->id;
            // Update master_quantity
            DB::table('items')->where('id', $itemId)->update([
                'master_quantity' => DB::raw("master_quantity + {$masterQty}"),
                'updated_at' => now(),
            ]);
            DB::table('system_logs')->insert([
                'user' => $userName,
                'activity' => "Updated master quantity of '{$itemName}' by +{$masterQty}",
                'module' => 'Items',
                'action_type' => 'Update',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $messages[] = "Item '{$itemName}' master quantity updated by +{$masterQty}";
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
                'master_quantity' => $masterQty,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('system_logs')->insert([
                'user' => $userName,
                'activity' => "Added new item: {$itemName} (Master Qty: {$masterQty})",
                'module' => 'Items',
                'action_type' => 'Create',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $messages[] = "Item '{$itemName}' registered with master quantity of {$masterQty}";
        }

        // Process new sub-items
        $subItems = $request->input('sub_items', []);
        $subItems = array_filter(array_map('trim', $subItems));

        foreach ($subItems as $subItemName) {
            DB::table('sub_items')->insertGetId([
                'name' => $subItemName,
                'item_id' => $itemId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('system_logs')->insert([
                'user' => $userName,
                'activity' => "Added sub-item '{$subItemName}' under item '{$itemName}'",
                'module' => 'Items',
                'action_type' => 'Create',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $messages[] = "Sub-item '{$subItemName}' added";
        }

        if (empty($messages)) {
            $messages[] = "Item '{$itemName}' already exists — no changes made";
        }

        return back()->with('success', implode('. ', $messages) . '.');
    }

    /**
     * MODULE 2: ASSET DISTRIBUTION — Allocate items from the Master Registry to schools.
     */
    public function storeDistribution(Request $request)
    {
        $request->validate([
            'dist_item_id' => 'required|exists:items,id',
            'school_ids' => 'required|array|min:1',
            'school_ids.*' => 'exists:schools,id',
            'dist_sub_items' => 'required|array|min:1',
            'dist_sub_items.*' => 'integer|min:1',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $itemId = $request->dist_item_id;
        $schoolIds = $request->school_ids;
        $distSubItems = $request->dist_sub_items; // [ sub_item_id => quantity ]

        $item = DB::table('items')->where('id', $itemId)->first();
        if (!$item) {
            return back()->withErrors(['dist_item_id' => 'Item not found.']);
        }

        // Validate total quantity against remaining stock
        $distributedQty = DB::table('ownerships')->where('item_id', $itemId)->sum('quantity');
        $remainingStock = max(0, $item->master_quantity - $distributedQty);
        
        $totalQtyPerSchool = array_sum($distSubItems);
        $totalRequestedQty = $totalQtyPerSchool * count($schoolIds);
        
        if ($totalRequestedQty > $remainingStock) {
            return back()->withErrors(['dist_sub_items' => "Total requested quantity ({$totalRequestedQty}) exceeds remaining stock ({$remainingStock})."])->withInput();
        }

        $schoolsInfo = DB::table('schools')->whereIn('id', $schoolIds)->get()->keyBy('id');
        $messages = [];

        foreach ($schoolIds as $schoolId) {
            $schoolName = $schoolsInfo->has($schoolId) ? $schoolsInfo[$schoolId]->name : 'Unknown School';

            foreach ($distSubItems as $subItemId => $qty) {
                $subItem = DB::table('sub_items')->where('id', $subItemId)->first();
                $subItemName = $subItem ? $subItem->name : 'Unknown';

                DB::table('ownerships')->insert([
                    'school_id' => $schoolId,
                    'item_id' => $itemId,
                    'sub_item_id' => $subItemId,
                    'quantity' => $qty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('system_logs')->insert([
                    'user' => $userName,
                    'activity' => "Distributed {$qty} unit(s) of '{$subItemName}' (under '{$item->name}') to '{$schoolName}'",
                    'module' => 'Distribution',
                    'action_type' => 'Create',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $messages[] = "Distributed {$totalRequestedQty} unit(s) of '{$item->name}' to " . count($schoolIds) . " school(s)";
        return back()->with('success', implode('. ', $messages) . '.');
    }
}
