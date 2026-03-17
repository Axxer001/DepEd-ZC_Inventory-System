<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Look for the "Agent Verification Item"
$item = \Illuminate\Support\Facades\DB::table('items')->where('name', 'Agent Verification Item')->first();
if ($item) {
    echo "Item Created! ID: " . $item->id . "\n";
    $subItems = \Illuminate\Support\Facades\DB::table('sub_items')->where('item_id', $item->id)->get();
    echo "Sub Items Created: " . count($subItems) . "\n";
    
    $ownerships = \Illuminate\Support\Facades\DB::table('ownerships')->where('item_id', $item->id)->get();
    echo "Ownerships Created: " . count($ownerships) . "\n";
    foreach ($ownerships as $o) {
        $school = \Illuminate\Support\Facades\DB::table('schools')->where('id', $o->school_id)->first();
        echo "- School: {$school->name}, Item ID: {$o->item_id}, Sub ID: " . ($o->sub_item_id ?? 'NULL') . ", Qty: {$o->quantity}\n";
    }
} else {
    echo "Item not found in database.\n";
}
