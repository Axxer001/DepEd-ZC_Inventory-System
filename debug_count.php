<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$totalUsers = DB::table('users')->count();
$approvedMainAdmins = DB::table('users')
    ->where('approved', true)
    ->where('system_type', 'main')
    ->whereIn('role', ['admin', 'super_admin'])
    ->count();

$approvedSchoolAdmins = DB::table('users')
    ->where('approved', true)
    ->where('system_type', 'school')
    ->count();

echo "Total Users: $totalUsers\n";
echo "Approved Main Admins: $approvedMainAdmins\n";
echo "Approved School Admins: $approvedSchoolAdmins\n";
