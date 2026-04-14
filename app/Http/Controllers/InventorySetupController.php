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

        $schoolId = DB::table('schools')->insertGetId([
            'school_id' => $request->school_id,
            'name' => $request->name,
            'district_id' => $request->district_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Automatically register the school as a stakeholder (recipient)
        DB::table('stakeholders')->insert([
            'name' => $request->name,
            'type' => 'Recipient',
            'entity_type' => 'School',
            'school_id' => $schoolId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "Successfully added '{$request->name}' in the system.");
    }

    public function updateSchool(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:schools,id',
            'new_school_id' => 'required|string|max:255',
            'new_school_name' => 'required|string|max:255',
        ]);

        $school = DB::table('schools')->where('id', $request->id)->first();
        if (!$school) {
            return back()->withErrors(['school' => 'School not found.']);
        }

        $schoolIdStr = $school->school_id ?? '';
        $schoolName = $school->name ?? '';
        $schoolIdDb = $school->id ?? $request->id;

        // Prevent duplicate school IDs
        if ($schoolIdStr !== $request->new_school_id) {
            $duplicateId = DB::table('schools')->where('school_id', $request->new_school_id)->first();
            if ($duplicateId) {
                return back()->withErrors(['new_school_id' => "The school ID '{$request->new_school_id}' is already in use by another school."]);
            }
        }

        $userName = auth()->user() ? auth()->user()->name : 'System';

        DB::table('schools')->where('id', $schoolIdDb)->update([
            'school_id' => $request->new_school_id,
            'name' => $request->new_school_name,
            'updated_at' => now(),
        ]);

        DB::table('system_logs')->insert([
            'user' => $userName,
            'activity' => "Updated school info: [{$schoolIdStr}] {$schoolName} -> [{$request->new_school_id}] {$request->new_school_name}",
            'module' => 'Schools',
            'action_type' => 'Update',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "Successfully updated school to '{$request->new_school_name}'.");
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
        $subItemConditions = $request->input('sub_item_conditions', []);
        $subItemDistributors = $request->input('sub_item_distributors', []);
        
        $subItemPrices = $request->input('sub_item_prices', []);
        $subItemDates = $request->input('sub_item_dates', []);
        $subItemSerialized = $request->input('sub_item_serialized', []);
        $subItemPropertyNumbers = $request->input('sub_item_property_numbers', []);
        $subItemSerialNumbers = $request->input('sub_item_serial_numbers', []);
        
        $validSubItems = [];
        $masterQty = 0;
        foreach ($subItemsInput as $index => $name) {
            $name = trim($name);
            if (!empty($name) && isset($subItemQuantities[$index])) {
                $qty = (int) $subItemQuantities[$index];
                // Enforce serialized logic
                $isSerialized = isset($subItemSerialized[$index]) && $subItemSerialized[$index] === '1';
                if ($isSerialized) {
                    $qty = 1; // Strictly enforce qty 1 for serialized items
                }

                if ($qty > 0) {
                    $condition = $subItemConditions[$index] ?? 'Serviceable';
                    $distributorId = !empty($subItemDistributors[$index]) ? (int) $subItemDistributors[$index] : null;
                    $validSubItems[] = [
                        'name' => $name, 
                        'quantity' => $qty, 
                        'condition' => $condition, 
                        'distributor_id' => $distributorId,
                        'unit_price' => !empty($subItemPrices[$index]) ? (float) $subItemPrices[$index] : null,
                        'date_acquired' => !empty($subItemDates[$index]) ? $subItemDates[$index] : null,
                        'is_serialized' => $isSerialized,
                        'property_number' => !empty($subItemPropertyNumbers[$index]) ? trim($subItemPropertyNumbers[$index]) : null,
                        'serial_number' => !empty($subItemSerialNumbers[$index]) ? trim($subItemSerialNumbers[$index]) : null,
                    ];
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
            $subCondition = $sub['condition'] ?? 'Serviceable';

            // Serialized items should never be grouped, they must always exist as a unique row
            $existingSub = null;
            if (!$sub['is_serialized']) {
                $existingSub = DB::table('sub_items')
                    ->where('item_id', $itemId)
                    ->where('is_serialized', false)
                    ->whereRaw('LOWER(name) = ?', [strtolower($subItemName)])
                    ->first();
            }

            if ($existingSub) {
                // Update the existing sub-item's quantity and condition
                $updateData = [
                    'quantity' => DB::raw("quantity + {$subQty}"),
                    'condition' => $subCondition,
                    'updated_at' => now(),
                ];
                if ($sub['distributor_id']) {
                    $updateData['distributor_id'] = $sub['distributor_id'];
                }
                DB::table('sub_items')->where('id', $existingSub->id)->update($updateData);
                DB::table('system_logs')->insert([
                    'user' => $userName,
                    'activity' => "Updated sub-item '{$subItemName}' quantity by +{$subQty} (Condition: {$subCondition}) under item '{$itemName}'",
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
                    'condition' => $subCondition,
                    'distributor_id' => $sub['distributor_id'],
                    'qr_hash' => $request->input('scanned_tag') ?: \Illuminate\Support\Str::uuid()->toString(),
                    'is_serialized' => $sub['is_serialized'],

                    'unit_price' => $sub['unit_price'],
                    'date_acquired' => $sub['date_acquired'] ?: now()->toDateString(),
                    'property_number' => $sub['property_number'],
                    'serial_number' => $sub['serial_number'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('system_logs')->insert([
                    'user' => $userName,
                    'activity' => "Added sub-item '{$subItemName}' (Qty: {$subQty}, Condition: {$subCondition}) under item '{$itemName}'",
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
                // Fallback to school_id if old UI is used temporarily, otherwise use recipient_id
                $recipientId = $dist['recipient_id'] ?? null;
                if (!$recipientId && isset($dist['school_id'])) {
                    $recipientId = DB::table('stakeholders')->where('school_id', $dist['school_id'])->value('id');
                }
                
                $itemId = $dist['item_id'] ?? null;
                $subItems = $dist['sub_items'] ?? [];

                if (!$recipientId || !$itemId || empty($subItems)) {
                    continue; // Skip invalid tabs silently
                }

                $item = DB::table('items')->where('id', $itemId)->lockForUpdate()->first();
                $recipient = DB::table('stakeholders')->where('id', $recipientId)->first();

                if (!$item || !$recipient) {
                    throw new \Exception("Invalid item or recipient selected.");
                }

                foreach ($subItems as $sub) {
                    $subId = $sub['id'];
                    $qty = (int) ($sub['qty'] ?? 0);
                    $distributorId = $sub['distributor_id'] ?? null;

                    if ($qty <= 0 || !$distributorId) continue;

                    // Fetch the specific sub-item record which inherently belongs to a specific distributor
                    $subItem = DB::table('sub_items')->where('id', $subId)->lockForUpdate()->first();
                    $distributor = DB::table('stakeholders')->where('id', $distributorId)->first();

                    if (!$subItem || !$distributor) {
                        throw new \Exception("Sub-item or distributor not found.");
                    }

                    if ($subItem->quantity < $qty) {
                        throw new \Exception("Requested quantity ({$qty}) for '{$subItem->name}' exceeds available stock ({$subItem->quantity}).");
                    }

                    // Decrement sub-item available stock directly from its master record
                    DB::table('sub_items')->where('id', $subId)->decrement('quantity', $qty);

                    // Insert or update ownership for the recipient
                    $existingOwnership = DB::table('ownerships')
                        ->where('recipient_id', $recipientId)
                        ->where('sub_item_id', $subId)
                        ->where('condition', $sub['condition'] ?? 'Serviceable')
                        ->first();

                    if ($existingOwnership) {
                        DB::table('ownerships')->where('id', $existingOwnership->id)->update([
                            'quantity' => DB::raw("quantity + {$qty}"),
                            'updated_at' => now(),
                            'distributor_id' => $distributorId // Update last distributor source
                        ]);
                    } else {
                        DB::table('ownerships')->insert([
                            'distributor_id' => $distributorId,
                            'recipient_id' => $recipientId,
                            'item_id' => $itemId,
                            'sub_item_id' => $subId,
                            'quantity' => $qty,
                            'condition' => $sub['condition'] ?? 'Serviceable',
                            'school_id' => $recipient->school_id ?? null, // Keep for backward compatibility reporting if needed
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Log activity
                    $itemName = $item->name ?? 'Unknown Item';
                    $distributorName = $distributor->name ?? 'Unknown Distributor';
                    $recipientName = $recipient->name ?? 'Unknown Recipient';
                    
                    DB::table('system_logs')->insert([
                        'user' => $userName,
                        'activity' => "Distributed {$qty} unit(s) of sub-item ID {$subId} (under '{$itemName}') from '{$distributorName}' to '{$recipientName}'",
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

    /**
     * RENAME: Rename a category, item, or sub-item.
     */
    public function renameRecord(Request $request)
    {
        $request->validate([
            'type'     => 'required|in:category,item,sub_item',
            'id'       => 'required|integer',
            'new_name' => 'required|string|max:255',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $type     = $request->type;
        $id       = $request->id;
        $newName  = trim($request->new_name);

        if ($type === 'category') {
            $record = DB::table('categories')->where('id', $id)->first();
            if (!$record) return response()->json(['success' => false, 'message' => 'Category not found.'], 404);

            $duplicate = DB::table('categories')
                ->whereRaw('LOWER(name) = ?', [strtolower($newName)])
                ->where('id', '!=', $id)
                ->first();
            if ($duplicate) return response()->json(['success' => false, 'message' => "The name \"{$newName}\" is already used by another category."], 422);

            DB::table('categories')->where('id', $id)->update(['name' => $newName, 'updated_at' => now()]);
            DB::table('system_logs')->insert(['user' => $userName, 'activity' => "Renamed category: \"{$record->name}\" → \"{$newName}\"", 'module' => 'Categories', 'action_type' => 'Update', 'created_at' => now(), 'updated_at' => now()]);

            return response()->json(['success' => true, 'message' => "Category renamed to \"{$newName}\" successfully."]);
        }

        if ($type === 'item') {
            $record = DB::table('items')->where('id', $id)->first();
            if (!$record) return response()->json(['success' => false, 'message' => 'Item not found.'], 404);

            $duplicate = DB::table('items')
                ->whereRaw('LOWER(name) = ?', [strtolower($newName)])
                ->where('id', '!=', $id)
                ->first();
            if ($duplicate) return response()->json(['success' => false, 'message' => "The name \"{$newName}\" is already used by another item."], 422);

            DB::table('items')->where('id', $id)->update(['name' => $newName, 'updated_at' => now()]);
            DB::table('system_logs')->insert(['user' => $userName, 'activity' => "Renamed item: \"{$record->name}\" → \"{$newName}\"", 'module' => 'Items', 'action_type' => 'Update', 'created_at' => now(), 'updated_at' => now()]);

            return response()->json(['success' => true, 'message' => "Item renamed to \"{$newName}\" successfully."]);
        }

        if ($type === 'sub_item') {
            $record = DB::table('sub_items')->where('id', $id)->first();
            if (!$record) return response()->json(['success' => false, 'message' => 'Sub-item not found.'], 404);

            $duplicate = DB::table('sub_items')
                ->whereRaw('LOWER(name) = ?', [strtolower($newName)])
                ->where('item_id', $record->item_id)
                ->where('id', '!=', $id)
                ->first();
            if ($duplicate) return response()->json(['success' => false, 'message' => "The name \"{$newName}\" is already used by another sub-item under the same item."], 422);

            DB::table('sub_items')->where('id', $id)->update(['name' => $newName, 'updated_at' => now()]);
            $parentItem = DB::table('items')->where('id', $record->item_id)->first();
            DB::table('system_logs')->insert(['user' => $userName, 'activity' => "Renamed sub-item: \"{$record->name}\" → \"{$newName}\" (under item: " . ($parentItem ? $parentItem->name : 'Unknown') . ")", 'module' => 'Items', 'action_type' => 'Update', 'created_at' => now(), 'updated_at' => now()]);

            return response()->json(['success' => true, 'message' => "Sub-item renamed to \"{$newName}\" successfully."]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid type.'], 422);
    }

    /**
     * PREVIEW DELETE: Show what will be affected before deleting.
     */
    public function previewDelete(Request $request)
    {
        $request->validate([
            'type' => 'required|in:category,item,sub_item,school',
            'id'   => 'required|integer',
        ]);

        $type = $request->type;
        $id   = $request->id;
        $impact = ['items' => 0, 'sub_items' => 0, 'ownerships' => 0, 'schools_affected' => 0, 'total_stock' => 0];

        if ($type === 'category') {
            $items = DB::table('items')->where('category_id', $id)->pluck('id');
            $impact['items'] = $items->count();
            if ($items->count() > 0) {
                $subItems = DB::table('sub_items')->whereIn('item_id', $items)->get();
                $impact['sub_items'] = $subItems->count();
                $impact['total_stock'] = $subItems->sum('quantity');
                $ownerships = DB::table('ownerships')->whereIn('item_id', $items)->get();
                $impact['ownerships'] = $ownerships->sum('quantity');
                $impact['schools_affected'] = $ownerships->pluck('school_id')->unique()->count();
            }
        } elseif ($type === 'item') {
            $subItems = DB::table('sub_items')->where('item_id', $id)->get();
            $impact['sub_items'] = $subItems->count();
            $impact['total_stock'] = $subItems->sum('quantity');
            $ownerships = DB::table('ownerships')->where('item_id', $id)->get();
            $impact['ownerships'] = $ownerships->sum('quantity');
            $impact['schools_affected'] = $ownerships->pluck('school_id')->unique()->count();
        } elseif ($type === 'sub_item') {
            $sub = DB::table('sub_items')->where('id', $id)->first();
            if ($sub) {
                $impact['total_stock'] = $sub->quantity;
                $ownerships = DB::table('ownerships')->where('sub_item_id', $id)->get();
                $impact['ownerships'] = $ownerships->sum('quantity');
                $impact['schools_affected'] = $ownerships->pluck('school_id')->unique()->count();
            }
        } elseif ($type === 'school') {
            $impact['items'] = 0;
            $impact['sub_items'] = 0;
            $impact['total_stock'] = 0;
            $ownerships = DB::table('ownerships')->where('school_id', $id)->get();
            $impact['ownerships'] = $ownerships->sum('quantity');
            $impact['schools_affected'] = 1;
        }

        return response()->json(['success' => true, 'impact' => $impact]);
    }

    /**
     * DELETE: Delete a category, item, or sub-item with cascading.
     */
    public function deleteRecord(Request $request)
    {
        $request->validate([
            'type' => 'required|in:category,item,sub_item,school',
            'id'   => 'required|integer',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $type = $request->type;
        $id   = $request->id;

        DB::beginTransaction();
        try {
            if ($type === 'category') {
                $record = DB::table('categories')->where('id', $id)->first();
                if (!$record) throw new \Exception('Category not found.');

                $items = DB::table('items')->where('category_id', $id)->pluck('id');
                if ($items->count() > 0) {
                    DB::table('ownerships')->whereIn('item_id', $items)->delete();
                    DB::table('sub_items')->whereIn('item_id', $items)->delete();
                    DB::table('items')->whereIn('id', $items)->delete();
                }
                DB::table('categories')->where('id', $id)->delete();

                DB::table('system_logs')->insert(['user' => $userName, 'activity' => "Deleted category \"{$record->name}\" and all related items ({$items->count()}), sub-items, and ownerships", 'module' => 'Categories', 'action_type' => 'Delete', 'created_at' => now(), 'updated_at' => now()]);

            } elseif ($type === 'item') {
                $record = DB::table('items')->where('id', $id)->first();
                if (!$record) throw new \Exception('Item not found.');

                DB::table('ownerships')->where('item_id', $id)->delete();
                DB::table('sub_items')->where('item_id', $id)->delete();
                DB::table('items')->where('id', $id)->delete();

                DB::table('system_logs')->insert(['user' => $userName, 'activity' => "Deleted item \"{$record->name}\" and all related sub-items and ownerships", 'module' => 'Items', 'action_type' => 'Delete', 'created_at' => now(), 'updated_at' => now()]);

            } elseif ($type === 'sub_item') {
                $record = DB::table('sub_items')->where('id', $id)->first();
                if (!$record) throw new \Exception('Sub-item not found.');

                $parentItem = DB::table('items')->where('id', $record->item_id)->first();
                $ownedQty = DB::table('ownerships')->where('sub_item_id', $id)->sum('quantity');

                DB::table('ownerships')->where('sub_item_id', $id)->delete();
                DB::table('sub_items')->where('id', $id)->delete();

                // Deduct from master quantity
                if ($parentItem) {
                    $totalToDeduct = $record->quantity + $ownedQty;
                    DB::table('items')->where('id', $parentItem->id)->decrement('master_quantity', $totalToDeduct);
                }

                DB::table('system_logs')->insert(['user' => $userName, 'activity' => "Deleted sub-item \"{$record->name}\" (under item: " . ($parentItem ? $parentItem->name : 'Unknown') . ") and all related ownerships", 'module' => 'Items', 'action_type' => 'Delete', 'created_at' => now(), 'updated_at' => now()]);
            } elseif ($type === 'school') {
                $record = DB::table('schools')->where('id', $id)->first();
                if (!$record) throw new \Exception('School not found.');

                // Recover distributed assets back to available stock (sub_items.quantity)
                $ownerships = DB::table('ownerships')->where('school_id', $id)->get();
                foreach ($ownerships as $ownership) {
                    DB::table('sub_items')->where('id', $ownership->sub_item_id)->increment('quantity', $ownership->quantity);
                }

                $ownershipsCount = $ownerships->count();
                DB::table('ownerships')->where('school_id', $id)->delete();
                DB::table('schools')->where('id', $id)->delete();

                DB::table('system_logs')->insert(['user' => $userName, 'activity' => "Deleted school \"{$record->name}\" ID: {$record->school_id} and recovered all its {$ownershipsCount} asset(s) back to available stock", 'module' => 'Schools', 'action_type' => 'Delete', 'created_at' => now(), 'updated_at' => now()]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * MODULE 2 (MODIFIER): ASSET DISTRIBUTION (EDIT / UPDATE)
     */
    public function updateDistribution(Request $request)
    {
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
                    $action = $sub['action'] ?? 'add';

                    if ($qty <= 0 && $action !== 'delete_all') continue;

                    $subItem = DB::table('sub_items')->where('id', $subId)->where('item_id', $itemId)->lockForUpdate()->first();

                    if (!$subItem) {
                        throw new \Exception("Sub-item not found.");
                    }

                    $ownership = DB::table('ownerships')
                        ->where('school_id', $schoolId)
                        ->where('item_id', $itemId)
                        ->where('sub_item_id', $subId)
                        ->lockForUpdate()
                        ->first();

                    if ($action === 'add') {
                        if ($subItem->quantity < $qty) {
                            throw new \Exception("Requested addition ({$qty}) for '{$subItem->name}' exceeds available stock ({$subItem->quantity}).");
                        }
                        
                        if ($ownership) {
                            DB::table('ownerships')->where('id', $ownership->id)->increment('quantity', $qty);
                        } else {
                            DB::table('ownerships')->insert([
                                'school_id' => $schoolId,
                                'item_id' => $itemId,
                                'sub_item_id' => $subId,
                                'quantity' => $qty,
                                'condition' => $sub['condition'] ?? 'Serviceable',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                        DB::table('sub_items')->where('id', $subId)->decrement('quantity', $qty);
                        
                        DB::table('system_logs')->insert([
                            'user' => $userName,
                            'activity' => "Added {$qty} unit(s) of '{$subItem->name}' to '{$school->name}'",
                            'module' => 'Distribution',
                            'action_type' => 'Update',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $totalDistributed += $qty;

                    } elseif ($action === 'subtract') {
                        if (!$ownership || $ownership->quantity < $qty) {
                            throw new \Exception("Requested subtraction ({$qty}) for '{$subItem->name}' exceeds owned stock.");
                        }

                        $remaining = $ownership->quantity - $qty;
                        if ($remaining <= 0) {
                            DB::table('ownerships')->where('id', $ownership->id)->delete();
                        } else {
                            DB::table('ownerships')->where('id', $ownership->id)->update(['quantity' => $remaining, 'updated_at' => now()]);
                        }
                        DB::table('sub_items')->where('id', $subId)->increment('quantity', $qty);

                        DB::table('system_logs')->insert([
                            'user' => $userName,
                            'activity' => "Subtracted {$qty} unit(s) of '{$subItem->name}' from '{$school->name}'",
                            'module' => 'Distribution',
                            'action_type' => 'Update',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $totalDistributed += $qty;

                    } elseif ($action === 'delete_all') {
                        if ($ownership) {
                            $ownedQty = $ownership->quantity;
                            DB::table('ownerships')->where('id', $ownership->id)->delete();
                            DB::table('sub_items')->where('id', $subId)->increment('quantity', $ownedQty);

                            DB::table('system_logs')->insert([
                                'user' => $userName,
                                'activity' => "Deleted all ({$ownedQty}) unit(s) of '{$subItem->name}' from '{$school->name}'",
                                'module' => 'Distribution',
                                'action_type' => 'Delete',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $totalDistributed += $ownedQty;
                        }
                    }
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
