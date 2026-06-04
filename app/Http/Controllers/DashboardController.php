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
        $buildingPool = DB::table('building_records')->count();
        $totalAssets = $itemPool + $buildingPool;

        // 2. Distributed Assets (What is currently in schools/offices)
        $distributedItems = DB::table('asset_assignments')
            ->join('custodians', 'asset_assignments.custodian_id', '=', 'custodians.id')
            ->where(function($q) {
                $q->whereNotNull('custodians.office_id')
                  ->orWhereNotNull('custodians.school_id');
            })
            ->count();
        $distributedCount = $distributedItems + $buildingPool; // Buildings are always "distributed"

        // 3. Total Asset Value
        $itemsValue = DB::table('asset_sources')->sum(DB::raw('quantity * asset_cost'));
        $buildingsValue = DB::table('building_records')->sum('acquisition_cost');
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
        $quadrantStats = DB::table('quadrants')
            ->select('id')
            ->get()
            ->mapWithKeys(function($q) {
                $itemId = $q->id;
                
                $itemData = DB::table('asset_assignments as ad')
                    ->join('custodians as c', 'ad.custodian_id', '=', 'c.id')
                    ->leftJoin('offices as o', 'c.office_id', '=', 'o.id')
                    ->leftJoin('schools as s', function($join) {
                        $join->on('c.school_id', '=', 's.school_id')
                             ->orOn('o.school_id', '=', 's.id');
                    })
                    ->join('districts as d', 's.district_id', '=', 'd.id')
                    ->where('d.quadrant_id', $itemId)
                    ->select(
                        DB::raw('COUNT(ad.id) as qty'),
                        DB::raw('SUM(ad.acquisition_cost) as value')
                    )
                    ->first();

                $bldgData = DB::table('building_records as b')
                    ->join('schools', 'b.school_id', '=', 'schools.id')
                    ->join('districts', 'schools.district_id', '=', 'districts.id')
                    ->where('districts.quadrant_id', $itemId)
                    ->select(
                        DB::raw('COUNT(b.id) as qty'),
                        DB::raw('SUM(b.acquisition_cost) as value')
                    )
                    ->first();

                return [$itemId => [
                    'qty' => ($itemData->qty ?? 0) + ($bldgData->qty ?? 0),
                    'value' => ($itemData->value ?? 0) + ($bldgData->value ?? 0)
                ]];
            })
            ->toArray();

        // 6. Recent Transaction Logs
        $recentLogs = DB::table('asset_assignments as ad')
            ->leftJoin('custodians as c', 'ad.custodian_id', '=', 'c.id')
            ->leftJoin('offices as o', 'c.office_id', '=', 'o.id')
            ->leftJoin('schools as s', function($join) {
                $join->on('c.school_id', '=', 's.school_id')
                     ->orOn('o.school_id', '=', 's.id');
            })
            ->select('ad.id', DB::raw('COALESCE(s.name, o.name, ad.location) as school'), 'ad.acquisition_cost as value', 'ad.created_at as timestamp')
            ->orderByDesc('ad.created_at')
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

        $totalForPercent = array_sum($categoryData);
        $categoryPercents = [];
        foreach ($categoryData as $key => $val) {
            $categoryPercents[$key] = $totalForPercent > 0 ? round(($val / $totalForPercent) * 100) : 0;
        }

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
            'quadrantStats',
            'recentLogs',
            'categoryData',
            'categoryPercents',
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
        $minBldgYear = DB::table('building_records')->whereNotNull('acquisition_date')->min(DB::raw('YEAR(acquisition_date)'));
        $minAssetYear = DB::table('asset_assignments')->whereNotNull('acquisition_date')->min(DB::raw('YEAR(acquisition_date)'));
        $earliestYear = min($minBldgYear ?? date('Y'), $minAssetYear ?? date('Y'));
        $currentYear = date('Y');

        $bldgs = DB::table('building_records')->select('acquisition_cost', 'acquisition_date')->whereNotNull('acquisition_date')->get();
        $assets = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->select('ad.acquisition_cost', 'ad.acquisition_date', 'asrc.asset_cost')
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