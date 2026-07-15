<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegislativeDistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('legislative_districts')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $districts = [
            ['id' => 1, 'name' => 'LD1_West-coast', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'LD2_East-coast', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('legislative_districts')->insert($districts);

        $this->command->info("Successfully seeded 2 legislative districts!");
    }
}
