<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = [
    'asset_assignments',
    'asset_sources',
    'acquisition_contacts',
    'acquisition_sources',
    'procurement_modes',
    'items',
    'categories',
    'classifications',
    'building_records',
    'building_specs',
    'building_types',
    'building_classifications',
    'asset_transfers',
    'system_logs'
];

echo "Starting comprehensive database cleanup...\n";

DB::statement('SET FOREIGN_KEY_CHECKS=0;');

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        DB::table($table)->truncate();
        echo "Truncated table: $table\n";
    } else {
        echo "Table does not exist, skipping: $table\n";
    }
}

DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "Comprehensive database cleanup completed successfully.\n";
