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

        // 1. Total System Assets (Sum of all master quantities across registry)
        $totalAssets = \Illuminate\Support\Facades\DB::table('items')->sum('master_quantity');
        
        // Calculate condition-based counts from ownerships
        $serviceableCount = \Illuminate\Support\Facades\DB::table('ownerships')
            ->where('condition', 'Serviceable')->sum('quantity');
        $unserviceableCount = \Illuminate\Support\Facades\DB::table('ownerships')
            ->where('condition', 'Unserviceable')->sum('quantity');
        $forRepairCount = \Illuminate\Support\Facades\DB::table('ownerships')
            ->where('condition', 'For Repair')->sum('quantity');

        // 2. Per-Quadrant Totals
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

        // 3. Recently Added Items
        $recentOwnerships = \Illuminate\Support\Facades\DB::table('ownerships')
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
                \Illuminate\Support\Facades\DB::raw('(SELECT user FROM system_logs WHERE module = "Items" AND activity LIKE CONCAT("%", items.name, "%") ORDER BY created_at DESC LIMIT 1) as added_by')
            )
            ->orderByDesc('ownerships.created_at')
            ->limit(10)
            ->get();

        // 4. Data for Quick Asset Entry
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

        return view('dashboard', compact(
            'schools', 
            'totalAssets', 
            'serviceableCount', 
            'unserviceableCount', 
            'forRepairCount',
            'quadrantTotals',
            'recentOwnerships',
            'categories',
            'items',
            'subItems'
        ));
    }

    public function storeQuickAsset(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            // sub_item_id is optional depending on the item
        ]);

        $subItemId = $request->input('sub_item_id');
        // Convert empty string/null to actual null so DB doesn't insert empty strings into an int column
        if (empty($subItemId)) {
            $subItemId = null;
        }

        $condition = $request->input('condition', 'Serviceable');

        \Illuminate\Support\Facades\DB::table('ownerships')->insert([
            'school_id' => $request->school_id,
            'item_id' => $request->item_id,
            'sub_item_id' => $subItemId,
            'quantity' => $request->quantity,
            'condition' => $condition,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $schoolName = \Illuminate\Support\Facades\DB::table('schools')->where('id', $request->school_id)->value('name');
        
        $logMessage = "Assigned {$request->quantity} unit(s) of ";
        if ($subItemId) {
            $subItemName = \Illuminate\Support\Facades\DB::table('sub_items')->where('id', $subItemId)->value('name');
            $logMessage .= "sub-item '{$subItemName}' to school '{$schoolName}'";
        } else {
            $itemName = \Illuminate\Support\Facades\DB::table('items')->where('id', $request->item_id)->value('name');
            $logMessage .= "item '{$itemName}' to school '{$schoolName}'";
        }

        $userName = \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()->name : 'System';

        \Illuminate\Support\Facades\DB::table('system_logs')->insert([
            'user' => $userName,
            'activity' => $logMessage,
            'module' => 'Items',
            'action_type' => 'Create',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Asset assigned successfully!');
    }
}