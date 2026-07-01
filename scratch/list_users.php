<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::all();
foreach ($users as $user) {
    echo "ID: " . $user->id . " | Name: " . $user->name . " | Email: " . $user->email . " | Role: " . $user->role . " | Approved: " . ($user->approved ? 'Yes' : 'No') . "\n";
}
