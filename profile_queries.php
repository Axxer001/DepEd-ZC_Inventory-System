<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$start = microtime(true);
function pt($label) {
    global $start;
    $time = microtime(true) - $start;
    echo $label . ": " . number_format($time, 4) . "s\n";
    $start = microtime(true);
}

pt('Bootstrap');

$districts = DB::table('districts')
    ->join('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
    ->select('districts.id', 'districts.name', 'quadrants.legislative_district_id', 'quadrants.name as quadrant_name')
    ->get();
pt('Districts Query');

$categories = DB::table('categories')->orderBy('name')->get();
pt('Categories Query');

$items = DB::table('items')
    ->leftJoin(DB::raw('(SELECT item_id, COALESCE(SUM(quantity), 0) as distributed_quantity FROM ownerships GROUP BY item_id) as dist'), 'items.id', '=', 'dist.item_id')
    ->select('items.id', 'items.name', 'items.category_id', 'items.master_quantity', DB::raw('COALESCE(dist.distributed_quantity, 0) as distributed_quantity'))
    ->orderBy('items.name')
    ->get();
pt('Items Query');

$subItems = DB::table('sub_items')
    ->leftJoin('stakeholders', 'sub_items.distributor_id', '=', 'stakeholders.id')
    ->select('sub_items.id', 'sub_items.name', 'sub_items.item_id', 'sub_items.quantity', 'sub_items.distributor_id', 'stakeholders.name as distributor_name')
    ->orderBy('sub_items.name')
    ->get();
pt('SubItems Query');

$allSchools = DB::table('schools')
    ->leftJoin('ownerships', 'schools.id', '=', 'ownerships.school_id')
    ->select('schools.id', 'schools.school_id', 'schools.name', DB::raw('COALESCE(SUM(ownerships.quantity), 0) as total_assets'))
    ->groupBy('schools.id', 'schools.school_id', 'schools.name')
    ->orderBy('schools.name')
    ->get();
pt('AllSchools Query');
