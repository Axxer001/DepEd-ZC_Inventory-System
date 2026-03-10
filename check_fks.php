<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$fks = DB::select("SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = 'defaultdb' AND REFERENCED_TABLE_NAME = 'schools'");

if (count($fks) > 0) {
    echo "FOREIGN KEYS FOUND:\n";
    print_r($fks);
} else {
    echo "NO FOREIGN KEYS DEPEND ON schools.id\n";
}
