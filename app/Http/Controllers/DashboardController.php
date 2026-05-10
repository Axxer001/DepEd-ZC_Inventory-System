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

        // 1. Total Assets Pool (Inventory Base)
        $itemPool = DB::table('asset_sources')->sum('quantity');
        $buildingPool = DB::table('buildings')->count();
        $totalAssets = $itemPool + $buildingPool;

        // 2. Distributed Assets (What is currently in schools/offices)
        $distributedItems = DB::table('asset_distributions')
            ->whereNotNull('office_school_type')
            ->where('office_school_type', '!=', '')
            ->whereNotNull('office_school_name')
            ->where('office_school_name', '!=', '')
            ->count();
        $distributedCount = $distributedItems + $buildingPool; // Buildings are always "distributed"

        // 3. Total Asset Value
        $itemsValue = DB::table('asset_sources')->sum(DB::raw('quantity * asset_cost'));
        $buildingsValue = DB::table('buildings')->sum('acquisition_cost');
        $totalAmount = $itemsValue + $buildingsValue;

        // 4. Asset Source Portfolio (Grouping logic)
        $pifSource = DB::table('acquisition_sources')->where('name', 'PIF Import')->first();
        $pifId = $pifSource ? $pifSource->id : null;

        // Fetch from asset_sources
        $sourceData = DB::table('asset_sources')
            ->select('acquisition_source_id', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(quantity * asset_cost) as value'))
            ->groupBy('acquisition_source_id')
            ->get();

        $portfolio = [
            'DEPED' => ['qty' => $buildingPool, 'value' => $buildingsValue, 'title' => 'DEPED ASSETS', 'image' => 'central.png'],
            'DONATED' => ['qty' => 0, 'value' => 0, 'title' => 'DONATED ASSETS', 'image' => 'donated.png'],
            'TRANSFERRED' => ['qty' => 0, 'value' => 0, 'title' => 'TRANSFERRED ASSETS', 'image' => 'transferred.png'],
            'REGIONAL' => ['qty' => 0, 'value' => 0, 'title' => 'REGIONAL ASSETS', 'image' => 'regional.png'],
        ];

        foreach ($sourceData as $sd) {
            $isDeped = is_null($sd->acquisition_source_id) || $sd->acquisition_source_id == $pifId;
            
            if ($isDeped) {
                $portfolio['DEPED']['qty'] += $sd->qty;
                $portfolio['DEPED']['value'] += $sd->value;
            } else {
                // Try to find source name
                $sourceName = DB::table('acquisition_sources')->where('id', $sd->acquisition_source_id)->value('name');
                $key = 'DEPED';
                if (stripos($sourceName, 'Donate') !== false) $key = 'DONATED';
                elseif (stripos($sourceName, 'Transfer') !== false) $key = 'TRANSFERRED';
                elseif (stripos($sourceName, 'Region') !== false) $key = 'REGIONAL';
                
                $portfolio[$key]['qty'] += $sd->qty;
                $portfolio[$key]['value'] += $sd->value;
            }
        }
        $assetSources = array_values($portfolio);

        // 5. Per-Quadrant Totals (Combined Items + Buildings)
        $itemQuadrants = DB::table('asset_distributions as ad')
            ->join('schools', DB::raw('CAST(ad.school_id AS CHAR)'), '=', 'schools.school_id')
            ->join('districts', 'schools.district_id', '=', 'districts.id')
            ->select('districts.quadrant_id', DB::raw('COUNT(ad.id) as total_qty'))
            ->groupBy('districts.quadrant_id')
            ->get();

        $buildingQuadrants = DB::table('buildings as b')
            ->join('schools', DB::raw('CAST(b.school_id AS CHAR)'), '=', 'schools.school_id')
            ->join('districts', 'schools.district_id', '=', 'districts.id')
            ->select('districts.quadrant_id', DB::raw('COUNT(b.id) as total_qty'))
            ->groupBy('districts.quadrant_id')
            ->get();

        $quadrantTotals = [];
        foreach ($itemQuadrants as $iq) $quadrantTotals[$iq->quadrant_id] = ($quadrantTotals[$iq->quadrant_id] ?? 0) + $iq->total_qty;
        foreach ($buildingQuadrants as $bq) $quadrantTotals[$bq->quadrant_id] = ($quadrantTotals[$bq->quadrant_id] ?? 0) + $bq->total_qty;

        // 6. Recent Transaction Logs
        $recentLogs = DB::table('asset_distributions')
            ->select('id', 'office_school_name as school', 'acquisition_cost as value', 'created_at as timestamp')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function($log) {
                $dt = \Carbon\Carbon::parse($log->timestamp);
                $log->timestamp = $dt->format('M d, Y | h:i A');
                $log->qty = 1;
                $log->year = $dt->year;
                $log->month = $dt->month;
                return $log;
            });

        // 7. Category Distribution & Values (Chart Data & Filters)
        $ppeStats = DB::table('asset_sources')->where('asset_cost', '>=', 50000)->select(DB::raw('SUM(quantity) as qty'), DB::raw('SUM(quantity * asset_cost) as value'))->first();
        $semiExpStats = DB::table('asset_sources')->where('asset_cost', '<', 50000)->select(DB::raw('SUM(quantity) as qty'), DB::raw('SUM(quantity * asset_cost) as value'))->first();
        
        $ppeCount = $ppeStats->qty ?? 0;
        $ppeValue = $ppeStats->value ?? 0;
        $semiExpCount = $semiExpStats->qty ?? 0;
        $semiExpValue = $semiExpStats->value ?? 0;
        
        $categoryData = [
            'buildings' => $buildingPool,
            'ppe' => $ppeCount,
            'semi_exp' => $semiExpCount,
            'items' => $itemPool - ($ppeCount + $semiExpCount) 
        ];

        $filterValues = [
            'Overall' => $totalAmount,
            'Items' => $itemsValue,
            'Buildings' => $buildingsValue,
            'PPE' => $ppeValue,
            'SemiExpendable' => $semiExpValue
        ];

        // Requirement: all assets are set to serviceable by default
        $serviceableCount = $totalAssets;
        $unserviceableCount = 0;
        $forRepairCount = 0;

        $categories = DB::table('categories')->orderBy('name')->get();
        $items = DB::table('items')->orderBy('name')->get();

        return view('dashboard', compact(
            'schools',
            'totalAssets',
            'distributedCount',
            'totalAmount',
            'serviceableCount',
            'unserviceableCount',
            'forRepairCount',
            'assetSources',
            'quadrantTotals',
            'recentLogs',
            'categoryData',
            'filterValues',
            'categories',
            'items'
        ));
    }

    public function storeQuickAsset(Request $request)
    {
        return back()->with('error', 'Quick Asset Entry is being redesigned. Please use the Bulk Import feature instead.');
    }
}