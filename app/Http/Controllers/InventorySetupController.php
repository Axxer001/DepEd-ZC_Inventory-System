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
            'sub_items' => 'required|array|min:1|max:10',
            'sub_items.*' => 'required|string|max:255',
            'sub_item_quantities' => 'required|array|min:1|max:10',
            'sub_item_quantities.*' => 'required|integer|min:1',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $existingItemId = $request->existing_item_id;
        $itemName = trim($request->item_name);
        
        $subItemsInput = $request->input('sub_items', []);
        $subItemQuantities = $request->input('sub_item_quantities', []);
        
        $validSubItems = [];
        $masterQty = 0;
        foreach ($subItemsInput as $index => $name) {
            $name = trim($name);
            if (!empty($name) && isset($subItemQuantities[$index])) {
                $qty = (int) $subItemQuantities[$index];
                if ($qty > 0) {
                    $validSubItems[] = ['name' => $name, 'quantity' => $qty];
                    $masterQty += $qty;
                }
            }
        }

        if ($masterQty === 0) {
             return back()->withErrors(['sub_items' => 'You must provide at least one valid sub-item with a quantity greater than zero.'])->withInput();
        }

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

        // Process sub-items — update existing ones, insert new ones
        foreach ($validSubItems as $sub) {
            $subItemName = $sub['name'];
            $subQty = $sub['quantity'];

            // Check if a sub-item with the same name already exists for this item
            $existingSub = DB::table('sub_items')
                ->where('item_id', $itemId)
                ->whereRaw('LOWER(name) = ?', [strtolower($subItemName)])
                ->first();

            if ($existingSub) {
                // Update the existing sub-item's quantity
                DB::table('sub_items')->where('id', $existingSub->id)->update([
                    'quantity' => DB::raw("quantity + {$subQty}"),
                    'updated_at' => now(),
                ]);
                DB::table('system_logs')->insert([
                    'user' => $userName,
                    'activity' => "Updated sub-item '{$subItemName}' quantity by +{$subQty} under item '{$itemName}'",
                    'module' => 'Items',
                    'action_type' => 'Update',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $messages[] = "Sub-item '{$subItemName}' quantity updated by +{$subQty}";
            } else {
                // Insert a brand-new sub-item
                DB::table('sub_items')->insertGetId([
                    'name' => $subItemName,
                    'item_id' => $itemId,
                    'quantity' => $subQty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('system_logs')->insert([
                    'user' => $userName,
                    'activity' => "Added sub-item '{$subItemName}' (Qty: {$subQty}) under item '{$itemName}'",
                    'module' => 'Items',
                    'action_type' => 'Create',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $messages[] = "Sub-item '{$subItemName}' (Qty: {$subQty}) added";
            }
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
        // Expecting a JSON payload or structured array
        $payload = $request->input('distributions'); 
        
        if (empty($payload) || !is_array($payload)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'No distributions provided.'], 400);
            }
            return back()->withErrors(['distributions' => 'No distributions provided.']);
        }

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $totalDistributed = 0;

        DB::beginTransaction();
        try {
            foreach ($payload as $dist) {
                $schoolId = $dist['school_id'] ?? null;
                $itemId = $dist['item_id'] ?? null;
                $subItems = $dist['sub_items'] ?? [];

                if (!$schoolId || !$itemId || empty($subItems)) {
                    continue; // Skip invalid tabs silently, or throw if preferred
                }

                $item = DB::table('items')->where('id', $itemId)->lockForUpdate()->first();
                $school = DB::table('schools')->where('id', $schoolId)->first();

                if (!$item || !$school) {
                    throw new \Exception("Invalid item or school selected.");
                }

                foreach ($subItems as $sub) {
                    $subId = $sub['id'];
                    $qty = (int) ($sub['qty'] ?? 0);

                    if ($qty <= 0) continue;

                    $subItem = DB::table('sub_items')->where('id', $subId)->where('item_id', $itemId)->lockForUpdate()->first();

                    if (!$subItem) {
                        throw new \Exception("Sub-item not found.");
                    }

                    if ($subItem->quantity < $qty) {
                        throw new \Exception("Requested quantity ({$qty}) for '{$subItem->name}' exceeds available stock ({$subItem->quantity}).");
                    }

                    // Insert ownership
                    DB::table('ownerships')->insert([
                        'school_id' => $schoolId,
                        'item_id' => $itemId,
                        'sub_item_id' => $subId,
                        'quantity' => $qty,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Decrement sub-item available stock (master_quantity stays unchanged)
                    DB::table('sub_items')->where('id', $subId)->decrement('quantity', $qty);

                    // Log activity
                    DB::table('system_logs')->insert([
                        'user' => $userName,
                        'activity' => "Distributed {$qty} unit(s) of '{$subItem->name}' (under '{$item->name}') to '{$school->name}'",
                        'module' => 'Distribution',
                        'action_type' => 'Create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $totalDistributed += $qty;
                }
            }
            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => "Successfully distributed {$totalDistributed} asset(s)."]);
            }
            return back()->with('success', "Successfully distributed {$totalDistributed} asset(s).");

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return back()->withErrors(['distributions' => $e->getMessage()]);
        }
    }
}
