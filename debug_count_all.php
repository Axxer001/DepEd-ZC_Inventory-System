<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Suppliers: " . DB::table('suppliers')->count() . "\n";
echo "Acquisition Sources: " . DB::table('acquisition_sources')->count() . "\n";
echo "Procurement Modes: " . DB::table('procurement_modes')->count() . "\n";
echo "Asset Sources: " . DB::table('asset_sources')->count() . "\n";
echo "Asset Assignments: " . DB::table('asset_assignments')->count() . "\n";
