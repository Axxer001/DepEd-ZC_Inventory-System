<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$orphans = DB::table('sub_items')->whereNull('distributor_id')->get();
echo "Sub-items without distributor: " . $orphans->count() . PHP_EOL;

foreach ($orphans as $s) {
    echo "  ID={$s->id} name={$s->name} item_id={$s->item_id} qty={$s->quantity}" . PHP_EOL;
    DB::table('items')->where('id', $s->item_id)->decrement('master_quantity', $s->quantity);
    $od = DB::table('ownerships')->where('sub_item_id', $s->id)->delete();
    echo "    -> Deleted {$od} ownership(s), deducted qty from master" . PHP_EOL;
    DB::table('sub_items')->where('id', $s->id)->delete();
}

echo "Done!" . PHP_EOL;
