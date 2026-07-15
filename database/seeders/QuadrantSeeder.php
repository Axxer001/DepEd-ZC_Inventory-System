<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuadrantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \Illuminate\Support\Facades\DB::table('quadrants')->delete();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $quadrants = [
            ['name' => 'Quadrant 1.1', 'legislative_district_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Quadrant 1.2', 'legislative_district_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Quadrant 2.1', 'legislative_district_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Quadrant 2.2', 'legislative_district_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ];

        \Illuminate\Support\Facades\DB::table('quadrants')->insert($quadrants);
    }
}
