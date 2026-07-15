<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassificationCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::table('classifications')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $classifications = [
            1 => 'Land Improvements',
            2 => 'Infrastructure Assets',
            3 => 'Machinery and Equipment',
            4 => 'Transportation and Equipment',
            5 => 'Furniture Fixtures and Books',
            6 => 'Heritage Assets',
            7 => 'Other Property Plant and Equipment',
        ];

        $categories = [
            ['name' => 'LAND', 'classification_id' => 1, 'ppe_code' => '0101', 'see_code' => '0000'],
            ['name' => 'BUILDINGS', 'classification_id' => 2, 'ppe_code' => '0401', 'see_code' => '0000'],
            ['name' => 'SCHOOL BUILDINGS', 'classification_id' => 2, 'ppe_code' => '0402', 'see_code' => '0000'],
            ['name' => 'MACHINERY', 'classification_id' => 3, 'ppe_code' => '0501', 'see_code' => '0501'],
            ['name' => 'OFFICE EQUIPMENT', 'classification_id' => 3, 'ppe_code' => '0502', 'see_code' => '0502'],
            ['name' => 'ICT EQUIPMENT', 'classification_id' => 3, 'ppe_code' => '0503', 'see_code' => '0503'],
            ['name' => 'AGRICULTURAL & FORESTRY EQUIPMENT', 'classification_id' => 3, 'ppe_code' => '0504', 'see_code' => '0504'],
            ['name' => 'MARINE & FISHERY EQUIPMENT', 'classification_id' => 3, 'ppe_code' => '0505', 'see_code' => '0505'],
            ['name' => 'COMMUNICATION EQUIPMENT', 'classification_id' => 3, 'ppe_code' => '0507', 'see_code' => '0507'],
            ['name' => 'DRR EQUIPMENT', 'classification_id' => 3, 'ppe_code' => '0509', 'see_code' => '0508'],
            ['name' => 'MEDICAL EQUIPMENT', 'classification_id' => 3, 'ppe_code' => '0509', 'see_code' => '0510'],
            ['name' => 'PRINTING EQUIPMENT', 'classification_id' => 3, 'ppe_code' => '0512', 'see_code' => '0511'],
            ['name' => 'SPORTS EQUIPMENT', 'classification_id' => 3, 'ppe_code' => '0513', 'see_code' => '0512'],
            ['name' => 'TECHNICAL & SCIENTIFIC EQUIPMENT', 'classification_id' => 3, 'ppe_code' => '0514', 'see_code' => '0513'],
            ['name' => 'OTHER MACHINERY & EQUIPMENT', 'classification_id' => 3, 'ppe_code' => '0599', 'see_code' => '0519'],
            ['name' => 'MOTOR VEHICLES EQUIPMENT', 'classification_id' => 4, 'ppe_code' => '0601', 'see_code' => '0000'],
            ['name' => 'FURNITURES & FIXTURES', 'classification_id' => 5, 'ppe_code' => '0701', 'see_code' => '0601'],
            ['name' => 'BOOKS', 'classification_id' => 5, 'ppe_code' => '0702', 'see_code' => '0602'],
        ];

        // Insert Classifications
        foreach ($classifications as $id => $name) {
            DB::table('classifications')->insert([
                'id' => $id,
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insert Categories
        foreach ($categories as $cat) {
            DB::table('categories')->insert([
                'name' => $cat['name'],
                'classification_id' => $cat['classification_id'],
                'see_category_code' => $cat['see_code'],
                'ppe_category_code' => $cat['ppe_code'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("Successfully seeded " . count($classifications) . " classifications and " . count($categories) . " categories!");
    }
}
