<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isSchool = $user->isSchoolSystem();
        $school_id = $user->school_id;

        $cacheKey = $isSchool ? "dashboard:school:{$school_id}:stats" : 'dashboard:main:stats';

        $stats = Cache::remember($cacheKey, 60, function () use ($isSchool, $school_id) {
            if (!$isSchool) {
                $schools = DB::table('schools')
                    ->join('districts', 'schools.district_id', '=', 'districts.id')
                    ->select('schools.*', 'districts.quadrant_id')
                    ->orderBy('name')
                    ->get();

                // 1. Total Assets Pool (Inventory Base)
                $itemPool    = DB::table('asset_sources')->sum('quantity');
                $totalAssets = $itemPool;

                // 2. Distributed Assets (What is currently in schools/offices)
                $distributedCount = DB::table('asset_assignments')
                    ->join('asset_sources', 'asset_assignments.asset_source_id', '=', 'asset_sources.id')
                    ->where(function ($q) {
                        $q->whereNotNull('asset_assignments.employee_id')
                          ->orWhereNotNull('asset_assignments.school_id')
                          ->orWhereNotNull('asset_assignments.office_id');
                    })
                    ->sum('asset_sources.quantity');

                // 3. Total Asset Value
                $itemsValue  = DB::table('asset_sources')->sum(DB::raw('quantity * asset_cost'));
                $totalAmount = $itemsValue;

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
                    ->map(function ($src) {
                        $name = strtolower($src->title);
                        $src->image = 'central.png';
                        if (str_contains($name, 'donate')) $src->image = 'donated.png';
                        elseif (str_contains($name, 'transfer')) $src->image = 'transferred.png';
                        elseif (str_contains($name, 'region')) $src->image = 'regional.png';
                        $src->title = strtoupper($src->title) . ' ASSETS';
                        return (array) $src;
                    })
                    ->toArray();

                // 5. Per-Quadrant Totals
                $quadrantStats = DB::table('quadrants')
                    ->select('id')
                    ->get()
                    ->mapWithKeys(function ($q) {
                        $itemId   = $q->id;
                        $itemData = DB::table('asset_assignments as ad')
                            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
                            ->leftJoin('employees as c', 'ad.employee_id', '=', 'c.id')
                            ->leftJoin('schools as s', function ($join) {
                                $join->on('ad.school_id', '=', 's.id')
                                     ->orOn('c.school_id', '=', 's.id');
                            })
                            ->join('districts as d', 's.district_id', '=', 'd.id')
                            ->where('d.quadrant_id', $itemId)
                            ->select(
                                DB::raw('SUM(asrc.quantity) as qty'),
                                DB::raw('SUM(ad.acquisition_cost) as value')
                            )
                            ->first();
                        return [$itemId => [
                            'qty'   => ($itemData->qty ?? 0),
                            'value' => ($itemData->value ?? 0),
                        ]];
                    })
                    ->toArray();

                // 6. Recent Transaction Logs
                $recentLogs = DB::table('asset_assignments as ad')
                    ->leftJoin('employees as c', 'ad.employee_id', '=', 'c.id')
                    ->leftJoin('offices as o_cus', 'c.office_id', '=', 'o_cus.id')
                    ->leftJoin('schools as s_cus', 'c.school_id', '=', 's_cus.id')
                    ->leftJoin('offices as o_dir', 'ad.office_id', '=', 'o_dir.id')
                    ->leftJoin('schools as s_dir', 'ad.school_id', '=', 's_dir.id')
                    ->select(
                        'ad.id',
                        DB::raw('COALESCE(s_dir.name, o_dir.name, s_cus.name, o_cus.name, "Warehouse") as school'),
                        'ad.acquisition_cost as value',
                        'ad.created_at as timestamp'
                    )
                    ->orderByDesc('ad.created_at')
                    ->limit(10)
                    ->get()
                    ->map(function ($log) {
                        $dt = \Carbon\Carbon::parse($log->timestamp);
                        $log->timestamp = $dt->format('M d, Y | h:i A');
                        $log->qty   = 1;
                        $log->year  = $dt->year;
                        $log->month = $dt->month;
                        return $log;
                    });

                // 7. Category Distribution & Values
                $ppeStats     = DB::table('asset_sources')->where('asset_cost', '>=', 50000)->select(DB::raw('SUM(quantity) as qty'), DB::raw('SUM(quantity * asset_cost) as value'))->first();
                $semiExpStats = DB::table('asset_sources')->where('asset_cost', '<', 50000)->select(DB::raw('SUM(quantity) as qty'), DB::raw('SUM(quantity * asset_cost) as value'))->first();

                $ppeCount     = $ppeStats->qty ?? 0;
                $ppeValue     = $ppeStats->value ?? 0;
                $semiExpCount = $semiExpStats->qty ?? 0;
                $semiExpValue = $semiExpStats->value ?? 0;

                $categoryData = ['ppe' => $ppeCount, 'semi_exp' => $semiExpCount];

                $totalForPercent  = array_sum($categoryData);
                $categoryPercents = [];
                foreach ($categoryData as $key => $val) {
                    $categoryPercents[$key] = $totalForPercent > 0 ? round(($val / $totalForPercent) * 100) : 0;
                }

                $filterValues = [
                    'Overall'        => $totalAmount,
                    'PPE'            => $ppeValue,
                    'SemiExpendable' => $semiExpValue,
                ];

                // Asset condition summary
                $conditions = DB::table('asset_sources')
                    ->select('condition', DB::raw('SUM(quantity) as qty'))
                    ->groupBy('condition')
                    ->get();

                $serviceableCount   = 0;
                $unserviceableCount = 0;
                $forRepairCount     = 0;

                foreach ($conditions as $c) {
                    $cond = strtolower(trim($c->condition ?? ''));
                    if ($cond === 'good condition' || $cond === 'serviceable') {
                        $serviceableCount += $c->qty;
                    } elseif ($cond === 'needs repair' || $cond === 'for repair') {
                        $forRepairCount += $c->qty;
                    } elseif ($cond === 'unserviceable') {
                        $unserviceableCount += $c->qty;
                    } else {
                        $serviceableCount += $c->qty;
                    }
                }

                // 9. Classification Breakdown
                $classificationBreakdown = DB::table('asset_sources as asrc')
                    ->join('items', 'asrc.item_id', '=', 'items.id')
                    ->join('categories as cat', 'items.category_id', '=', 'cat.id')
                    ->join('classifications as class', 'cat.classification_id', '=', 'class.id')
                    ->select(
                        'class.name',
                        DB::raw('SUM(asrc.quantity) as qty'),
                        DB::raw('SUM(asrc.quantity * asrc.asset_cost) as value')
                    )
                    ->groupBy('class.id', 'class.name')
                    ->get();

                // 10. Top 5 Schools by Asset Value
                $topSchools = DB::table('asset_assignments as ad')
                    ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
                    ->leftJoin('employees as emp', 'ad.employee_id', '=', 'emp.id')
                    ->join('schools as s', function ($join) {
                        $join->on('ad.school_id', '=', 's.id')
                             ->orOn(function ($q) {
                                 $q->whereNull('ad.school_id')
                                   ->whereColumn('emp.school_id', 's.id');
                             });
                    })
                    ->select(
                        's.name',
                        DB::raw('SUM(asrc.quantity) as total_units'),
                        DB::raw('SUM(ad.acquisition_cost) as total_value')
                    )
                    ->groupBy('s.id', 's.name')
                    ->orderByDesc('total_value')
                    ->limit(5)
                    ->get();

                // 11. System Oversight Counters
                $pendingRegistrationsCount = DB::table('pending_registrations')->count();
                $blockedAccountsCount      = DB::table('blocked_accounts')->count();

                // 12. Organizational Footprint Stats
                $schoolsCount   = DB::table('schools')->count();
                $officesCount   = DB::table('offices')->count();
                $employeesCount = DB::table('employees')->count();
                $buildingsCount = DB::table('building_records')->count();
                $buildingsValue = DB::table('building_records')->sum('acquisition_cost');
            } else {
                $schools = DB::table('schools')
                    ->where('id', $school_id)
                    ->get();

                // 1. Total Assets Pool (Inventory Base) for school
                $totalAssets = DB::table('asset_assignments as ad')
                    ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
                    ->where(function ($q) use ($school_id) {
                        $q->where('ad.school_id', $school_id)
                          ->orWhere('e.school_id', $school_id);
                    })
                    ->count();

                // 2. Distributed Assets
                $distributedCount = $totalAssets;

                // 3. Total Asset Value
                $totalAmount = DB::table('asset_assignments as ad')
                    ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
                    ->where(function ($q) use ($school_id) {
                        $q->where('ad.school_id', $school_id)
                          ->orWhere('e.school_id', $school_id);
                    })
                    ->sum('ad.acquisition_cost');

                // 4. Asset Source Portfolio (Dynamic Acquisition Sources)
                $assetSources = DB::table('asset_assignments as ad')
                    ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
                    ->join('acquisition_sources', 'asrc.acquisition_source_id', '=', 'acquisition_sources.id')
                    ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
                    ->where(function ($q) use ($school_id) {
                        $q->where('ad.school_id', $school_id)
                          ->orWhere('e.school_id', $school_id);
                    })
                    ->select(
                        'acquisition_sources.name as title',
                        DB::raw('COUNT(ad.id) as qty'),
                        DB::raw('SUM(ad.acquisition_cost) as value')
                    )
                    ->groupBy('acquisition_sources.name')
                    ->get()
                    ->map(function ($src) {
                        $name = strtolower($src->title);
                        $src->image = 'central.png';
                        if (str_contains($name, 'donate')) $src->image = 'donated.png';
                        elseif (str_contains($name, 'transfer')) $src->image = 'transferred.png';
                        elseif (str_contains($name, 'region')) $src->image = 'regional.png';
                        $src->title = strtoupper($src->title) . ' ASSETS';
                        return (array) $src;
                    })
                    ->toArray();

                $quadrantStats = [];

                // 6. Recent Transaction Logs
                $recentLogs = DB::table('asset_assignments as ad')
                    ->leftJoin('employees as c', 'ad.employee_id', '=', 'c.id')
                    ->leftJoin('offices as o_cus', 'c.office_id', '=', 'o_cus.id')
                    ->leftJoin('schools as s_cus', 'c.school_id', '=', 's_cus.id')
                    ->leftJoin('offices as o_dir', 'ad.office_id', '=', 'o_dir.id')
                    ->leftJoin('schools as s_dir', 'ad.school_id', '=', 's_dir.id')
                    ->where(function ($q) use ($school_id) {
                        $q->where('ad.school_id', $school_id)
                          ->orWhere('c.school_id', $school_id);
                    })
                    ->select(
                        'ad.id',
                        DB::raw('COALESCE(s_dir.name, o_dir.name, s_cus.name, o_cus.name, "Warehouse") as school'),
                        'ad.acquisition_cost as value',
                        'ad.created_at as timestamp'
                    )
                    ->orderByDesc('ad.created_at')
                    ->limit(10)
                    ->get()
                    ->map(function ($log) {
                        $dt = \Carbon\Carbon::parse($log->timestamp);
                        $log->timestamp = $dt->format('M d, Y | h:i A');
                        $log->qty   = 1;
                        $log->year  = $dt->year;
                        $log->month = $dt->month;
                        return $log;
                    });

                // 7. Category Distribution & Values
                $ppeStats = DB::table('asset_assignments as ad')
                    ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
                    ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
                    ->where(function ($q) use ($school_id) {
                        $q->where('ad.school_id', $school_id)
                          ->orWhere('e.school_id', $school_id);
                    })
                    ->where('asrc.asset_cost', '>=', 50000)
                    ->select(DB::raw('COUNT(ad.id) as qty'), DB::raw('SUM(ad.acquisition_cost) as value'))
                    ->first();

                $semiExpStats = DB::table('asset_assignments as ad')
                    ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
                    ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
                    ->where(function ($q) use ($school_id) {
                        $q->where('ad.school_id', $school_id)
                          ->orWhere('e.school_id', $school_id);
                    })
                    ->where('asrc.asset_cost', '<', 50000)
                    ->select(DB::raw('COUNT(ad.id) as qty'), DB::raw('SUM(ad.acquisition_cost) as value'))
                    ->first();

                $ppeCount     = $ppeStats->qty ?? 0;
                $ppeValue     = $ppeStats->value ?? 0;
                $semiExpCount = $semiExpStats->qty ?? 0;
                $semiExpValue = $semiExpStats->value ?? 0;

                $categoryData = ['ppe' => $ppeCount, 'semi_exp' => $semiExpCount];

                $totalForPercent  = array_sum($categoryData);
                $categoryPercents = [];
                foreach ($categoryData as $key => $val) {
                    $categoryPercents[$key] = $totalForPercent > 0 ? round(($val / $totalForPercent) * 100) : 0;
                }

                $filterValues = [
                    'Overall'        => $totalAmount,
                    'PPE'            => $ppeValue,
                    'SemiExpendable' => $semiExpValue,
                ];

                // Asset condition summary
                $conditions = DB::table('asset_assignments as ad')
                    ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
                    ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
                    ->where(function ($q) use ($school_id) {
                        $q->where('ad.school_id', $school_id)
                          ->orWhere('e.school_id', $school_id);
                    })
                    ->select('asrc.condition', DB::raw('COUNT(ad.id) as qty'))
                    ->groupBy('asrc.condition')
                    ->get();

                $serviceableCount   = 0;
                $unserviceableCount = 0;
                $forRepairCount     = 0;

                foreach ($conditions as $c) {
                    $cond = strtolower(trim($c->condition ?? ''));
                    if ($cond === 'good condition' || $cond === 'serviceable') {
                        $serviceableCount += $c->qty;
                    } elseif ($cond === 'needs repair' || $cond === 'for repair') {
                        $forRepairCount += $c->qty;
                    } elseif ($cond === 'unserviceable') {
                        $unserviceableCount += $c->qty;
                    } else {
                        $serviceableCount += $c->qty;
                    }
                }

                // 9. Classification Breakdown
                $classificationBreakdown = DB::table('asset_assignments as ad')
                    ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
                    ->join('items', 'asrc.item_id', '=', 'items.id')
                    ->join('categories as cat', 'items.category_id', '=', 'cat.id')
                    ->join('classifications as class', 'cat.classification_id', '=', 'class.id')
                    ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
                    ->where(function ($q) use ($school_id) {
                        $q->where('ad.school_id', $school_id)
                          ->orWhere('e.school_id', $school_id);
                    })
                    ->select(
                        'class.name',
                        DB::raw('COUNT(ad.id) as qty'),
                        DB::raw('SUM(ad.acquisition_cost) as value')
                    )
                    ->groupBy('class.id', 'class.name')
                    ->get();

                $topSchools = [];

                // 11. System Oversight Counters
                $pendingRegistrationsCount = 0;
                $blockedAccountsCount      = 0;

                // 12. Organizational Footprint Stats
                $schoolsCount   = 1;
                $officesCount   = DB::table('offices')->where('school_id', $school_id)->count();
                $employeesCount = DB::table('employees')->where('school_id', $school_id)->count();
                $buildingsCount = DB::table('building_records')->where('school_id', $school_id)->count();
                $buildingsValue = DB::table('building_records')->where('school_id', $school_id)->sum('acquisition_cost');
            }

            $categories = DB::table('categories')->orderBy('name')->get();
            $items      = DB::table('items')->orderBy('name')->get();

            return compact(
                'schools', 'totalAssets', 'distributedCount', 'totalAmount',
                'serviceableCount', 'unserviceableCount', 'forRepairCount',
                'assetSources', 'quadrantStats', 'recentLogs',
                'categoryData', 'categoryPercents', 'filterValues',
                'categories', 'items', 'classificationBreakdown', 'topSchools',
                'pendingRegistrationsCount', 'blockedAccountsCount',
                'schoolsCount', 'officesCount', 'employeesCount',
                'buildingsCount', 'buildingsValue'
            );
        });

        // Growth data is not included in the main stats cache because it accepts
        // dynamic mode/value parameters via the API endpoint below.
        $growthData = $this->calculateGrowthData($isSchool ? 'specific' : 'gap', $isSchool ? date('Y') : 5, $school_id);

        // Global notice is intentionally NOT cached (admins expect changes to show instantly)
        $globalNotice = \App\Models\GlobalNotice::where('active', true)->latest()->first();

        return view('dashboard', array_merge($stats, compact('growthData', 'globalNotice')));
    }

    /**
     * API Endpoint for dynamic growth data.
     *
     * Cached per mode+value combination for 60 seconds.
     */
    public function getGrowthData(Request $request)
    {
        $user = auth()->user();
        $isSchool = $user->isSchoolSystem();
        $school_id = $user->school_id;

        $mode  = $request->input('mode', $isSchool ? 'specific' : 'gap');
        $value = (int) $request->input('value', $isSchool ? date('Y') : 5);

        // Cache key per mode/value pair so different chart views are cached independently
        $cacheKey = $isSchool ? "dashboard:school:{$school_id}:growth:{$mode}:{$value}" : "dashboard:main:growth:{$mode}:{$value}";

        $data = Cache::remember($cacheKey, 60, function () use ($mode, $value, $school_id) {
            return $this->calculateGrowthData($mode, $value, $school_id);
        });

        return response()->json($data);
    }

    private function calculateGrowthData($mode, $value, $schoolId = null)
    {
        $minAssetYear = DB::table('asset_assignments')->whereNotNull('acquisition_date')->min(DB::raw('YEAR(acquisition_date)'));
        $earliestYear = $minAssetYear ?? date('Y');
        $currentYear = date('Y');

        $query = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->select('ad.acquisition_cost', 'ad.acquisition_date', 'asrc.asset_cost')
            ->whereNotNull('ad.acquisition_date');

        if ($schoolId) {
            $query->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
                  ->where(function ($q) use ($schoolId) {
                      $q->where('ad.school_id', $schoolId)
                        ->orWhere('e.school_id', $schoolId);
                  });
        }

        $assets = $query->get();

        $labels = [];
        $data = ['ppe' => [], 'semi_exp' => []];

        if ($mode === 'specific') {
            // Monthly for a specific year
            $year = $value ?: $currentYear;
            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            foreach (range(1, 12) as $m) {
                $labels[] = $monthNames[$m-1];
                $lastDay = date('Y-m-d', strtotime("$year-$m-01 +1 month -1 day"));
                
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