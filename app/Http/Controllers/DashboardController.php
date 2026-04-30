<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $schools = DB::table('schools')
            ->join('districts', 'schools.district_id', '=', 'districts.id')
            ->select('schools.*', 'districts.quadrant_id')
            ->orderBy('name')
            ->get();

        // 1. Total Assets Sourced (from new asset_sources table)
        $totalAssets = DB::table('asset_sources')->sum('quantity');

        // 2. Total Assets Distributed (from new asset_distributions table)
        $distributedCount = DB::table('asset_distributions')->count();

        // 3. Total Asset Value (from asset_sources)
        $totalAmount = DB::table('asset_sources')
            ->sum(DB::raw('quantity * asset_cost'));

        // 4. Source Breakdown (from acquisition_sources -> asset_sources)
        $sourceBreakdown = DB::table('acquisition_sources as aq')
            ->leftJoin(DB::raw('(
                SELECT acquisition_source_id,
                       SUM(quantity) as total_qty,
                       SUM(quantity * asset_cost) as total_amount
                FROM asset_sources
                GROUP BY acquisition_source_id
            ) as summary'), 'aq.id', '=', 'summary.acquisition_source_id')
            ->select(
                'aq.name as source_name',
                DB::raw('COALESCE(summary.total_qty, 0) as total_qty'),
                DB::raw('COALESCE(summary.total_amount, 0) as total_amount')
            )
            ->orderBy('aq.name')
            ->get();

        // 5. Per-Quadrant Totals (from asset_distributions -> schools)
        $quadrantData = DB::table('asset_distributions as ad')
            ->join('schools', DB::raw('CAST(ad.school_id AS CHAR)'), '=', 'schools.school_id')
            ->join('districts', 'schools.district_id', '=', 'districts.id')
            ->select('districts.quadrant_id', DB::raw('COUNT(ad.id) as total_qty'))
            ->groupBy('districts.quadrant_id')
            ->get();

        $quadrantTotals = [];
        foreach ($quadrantData as $qd) {
            $quadrantTotals[$qd->quadrant_id] = $qd->total_qty;
        }

        // 6. Recently Added Assets (from asset_sources with item hierarchy)
        $recentOwnerships = DB::table('asset_sources as asrc')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->join('acquisition_sources as aq', 'asrc.acquisition_source_id', '=', 'aq.id')
            ->select(
                'aq.name as school_name',
                DB::raw("'Asset Source' as district_name"),
                'categories.name as category_name',
                'items.name as item_name',
                DB::raw('LEFT(asrc.description, 50) as sub_item_name'),
                'asrc.quantity',
                'asrc.created_at',
                DB::raw("(SELECT user FROM system_logs WHERE module = 'Import' ORDER BY created_at DESC LIMIT 1) as added_by")
            )
            ->orderByDesc('asrc.created_at')
            ->limit(10)
            ->get();

        // 7. Data for Quick Asset Entry
        $categories = DB::table('categories')->orderBy('name')->get();
        $items = DB::table('items')->orderBy('name')->get();

        // Condition and sub-item stats are no longer tracked in the old way.
        // Set placeholder values until the new UI is built.
        $serviceableCount = 0;
        $unserviceableCount = 0;
        $forRepairCount = 0;

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
            'items'
        ));
    }

    /**
     * Quick Asset Entry — temporarily disabled pending new schema integration.
     */
    public function storeQuickAsset(Request $request)
    {
        return back()->with('error', 'Quick Asset Entry is being redesigned for the new asset management system. Please use the Bulk Import feature instead.');
    }
}