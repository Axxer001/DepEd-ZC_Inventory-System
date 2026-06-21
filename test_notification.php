<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = \App\Models\User::find(2);
if ($admin) {
    $dummy = (object)['description' => 'Test Item'];
    $admin->notify(new \App\Notifications\AssetAddedNotification($dummy));
    echo "Count: " . \Illuminate\Support\Facades\DB::table('notifications')->count() . "\n";
} else {
    echo "Admin not found\n";
}
