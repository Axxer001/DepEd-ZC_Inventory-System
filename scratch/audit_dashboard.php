<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TOTAL ASSETS ===" . PHP_EOL;
$itemPool = DB::table('asset_sources')->sum('quantity');
$buildingPool = DB::table('buildings')->count();
$totalAssets = $itemPool + $buildingPool;
echo "item_pool (asset_sources SUM qty): $itemPool" . PHP_EOL;
echo "building_pool (buildings COUNT): $buildingPool" . PHP_EOL;
echo "totalAssets: $totalAssets" . PHP_EOL;

echo PHP_EOL . "=== DISTRIBUTED ===" . PHP_EOL;
$distributedItems = DB::table('asset_distributions')->whereNotNull('office_school_name')->where('office_school_name', '!=', '')->count();
echo "distributedItems: $distributedItems" . PHP_EOL;
echo "distributedCount (incl buildings): " . ($distributedItems + $buildingPool) . PHP_EOL;

echo PHP_EOL . "=== TOTAL VALUE ===" . PHP_EOL;
$itemsValue = DB::table('asset_sources')->sum(DB::raw('quantity * asset_cost'));
$buildingsValue = DB::table('buildings')->sum('acquisition_cost');
$totalAmount = $itemsValue + $buildingsValue;
echo "itemsValue (asset_sources qty*cost): $itemsValue" . PHP_EOL;
echo "buildingsValue (buildings acq_cost): $buildingsValue" . PHP_EOL;
echo "totalAmount: $totalAmount" . PHP_EOL;

echo PHP_EOL . "=== PPE / SEMI-EXPENDABLE ===" . PHP_EOL;
$ppe = DB::table('asset_sources')->where('asset_cost', '>=', 50000)->selectRaw('SUM(quantity) as qty, SUM(quantity * asset_cost) as value')->first();
$semi = DB::table('asset_sources')->where('asset_cost', '<', 50000)->selectRaw('SUM(quantity) as qty, SUM(quantity * asset_cost) as value')->first();
echo "PPE (cost>=50k)  qty: " . ($ppe->qty ?? 0) . " | value: " . ($ppe->value ?? 0) . PHP_EOL;
echo "SemiExp (cost<50k) qty: " . ($semi->qty ?? 0) . " | value: " . ($semi->value ?? 0) . PHP_EOL;

echo PHP_EOL . "=== CATEGORY DATA (Portfolio Chart) ===" . PHP_EOL;
$ppeCount = $ppe->qty ?? 0;
$semiExpCount = $semi->qty ?? 0;
$categoryData = [
    'buildings' => $buildingPool,
    'ppe'       => $ppeCount,
    'semi_exp'  => $semiExpCount,
    'items'     => $itemPool - ($ppeCount + $semiExpCount),
];
$totalForPercent = array_sum($categoryData);
echo "buildings: " . $categoryData['buildings'] . PHP_EOL;
echo "ppe: " . $categoryData['ppe'] . PHP_EOL;
echo "semi_exp: " . $categoryData['semi_exp'] . PHP_EOL;
echo "items (residual): " . $categoryData['items'] . PHP_EOL;
echo "totalForPercent: $totalForPercent" . PHP_EOL;
foreach ($categoryData as $k => $v) {
    $pct = $totalForPercent > 0 ? round(($v / $totalForPercent) * 100) : 0;
    echo "  $k => $pct%" . PHP_EOL;
}

echo PHP_EOL . "=== CONDITIONS (Hardcoded) ===" . PHP_EOL;
echo "Serviceable: $totalAssets (= totalAssets, no DB condition column used)" . PHP_EOL;
echo "For Repair: 0 (hardcoded)" . PHP_EOL;
echo "Unserviceable: 0 (hardcoded)" . PHP_EOL;

echo PHP_EOL . "=== QUADRANT DISTRIBUTION ===" . PHP_EOL;
$quadrants = DB::table('quadrants')->select('id', 'name')->get();
foreach ($quadrants as $q) {
    $itemData = DB::table('asset_distributions as ad')
        ->join('schools', DB::raw('CAST(ad.school_id AS CHAR)'), '=', 'schools.school_id')
        ->join('districts', 'schools.district_id', '=', 'districts.id')
        ->where('districts.quadrant_id', $q->id)
        ->selectRaw('COUNT(ad.id) as qty, SUM(ad.acquisition_cost) as value')
        ->first();
    $bldgData = DB::table('buildings as b')
        ->join('schools', DB::raw('CAST(b.school_id AS CHAR)'), '=', 'schools.school_id')
        ->join('districts', 'schools.district_id', '=', 'districts.id')
        ->where('districts.quadrant_id', $q->id)
        ->selectRaw('COUNT(b.id) as qty, SUM(b.acquisition_cost) as value')
        ->first();
    $qty   = ($itemData->qty ?? 0) + ($bldgData->qty ?? 0);
    $value = ($itemData->value ?? 0) + ($bldgData->value ?? 0);
    echo "Quadrant {$q->id} ({$q->name}): qty=$qty, value=$value" . PHP_EOL;
}

echo PHP_EOL . "=== ASSET SOURCE PORTFOLIO ===" . PHP_EOL;
$sources = DB::table('asset_sources')
    ->join('acquisition_sources', 'asset_sources.acquisition_source_id', '=', 'acquisition_sources.id')
    ->selectRaw('acquisition_sources.name as title, SUM(asset_sources.quantity) as qty, SUM(asset_sources.asset_cost * asset_sources.quantity) as value')
    ->groupBy('acquisition_sources.name')
    ->get();
if ($sources->isEmpty()) {
    echo "(no acquisition sources found)" . PHP_EOL;
} else {
    foreach ($sources as $s) {
        echo "  {$s->title}: qty={$s->qty}, value={$s->value}" . PHP_EOL;
    }
}

echo PHP_EOL . "=== GROWTH DATA EARLIEST YEAR ===" . PHP_EOL;
$minBldg  = DB::table('buildings')->whereNotNull('acquisition_date')->min(DB::raw('YEAR(acquisition_date)'));
$minAsset = DB::table('asset_distributions')->whereNotNull('acquisition_date')->min(DB::raw('YEAR(acquisition_date)'));
echo "Earliest building year: " . ($minBldg ?? 'none') . PHP_EOL;
echo "Earliest asset dist year: " . ($minAsset ?? 'none') . PHP_EOL;
