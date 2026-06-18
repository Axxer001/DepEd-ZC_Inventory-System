<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Disable foreign key checks temporarily if needed
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    // Truncate users and pending registrations
    DB::table('users')->truncate();
    DB::table('pending_registrations')->truncate();
    DB::table('sessions')->truncate(); // Clear old sessions

    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    
    echo "All existing users, sessions, and pending registrations have been successfully deleted.";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
