<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $count = \App\Models\User::query()->update(['password' => bcrypt('password123')]);
    echo "Updated {$count} users' passwords to 'password123'.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
