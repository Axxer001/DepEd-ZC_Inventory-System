<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

DB::statement('SET FOREIGN_KEY_CHECKS=0;');
DB::table('ownerships')->truncate();
DB::table('sub_items')->truncate();
DB::table('items')->truncate();
DB::table('categories')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "All tables cleared: ownerships, sub_items, items, categories\n";
