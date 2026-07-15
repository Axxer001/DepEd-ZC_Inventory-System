<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->call([
            SuperAdminSeeder::class,
            LegislativeDistrictSeeder::class,
            QuadrantSeeder::class,
            DistrictSeeder::class,
            SchoolsSeeder::class,
            ClassificationCategorySeeder::class,
            OfficeSeeder::class,
            EmployeeSeeder::class,
        ]);

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
