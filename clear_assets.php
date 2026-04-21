<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Starting asset removal process...\n";

DB::statement('SET FOREIGN_KEY_CHECKS=0;');

$tablesToTruncate = [
    'asset_transactions',
    'ownerships',
    'sub_items',
    'items',
    'system_logs'
];

foreach ($tablesToTruncate as $table) {
    if (Schema::hasTable($table)) {
        DB::table($table)->truncate();
        echo "Truncated: {$table}\n";
    } else {
        echo "Table not found (skipping): {$table}\n";
    }
}

DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "All existing assets and related logs have been removed successfully.\n";
