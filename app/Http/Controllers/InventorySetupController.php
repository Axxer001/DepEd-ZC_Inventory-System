<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventorySetupController extends Controller
{
    /**
     * MODULE 1: MASTER REGISTRY — Register or update an item in the master list.
     *
     * This method now uses the new schema:
     * - acquisition_sources (replaces stakeholders)
     * - asset_sources (replaces sub_items)
     * - No more master_quantity on items (derived from SUM of asset_sources)
     * - No more asset_transactions
     */
    public function storeItem(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'category_name' => 'nullable|string|max:255',
            'item_name' => 'required|string|max:255',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $existingItemId = $request->existing_item_id;
        $itemName = trim($request->item_name);

        $messages = [];

        // ── Resolve Category ──
        $categoryId = $request->category_id;
        $categoryName = trim($request->category_name);

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

        // ── Resolve Item ──
        if ($existingItemId) {
            $existingItem = DB::table('items')->where('id', $existingItemId)->first();
            if (!$existingItem) {
                return back()->withErrors(['item_name' => 'The selected item does not exist.']);
            }
            $itemId = $existingItem->id;
            $messages[] = "Item '{$itemName}' selected for asset registration";
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
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('system_logs')->insert([
                'user' => $userName,
                'activity' => "Added new item: {$itemName}",
                'module' => 'Items',
                'action_type' => 'Create',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $messages[] = "Item '{$itemName}' registered";
        }

        if (empty($messages)) {
            $messages[] = "Item '{$itemName}' already exists — no changes made";
        }

        return back()->with('success', implode('. ', $messages) . '.');
    }

    /**
     * MODULE 2: BATCH REGISTRATION — Securely parse and insert bulk generated table rows
     */
    public function storeBatch(Request $request)
    {
        $payload = $request->validate([
            'source_of_acquisition' => 'required|string',
            'rows' => 'required|array|min:1',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';

        try {
            DB::beginTransaction();

            // 1. Resolve Global Acquisition Source
            $acqSourceName = trim($payload['source_of_acquisition']);
            $acqSource = DB::table('acquisition_sources')->whereRaw('LOWER(name) = ?', [strtolower($acqSourceName)])->first();
            if (!$acqSource) {
                $acqSourceId = DB::table('acquisition_sources')->insertGetId([
                    'name' => $acqSourceName,
                    'source_type' => 'Internal', 
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $acqSourceId = $acqSource->id;
            }

            foreach ($payload['rows'] as $row) {
                // 2. Resolve Classification
                $className = trim($row['classification'] ?? '');
                $classObj = DB::table('classifications')->whereRaw('LOWER(name) = ?', [strtolower($className)])->first();
                if (!$classObj) {
                    $classId = DB::table('classifications')->insertGetId([
                        'name' => $className,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $classId = $classObj->id;
                }

                // 3. Resolve Category
                $catName = trim($row['category'] ?? '');
                $catObj = DB::table('categories')->whereRaw('LOWER(name) = ?', [strtolower($catName)])->first();
                if (!$catObj) {
                    $catId = DB::table('categories')->insertGetId([
                        'classification_id' => $classId,
                        'name' => $catName,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $catId = $catObj->id;
                    if (!$catObj->classification_id) {
                        DB::table('categories')->where('id', $catId)->update(['classification_id' => $classId]);
                    }
                }

                // 4. Resolve Item
                $itemName = trim($row['item'] ?? '');
                $itemObj = DB::table('items')->whereRaw('LOWER(name) = ?', [strtolower($itemName)])->first();
                if (!$itemObj) {
                    $itemId = DB::table('items')->insertGetId([
                        'category_id' => $catId,
                        'name' => $itemName,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $itemId = $itemObj->id;
                }

                // 5. Insert Asset Source
                $costPerUnit = floatval($row['cost'] ?? 0);
                $qty = intval($row['qty'] ?? 1);
                
                $assetSourceId = DB::table('asset_sources')->insertGetId([
                    'item_id' => $itemId,
                    'description' => $row['description'] ?? null,
                    'acquisition_source_id' => $acqSourceId,
                    'mode_of_acquisition' => $row['mode'] ?? 'Unknown',
                    'source_personnel' => $row['personnel'] ?? null,
                    'personnel_position' => $row['position'] ?? null,
                    'asset_cost' => $costPerUnit,
                    'quantity' => $qty,
                    'estimated_useful_life' => isset($row['useful-life']) && $row['useful-life'] !== '' ? intval($row['useful-life']) : null,
                    'acceptance_date' => $row['acceptance-date'] ?? now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // 6. Insert Asset Distribution
                $acqCost = $costPerUnit * $qty;
                
                // Allow empty property number to be null instead of empty string for unique constraint handling
                $propertyNo = isset($row['property-no']) && trim($row['property-no']) !== '' ? trim($row['property-no']) : null;
                
                DB::table('asset_distributions')->insert([
                    'asset_source_id' => $assetSourceId,
                    'region' => $row['region'] ?? 'Region IX',
                    'division' => $row['division'] ?? 'Division of Zamboanga City',
                    'office_school_type' => $row['school-type'] ?? '',
                    'school_id' => $row['school-id'] ?? null,
                    'office_school_name' => $row['school-name'] ?? '',
                    'nature_of_occupancy' => $row['occupancy'] ?? '',
                    'location' => $row['location'] ?? null,
                    'property_number' => $propertyNo,
                    'acquisition_cost' => $acqCost,
                    'acquisition_date' => $row['acquisition-date'] ?? now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $count = count($payload['rows']);
            DB::table('system_logs')->insert([
                'user' => $userName,
                'activity' => "Registered {$count} items via Bulk Asset Registration",
                'module' => 'Assets',
                'action_type' => 'Create',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully registered {$count} items."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
