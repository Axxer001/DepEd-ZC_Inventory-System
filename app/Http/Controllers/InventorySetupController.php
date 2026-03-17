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
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        DB::table('categories')->insert([
            'name' => $request->name,
            'created_at' => now(),
        ]);

        // Log the action to system_logs
        $userName = auth()->user() ? auth()->user()->name : 'System';
        DB::table('system_logs')->insert([
            'user' => $userName,
            'activity' => "Added new category: {$request->name}",
            'module' => 'Categories',
            'action_type' => 'Create',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "Category '{$request->name}' has been added successfully.");
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'item_name' => 'required|string|max:255',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $existingItemId = $request->existing_item_id;
        $itemName = trim($request->item_name);
        $categoryId = $request->category_id;
        $messages = [];

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
                    'quantity' => 0,
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

        // Process sub-items
        $subItems = $request->input('sub_items', []);
        $subItems = array_filter(array_map('trim', $subItems)); // Remove empty entries

        foreach ($subItems as $subItemName) {
            DB::table('sub_items')->insert([
                'name' => $subItemName,
                'quantity' => 0,
                'item_id' => $itemId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

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

        if (empty($messages)) {
            $messages[] = "Item '{$itemName}' already exists — no changes made";
        }

        $successMsg = implode('. ', $messages) . '.';
        return back()->with('success', $successMsg);
    }
}
