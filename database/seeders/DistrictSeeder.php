<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('districts')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $districts = [
            ['id' => 1, 'name' => 'Tetuan District', 'quadrant_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Labuan District', 'quadrant_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Ayala District', 'quadrant_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Baliwasan District', 'quadrant_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'Sta. Maria District', 'quadrant_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'name' => 'Zamboanga Central District', 'quadrant_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'name' => 'Vitali District', 'quadrant_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'name' => 'Curuan District', 'quadrant_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'name' => 'Manicahan District', 'quadrant_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'name' => 'Putik District', 'quadrant_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'name' => 'Mercedes District', 'quadrant_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'name' => 'Talon-Talon District', 'quadrant_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('districts')->insert($districts);
    }
}
