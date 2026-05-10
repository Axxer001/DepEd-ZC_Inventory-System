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
            ->whereNotNull('office_school_name')
            ->where('office_school_name', '!=', '')
            ->count();
        $distributedCount = $distributedItems + $buildingPool; // Buildings are always "distributed"

        // 3. Total Asset Value
        $itemsValue = DB::table('asset_sources')->sum(DB::raw('quantity * asset_cost'));
        $buildingsValue = DB::table('buildings')->sum('acquisition_cost');
        $totalAmount = $itemsValue + $buildingsValue;

        // 4. Asset Source Portfolio (Dynamic Acquisition Sources)
        $assetSources = DB::table('asset_sources')
            ->join('acquisition_sources', 'asset_sources.acquisition_source_id', '=', 'acquisition_sources.id')
            ->select(
                'acquisition_sources.name as title',
                DB::raw('SUM(asset_sources.quantity) as qty'),
                DB::raw('SUM(asset_sources.asset_cost * asset_sources.quantity) as value')
            )
            ->groupBy('acquisition_sources.name')
            ->get()
            ->map(function($src) {
                // Assign a generic image based on name or default
                $name = strtolower($src->title);
                $src->image = 'central.png';
                if (str_contains($name, 'donate')) $src->image = 'donated.png';
                elseif (str_contains($name, 'transfer')) $src->image = 'transferred.png';
                elseif (str_contains($name, 'region')) $src->image = 'regional.png';
                
                // Format title for UI
                $src->title = strtoupper($src->title) . ' ASSETS';
                return (array)$src;
            })
            ->toArray();

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

        // 8. Default Growth Data (5-Year Intervals)
        $growthData = $this->calculateGrowthData('gap', 5);

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
            'items',
            'growthData'
        ));
    }

    /**
     * API Endpoint for dynamic growth data
     */
    public function getGrowthData(Request $request)
    {
        $mode = $request->input('mode', 'gap');
        $value = (int)$request->input('value', 5);
        
        return response()->json($this->calculateGrowthData($mode, $value));
    }

    private function calculateGrowthData($mode, $value)
    {
        $minBldgYear = DB::table('buildings')->whereNotNull('acquisition_date')->min(DB::raw('YEAR(acquisition_date)'));
        $minAssetYear = DB::table('asset_distributions')->whereNotNull('acquisition_date')->min(DB::raw('YEAR(acquisition_date)'));
        $earliestYear = min($minBldgYear ?? date('Y'), $minAssetYear ?? date('Y'));
        $currentYear = date('Y');

        $bldgs = DB::table('buildings')->select('acquisition_cost', 'acquisition_date')->whereNotNull('acquisition_date')->get();
        $assets = DB::table('asset_distributions as ad')
            ->join('asset_sources as as', 'ad.asset_source_id', '=', 'as.id')
            ->select('ad.acquisition_cost', 'ad.acquisition_date', 'as.asset_cost')
            ->whereNotNull('ad.acquisition_date')->get();

        $labels = [];
        $data = ['buildings' => [], 'ppe' => [], 'semi_exp' => []];

        if ($mode === 'specific') {
            // Monthly for a specific year
            $year = $value ?: $currentYear;
            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            foreach (range(1, 12) as $m) {
                $labels[] = $monthNames[$m-1];
                $lastDay = date('Y-m-d', strtotime("$year-$m-01 +1 month -1 day"));
                
                $data['buildings'][] = $bldgs->where('acquisition_date', '<=', $lastDay)->sum('acquisition_cost');
                $monthAssets = $assets->where('acquisition_date', '<=', $lastDay);
                $data['ppe'][] = $monthAssets->where('asset_cost', '>=', 50000)->sum('acquisition_cost');
                $data['semi_exp'][] = $monthAssets->where('asset_cost', '<', 50000)->sum('acquisition_cost');
            }
        } else {
            // Yearly with gaps
            $gap = $value ?: 5;
            $startYear = floor($earliestYear / $gap) * $gap;
            $years = [];
            for ($y = $startYear; $y <= $currentYear; $y += $gap) {
                $years[] = (int)$y;
            }
            if (end($years) < $currentYear) $years[] = (int)$currentYear;

            foreach ($years as $y) {
                $labels[] = (string)$y;
                $lastDay = "$y-12-31";
                $data['buildings'][] = $bldgs->where('acquisition_date', '<=', $lastDay)->sum('acquisition_cost');
                $yearAssets = $assets->where('acquisition_date', '<=', $lastDay);
                $data['ppe'][] = $yearAssets->where('asset_cost', '>=', 50000)->sum('acquisition_cost');
                $data['semi_exp'][] = $yearAssets->where('asset_cost', '<', 50000)->sum('acquisition_cost');
            }
        }

        return ['labels' => $labels, 'data' => $data, 'availableYears' => range($currentYear, $earliestYear)];
    }

    public function storeQuickAsset(Request $request)
    {
        return back()->with('error', 'Quick Asset Entry is being redesigned. Please use the Bulk Import feature instead.');
    }
}