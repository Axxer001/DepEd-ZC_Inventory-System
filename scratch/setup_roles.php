<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $count = \App\Models\User::query()->update(['role' => 'super_admin']);
    echo "Updated {$count} users to super_admin.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
