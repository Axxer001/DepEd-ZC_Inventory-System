<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $schools = \Illuminate\Support\Facades\DB::table('schools')
            ->join('districts', 'schools.district_id', '=', 'districts.id')
            ->select('schools.*', 'districts.quadrant_id')
            ->orderBy('name')
            ->get();

        // 1. Total Assets Distributed
        $distributedCount = \Illuminate\Support\Facades\DB::table('ownerships')->sum('quantity');

        // 2. Total Asset Value (Warehouse + Distributed)
        $warehouseValue = \Illuminate\Support\Facades\DB::table('sub_items')
            ->sum(\Illuminate\Support\Facades\DB::raw('quantity * COALESCE(unit_price, 0)'));
            
        $distributedValue = \Illuminate\Support\Facades\DB::table('ownerships')
            ->leftJoin('sub_items', 'ownerships.sub_item_id', '=', 'sub_items.id')
            ->sum(\Illuminate\Support\Facades\DB::raw('ownerships.quantity * COALESCE(sub_items.unit_price, 0)'));
            
        $totalAmount = $warehouseValue + $distributedValue;

        // 3. Condition-based counts from BOTH warehouse (sub_items) and distributed (ownerships)
        $warehouseServiceable = \Illuminate\Support\Facades\DB::table('sub_items')
            ->where('condition', 'Serviceable')->sum('quantity');
        $warehouseUnserviceable = \Illuminate\Support\Facades\DB::table('sub_items')
            ->where('condition', 'Unserviceable')->sum('quantity');
        $warehouseForRepair = \Illuminate\Support\Facades\DB::table('sub_items')
            ->where('condition', 'For Repair')->sum('quantity');

        $distServiceable = \Illuminate\Support\Facades\DB::table('ownerships')
            ->where('condition', 'Serviceable')->sum('quantity');
        $distUnserviceable = \Illuminate\Support\Facades\DB::table('ownerships')
            ->where('condition', 'Unserviceable')->sum('quantity');
        $distForRepair = \Illuminate\Support\Facades\DB::table('ownerships')
            ->where('condition', 'For Repair')->sum('quantity');

        $serviceableCount = $warehouseServiceable + $distServiceable;
        $unserviceableCount = $warehouseUnserviceable + $distUnserviceable;
        $forRepairCount = $warehouseForRepair + $distForRepair;

        // 4. Total System Assets (Sum of all tracked conditions)
        $totalAssets = $serviceableCount + $unserviceableCount + $forRepairCount;

        // 4. Source Breakdown (Non-Individual Distributors + sum of their individual children)
        // This calculates the total value of existing sub-items in the warehouse grouped by distributor,
        // aggregating items from "Individual" distributors into their parent entities.
        $sourceBreakdown = \Illuminate\Support\Facades\DB::table('stakeholders as s')
            ->whereIn('s.type', ['Distributor', 'System'])
            ->where('s.entity_type', '!=', 'Individual')
            ->leftJoin(\Illuminate\Support\Facades\DB::raw('(
                SELECT 
                    effective_id,
                    SUM(qty) as total_qty,
                    SUM(amount) as total_amount
                FROM (
                    SELECT 
                        CASE 
                            WHEN child.entity_type = "Individual" AND child.parent_id IS NOT NULL 
                            THEN child.parent_id 
                            ELSE child.id 
                        END as effective_id,
                        si.quantity as qty,
                        (si.quantity * COALESCE(si.unit_price, 0)) as amount
                    FROM sub_items si
                    JOIN stakeholders child ON si.distributor_id = child.id

                    UNION ALL

                    SELECT 
                        CASE 
                            WHEN child.entity_type = "Individual" AND child.parent_id IS NOT NULL 
                            THEN child.parent_id 
                            ELSE child.id 
                        END as effective_id,
                        o.quantity as qty,
                        (o.quantity * COALESCE(si.unit_price, 0)) as amount
                    FROM ownerships o
                    JOIN sub_items si ON o.sub_item_id = si.id
                    JOIN stakeholders child ON o.distributor_id = child.id
                ) combined
                GROUP BY effective_id
            ) as summary'), 's.id', '=', 'summary.effective_id')
            ->select(
                's.name as source_name',
                \Illuminate\Support\Facades\DB::raw('COALESCE(summary.total_qty, 0) as total_qty'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(summary.total_amount, 0) as total_amount')
            )
            ->orderBy('s.name')
            ->get();

        // 5. Per-Quadrant Totals
        // Join ownerships -> schools -> districts -> quadrants
        $quadrantData = \Illuminate\Support\Facades\DB::table('ownerships')
            ->join('schools', 'ownerships.school_id', '=', 'schools.id')
            ->join('districts', 'schools.district_id', '=', 'districts.id')
            ->select('districts.quadrant_id', \Illuminate\Support\Facades\DB::raw('SUM(ownerships.quantity) as total_qty'))
            ->groupBy('districts.quadrant_id')
            ->get();
            
        $quadrantTotals = [];
        foreach ($quadrantData as $qd) {
            $quadrantTotals[$qd->quadrant_id] = $qd->total_qty;
        }

        // 6. Recently Added Items (UNION of Distributions and Warehouse Additions)
        $deployments = \Illuminate\Support\Facades\DB::table('ownerships')
            ->join('schools', 'ownerships.school_id', '=', 'schools.id')
            ->join('districts', 'schools.district_id', '=', 'districts.id')
            ->join('items', 'ownerships.item_id', '=', 'items.id')
            ->leftJoin('sub_items', 'ownerships.sub_item_id', '=', 'sub_items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select(
                'schools.name as school_name',
                'districts.name as district_name',
                'categories.name as category_name',
                'items.name as item_name',
                'sub_items.name as sub_item_name',
                'ownerships.quantity',
                'ownerships.created_at',
                \Illuminate\Support\Facades\DB::raw('(SELECT user FROM system_logs WHERE (module = "Items" OR module = "Distribution") AND activity LIKE CONCAT("%", items.name, "%") ORDER BY created_at DESC LIMIT 1) as added_by')
            );

        $warehouse = \Illuminate\Support\Facades\DB::table('asset_transactions')
            ->where('asset_transactions.type', 'STOCK_IN')
            ->join('sub_items', 'asset_transactions.sub_item_id', '=', 'sub_items.id')
            ->join('items', 'sub_items.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select(
                \Illuminate\Support\Facades\DB::raw("'CENTRAL WAREHOUSE' as school_name"),
                \Illuminate\Support\Facades\DB::raw("'Stock Entry' as district_name"),
                'categories.name as category_name',
                'items.name as item_name',
                'sub_items.name as sub_item_name',
                'asset_transactions.quantity_affected as quantity',
                'asset_transactions.created_at',
                'asset_transactions.processed_by as added_by'
            );

        $recentOwnerships = $deployments->union($warehouse)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // 7. Data for Quick Asset Entry
        $categories = \Illuminate\Support\Facades\DB::table('categories')->orderBy('name')->get();
        // Calculate available_stock as the sum of remaining sub-item quantities
        // (sub_items.quantity already reflects deductions from distributions)
        $items = \Illuminate\Support\Facades\DB::table('items')
            ->leftJoin(
                \Illuminate\Support\Facades\DB::raw('(SELECT item_id, SUM(quantity) as remaining_qty FROM sub_items GROUP BY item_id) as subs'),
                'items.id', '=', 'subs.item_id'
            )
            ->select(
                'items.*',
                \Illuminate\Support\Facades\DB::raw('COALESCE(subs.remaining_qty, 0) as available_stock')
            )
            ->orderBy('items.name')
            ->get();
            
        $subItems = \Illuminate\Support\Facades\DB::table('sub_items')->orderBy('name')->get();

        $stakeholders = \Illuminate\Support\Facades\DB::table('stakeholders')->orderBy('name')->get();

        return view('dashboard', compact(
            'schools', 
            'totalAssets', 
            'distributedCount',
            'totalAmount',
            'serviceableCount', 
            'unserviceableCount', 
            'forRepairCount',
            'sourceBreakdown',
            'quadrantTotals',
            'recentOwnerships',
            'categories',
            'items',
            'subItems',
            'stakeholders'
        ));
    }

    public function storeQuickAsset(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:stakeholders,id',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'sub_item_id' => 'required|exists:sub_items,id',
            'is_serialized' => 'nullable|boolean',
            'property_number' => 'nullable|string',
            'serial_number' => 'nullable|string',
        ]);

        $subItemId = $request->input('sub_item_id');
        $condition = $request->input('condition', 'Serviceable');
        $recipientId = $request->input('recipient_id');
        $quantity = (int) $request->quantity;
        
        $isSerialized = $request->input('is_serialized', false);
        $propertyNumber = $request->input('property_number');
        $serialNumber = $request->input('serial_number');

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Fetch sub-item to get distributor and check stock
            $subItem = \Illuminate\Support\Facades\DB::table('sub_items')->where('id', $subItemId)->lockForUpdate()->first();

            if (!$subItem) {
                throw new \Exception("Sub-item not found.");
            }

            if ($subItem->quantity < $quantity) {
                throw new \Exception("Insufficient stock in warehouse. Available: {$subItem->quantity}");
            }

            $distributorId = $subItem->distributor_id;
            if (!$distributorId) {
                $distributorId = \Illuminate\Support\Facades\DB::table('stakeholders')
                    ->where('name', 'System Warehouse')
                    ->value('id');
            }

            // Deduct from warehouse
            \Illuminate\Support\Facades\DB::table('sub_items')->where('id', $subItemId)->decrement('quantity', $quantity);

            // Record ownership
            $recipient = \Illuminate\Support\Facades\DB::table('stakeholders')->where('id', $recipientId)->first();

            \Illuminate\Support\Facades\DB::table('ownerships')->insert([
                'school_id' => $recipient->school_id,
                'distributor_id' => $distributorId,
                'recipient_id' => $recipientId,
                'item_id' => $request->item_id,
                'sub_item_id' => $subItemId,
                'quantity' => $quantity,
                'condition' => $condition,
                'is_serialized' => $isSerialized,
                'property_number' => $propertyNumber,
                'serial_number' => $serialNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', $e->getMessage());
        }

        $logMessage = "Quick Entry: Assigned {$quantity} unit(s) to recipient '{$recipient->name}'" . ($isSerialized ? " [S/N: {$serialNumber}, P/N: {$propertyNumber}]" : "") . ".";
        $userName = \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()->name : 'System';

        \Illuminate\Support\Facades\DB::table('system_logs')->insert([
            'user' => $userName,
            'activity' => $logMessage,
            'module' => 'Items',
            'action_type' => 'Create',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "Successfully distributed assets to {$recipient->name}!");
    }
}