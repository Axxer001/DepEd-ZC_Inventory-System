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

    public function storeItem(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'category_name' => 'nullable|string|max:255',
            'item_name' => 'required|string|max:255',
            'school_ids' => 'nullable|array',
            'school_ids.*' => 'exists:schools,id',
            'quantity' => 'nullable|numeric|min:1|required_with:school_ids',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $existingItemId = $request->existing_item_id;
        $itemName = trim($request->item_name);
        
        $categoryId = $request->category_id;
        $categoryName = trim($request->category_name);
        $schoolIds = $request->input('school_ids', []);
        $quantity = $request->input('quantity', 0);
        $messages = [];

        if (!$categoryId) {
            if (!$categoryName) {
                return back()->withErrors(['category_name' => 'Please select a Main Category or type a new one.'])->withInput();
            }

            // Check if user typed an existing category name
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

        // Determine item ID: use existing or create new
        if ($existingItemId) {
            // Verify the existing item actually exists
            $existingItem = DB::table('items')->where('id', $existingItemId)->first();
            if (!$existingItem) {
                return back()->withErrors(['item_name' => 'The selected item does not exist.']);
            }
            $itemId = $existingItem->id;
        } else {
            // Case-insensitive duplicate check across ALL categories
            $duplicate = DB::table('items')
                ->whereRaw('LOWER(name) = ?', [strtolower($itemName)])
                ->first();

            if ($duplicate) {
                return back()->withErrors(['item_name' => "The item '{$itemName}' already exists in the system. Please use the dropdown to select it instead."])->withInput();
            } else {
                // Insert new item
                $itemId = DB::table('items')->insertGetId([
                    'name' => $itemName,
                    'category_id' => $categoryId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Log item creation
                DB::table('system_logs')->insert([
                    'user' => $userName,
                    'activity' => "Added new item: {$itemName}",
                    'module' => 'Items',
                    'action_type' => 'Create',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $messages[] = "Item '{$itemName}' added";
            }
        }

        // Process new sub-items
        $subItems = $request->input('sub_items', []);
        $subItems = array_filter(array_map('trim', $subItems)); // Remove empty entries

        $createdSubItemsData = [];
        foreach ($subItems as $subItemName) {
            $subItemId = DB::table('sub_items')->insertGetId([
                'name' => $subItemName,
                'item_id' => $itemId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $createdSubItemsData[$subItemId] = $subItemName;

            // Log each sub-item separately
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

        // Process existing sub-items
        $existingSubItemIds = $request->input('existing_sub_item_ids', []);
        $existingSubItemsData = [];
        if (!empty($existingSubItemIds)) {
            $existingSubItemsRecords = DB::table('sub_items')->whereIn('id', $existingSubItemIds)->get();
            foreach ($existingSubItemsRecords as $es) {
                $existingSubItemsData[$es->id] = $es->name;
            }
        }
        
        // Combine all sub-items (new + existing) that need ownership assignment
        $allSubItemsToAssign = $createdSubItemsData + $existingSubItemsData;

        // Process ownership assignments
        if (!empty($schoolIds) && $quantity > 0) {
            $schoolsInfo = DB::table('schools')->whereIn('id', $schoolIds)->get()->keyBy('id');
            foreach ($schoolIds as $schoolId) {
                $schoolName = $schoolsInfo->has($schoolId) ? $schoolsInfo[$schoolId]->name : 'Unknown School';

                // Record ownership for the main item
                DB::table('ownerships')->insert([
                    'school_id' => $schoolId,
                    'item_id' => $itemId,
                    'sub_item_id' => null,
                    'quantity' => $quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Log main item assignment
                DB::table('system_logs')->insert([
                    'user' => $userName,
                    'activity' => "Assigned {$quantity} unit(s) of item '{$itemName}' to school '{$schoolName}'",
                    'module' => 'Items',
                    'action_type' => 'Create',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Record ownership for each sub-item (newly created AND selected existing)
                foreach ($allSubItemsToAssign as $subItemId => $subItemName) {
                    DB::table('ownerships')->insert([
                        'school_id' => $schoolId,
                        'item_id' => $itemId,
                        'sub_item_id' => $subItemId,
                        'quantity' => $quantity,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Log sub-item assignment
                    DB::table('system_logs')->insert([
                        'user' => $userName,
                        'activity' => "Assigned {$quantity} unit(s) of sub-item '{$subItemName}' to school '{$schoolName}'",
                        'module' => 'Items',
                        'action_type' => 'Create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            $messages[] = "Assigned {$quantity} unit(s) to " . count($schoolIds) . " school(s)";
        }

        if (empty($messages)) {
            $messages[] = "Item '{$itemName}' already exists — no changes made";
        }

        $successMsg = implode('. ', $messages) . '.';
        return back()->with('success', $successMsg);
    }
}
