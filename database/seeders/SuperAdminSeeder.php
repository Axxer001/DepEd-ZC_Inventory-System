<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seeds the one hardcoded Super Admin account (Option A).
     * Run via: php artisan db:seed --class=SuperAdminSeeder
     * Safe to re-run — uses updateOrInsert to prevent duplicates.
     */
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'superadmin@deped.gov.ph'],
            [
                'name'       => 'Super Admin',
                'email'      => 'superadmin@deped.gov.ph',
                'password'   => Hash::make('DepEd@SuperAdmin2026'),
                'role'       => 'super_admin',
                'approved'   => true,
                'dark_mode'  => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('✅  Super Admin seeded: superadmin@deped.gov.ph');
        $this->command->warn('⚠️   Change the default password immediately after first login!');
    }
}
