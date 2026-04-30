<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventorySetupController extends Controller
{



    /**
     * MODULE 1: MASTER REGISTRY — Register or update an item in the master list.
     */
    public function storeItem(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'category_name' => 'nullable|string|max:255',
            'item_name' => 'required|string|max:255',
            'sub_items' => 'required|array|min:1|max:30',
            'sub_items.*' => 'required|string|max:255',
            'sub_item_quantities' => 'required|array|min:1|max:30',
            'sub_item_quantities.*' => 'required|integer|min:1',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $existingItemId = $request->existing_item_id;
        $itemName = trim($request->item_name);
        
        $messages = [];

        // Global Source Extraction
        $sourceType = $request->input('source_entity_type');
        $providerId = $request->input('provider_id');
        $providerName = trim($request->input('provider_name'));
        $personnelName = trim($request->input('personnel_name'));
        $personnelPosition = trim($request->input('personnel_position'));

        $globalDistributorId = null;

        // 1. Process Provider
        if ($sourceType === 'external') {
            // External: use the chosen provider's stakeholder ID directly
            if ($providerId) {
                $globalDistributorId = (int) $providerId;
            } else if ($providerName) {
                $globalDistributorId = DB::table('stakeholders')->insertGetId([
                    'name' => $providerName,
                    'type' => 'Distributor',
                    'entity_type' => 'External',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $messages[] = "Registered new Provider: '{$providerName}'";
                DB::table('system_logs')->insert([
                    'user' => $userName,
                    'activity' => "Registered new External Provider: {$providerName}",
                    'module' => 'Stakeholders',
                    'action_type' => 'Create',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else if ($sourceType === 'school') {
            // School: resolve the school's matching Distributor stakeholder by school_id
            if ($providerId) {
                $schoolDistributor = DB::table('stakeholders')
                    ->where('type', 'Distributor')
                    ->where('school_id', $providerId)
                    ->first();

                if ($schoolDistributor) {
                    $globalDistributorId = $schoolDistributor->id;
                } else {
                    // School doesn't have a Distributor stakeholder yet — create one
                    $school = DB::table('schools')->where('id', $providerId)->first();
                    $schoolName = $school ? $school->name : $providerName;
                    $globalDistributorId = DB::table('stakeholders')->insertGetId([
                        'name'        => $schoolName,
                        'type'        => 'Distributor',
                        'entity_type' => 'School',
                        'school_id'   => $providerId,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $messages[] = "Registered school '{$schoolName}' as a Distributor source.";
                    DB::table('system_logs')->insert([
                        'user'        => $userName,
                        'activity'    => "Auto-registered school '{$schoolName}' as Distributor stakeholder",
                        'module'      => 'Stakeholders',
                        'action_type' => 'Create',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }
        }

        // 2. Process Personnel
        if ($personnelName) {
            $existingPerson = DB::table('stakeholders')
                ->where('type', 'Distributor')
                ->where(function($q) use ($personnelName) {
                    $q->whereRaw('LOWER(name) = ?', [strtolower($personnelName)])
                      ->orWhereRaw('LOWER(person_name) = ?', [strtolower($personnelName)]);
                })
                ->first();
                
            if (!$existingPerson) {
                $pData = [
                    'name' => $personnelName,
                    'person_name' => $personnelName,
                    'position' => $personnelPosition,
                    'type' => 'Distributor',
                    'entity_type' => 'Individual',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if ($sourceType === 'external' && $globalDistributorId) {
                    $pData['parent_id'] = $globalDistributorId;
                } else if ($sourceType === 'school' && $providerId) {
                    $pData['school_id'] = $providerId;
                }
                
                DB::table('stakeholders')->insert($pData);
                $messages[] = "Registered new Personnel: '{$personnelName}'";
                DB::table('system_logs')->insert([
                    'user' => $userName,
                    'activity' => "Registered new Personnel: {$personnelName}",
                    'module' => 'Stakeholders',
                    'action_type' => 'Create',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        $subItemsInput = $request->input('sub_items', []);
        $subItemQuantities = $request->input('sub_item_quantities', []);
        $subItemConditions = $request->input('sub_item_conditions', []);
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
                $isSerialized = isset($subItemSerialized[$index]) && $subItemSerialized[$index] === '1';
                if ($isSerialized) {
                    $qty = 1;
                }

                if ($qty > 0) {
                    $condition = $subItemConditions[$index] ?? 'Serviceable';
                    $validSubItems[] = [
                        'name' => $name, 
                        'quantity' => $qty, 
                        'condition' => $condition, 
                        'distributor_id' => $globalDistributorId,
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

        // Validate serial/property uniqueness before ANY database changes
        $seenProps = [];
        $seenSerials = [];
        foreach ($validSubItems as $sub) {
            if ($sub['is_serialized']) {
                $p = $sub['property_number'];
                $s = $sub['serial_number'];
                
                if (!empty($p)) {
                    if (in_array(strtolower($p), $seenProps)) return back()->withErrors(['sub_items' => "Duplicate property number '{$p}' found within your form submission."])->withInput();
                    $seenProps[] = strtolower($p);
                    if (DB::table('sub_items')->where('property_number', $p)->exists()) {
                        return back()->withErrors(['sub_items' => "Property number '{$p}' is already registered in the system."])->withInput();
                    }
                }
                
                if (!empty($s)) {
                    if (in_array(strtolower($s), $seenSerials)) return back()->withErrors(['sub_items' => "Duplicate serial number '{$s}' found within your form submission."])->withInput();
                    $seenSerials[] = strtolower($s);
                    if (DB::table('sub_items')->where('serial_number', $s)->exists()) {
                        return back()->withErrors(['sub_items' => "Serial number '{$s}' is already registered in the system."])->withInput();
                    }
                }
            }
        }

        $categoryId = $request->category_id;
        $categoryName = trim($request->category_name);

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

            $finalSubId = null;
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
                $finalSubId = $existingSub->id;
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
                $finalSubId = DB::table('sub_items')->insertGetId([
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

            // RECORD TRANSACTION: Manual Stock-in
            if ($finalSubId) {
                DB::table('asset_transactions')->insert([
                    'type' => 'STOCK_IN',
                    'sub_item_id' => $finalSubId,
                    'quantity_affected' => $subQty,
                    'condition_before' => $subCondition,
                    'condition_after' => $subCondition,
                    'processed_by' => $userName,
                    'notes' => 'Manual inventory registration via Setup',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (empty($messages)) {
            $messages[] = "Item '{$itemName}' already exists — no changes made";
        }

        return back()->with('success', implode('. ', $messages) . '.');
    }


    /**
     * RENAME: Rename a category, item, or sub-item.
     */

    /**
     * PREVIEW DELETE: Show what will be affected before deleting.
     */

    /**
     * DELETE: Delete a category, item, or sub-item with cascading.
     */

    /**
     * TRANSFER DISTRIBUTOR: Transfer a sub-item's distributor to a new one.
     */

    /**
     * MODULE 2 (MODIFIER): ASSET DISTRIBUTION (EDIT / UPDATE)
     */
}
