<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // 1. Ensure Base Data (Schools)
        $schoolIds = DB::table('schools')->pluck('id')->toArray();
        if (empty($schoolIds)) {
            for ($i = 0; $i < 5; $i++) {
                $schoolIds[] = DB::table('schools')->insertGetId([
                    'school_id' => $faker->unique()->numerify('######'),
                    'name' => $faker->company . ' School',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Classifications
        $classIds = [];
        $classNames = ['Office Equipment', 'IT Equipment', 'Furniture'];
        foreach ($classNames as $name) {
            $existing = DB::table('classifications')->where('name', $name)->first();
            if ($existing) {
                $classIds[] = $existing->id;
            } else {
                $classIds[] = DB::table('classifications')->insertGetId([
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Categories
        $catIds = [];
        $catNamesByClass = [
            'Office Equipment' => ['Appliances', 'Machinery'],
            'IT Equipment' => ['Laptops', 'Desktops', 'Printers'],
            'Furniture' => ['Chairs', 'Tables', 'Cabinets']
        ];
        foreach ($classNames as $index => $className) {
            $classId = $classIds[$index] ?? null;
            if (!$classId) continue;
            
            $categories = $catNamesByClass[$className] ?? ['Generic'];
            foreach ($categories as $catName) {
                $existing = DB::table('categories')->where('name', $catName)->first();
                if ($existing) {
                    $catIds[] = $existing->id;
                } else {
                    $catIds[] = DB::table('categories')->insertGetId([
                        'classification_id' => $classId,
                        'name' => $catName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Items
        $itemIds = [];
        foreach ($catIds as $catId) {
            $catName = DB::table('categories')->where('id', $catId)->value('name');
            $itemNames = [$catName . ' Brand A', $catName . ' Brand B'];
            foreach ($itemNames as $itemName) {
                $existing = DB::table('items')->where('name', $itemName)->first();
                if ($existing) {
                    $itemIds[] = $existing->id;
                } else {
                    $itemIds[] = DB::table('items')->insertGetId([
                        'category_id' => $catId,
                        'name' => $itemName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        $itemIds = array_values(array_unique($itemIds));

        // Sources & Modes
        $sourceIds = [];
        for ($i = 0; $i < 5; $i++) {
            $sourceIds[] = DB::table('acquisition_sources')->insertGetId([
                'name' => $faker->company,
                'source_type' => $faker->randomElement(['Internal', 'External']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $modeIds = [];
        $modes = ['PROCUREMENT', 'TRANSFER', 'DONATION'];
        foreach ($modes as $mode) {
            $existing = DB::table('procurement_modes')->where('name', $mode)->first();
            if ($existing) {
                $modeIds[] = $existing->id;
            } else {
                $modeIds[] = DB::table('procurement_modes')->insertGetId([
                    'name' => $mode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Generating 100 Sample Records per Section

        // Section: Offices
        $officeIds = [];
        for ($i = 0; $i < 100; $i++) {
            $officeIds[] = DB::table('offices')->insertGetId([
                'name' => 'Office of ' . $faker->jobTitle,
                'office_id' => '095',
                'type' => $faker->randomElement(['Administrative', 'Technical', 'Support']),
                'location' => $faker->word . ' Building',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Section: Employees (Renamed from Custodians)
        $employeeIds = [];
        for ($i = 0; $i < 100; $i++) {
            $isSchool = $faker->boolean;
            $employeeIds[] = DB::table('employees')->insertGetId([
                'first_name' => $faker->firstName,
                'middle_name' => $faker->lastName,
                'last_name' => $faker->lastName,
                'sex' => $faker->randomElement(['Male', 'Female']),
                'employee_id' => 'EMP-' . $faker->unique()->numerify('######'),
                'position' => $faker->jobTitle,
                'date_of_birth' => $faker->date('Y-m-d', '-20 years'),
                'status' => 'Active',
                'school_id' => $isSchool ? $faker->randomElement($schoolIds) : null,
                'office_id' => !$isSchool ? $faker->randomElement($officeIds) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Section: Assets (Sources and Assignments)
        for ($i = 0; $i < 100; $i++) {
            $assetCost = $faker->randomFloat(2, 500, 100000);
            $assetSourceId = DB::table('asset_sources')->insertGetId([
                'item_id' => $faker->randomElement($itemIds),
                'description' => $faker->sentence,
                'unit_of_measurement' => $faker->randomElement(['Unit', 'Pc', 'Set']),
                'acquisition_source_id' => $faker->randomElement($sourceIds),
                'asset_cost' => $assetCost,
                'quantity' => 1,
                'estimated_useful_life' => $faker->numberBetween(3, 15),
                'acceptance_date' => $faker->date(),
                'condition' => $faker->randomElement(['Good Condition', 'Needs Repair', 'Unserviceable']),
                'equipment' => $assetCost <= 49999 ? 'SEE' : 'PPE',
                'created_at' => now(),
                'updated_at' => now(),
                'procurement_mode_id' => $faker->randomElement($modeIds),
            ]);

            DB::table('asset_assignments')->insert([
                'asset_source_id' => $assetSourceId,
                'employee_id' => $faker->randomElement($employeeIds),
                'office_id' => $faker->randomElement($officeIds),
                'school_id' => $faker->randomElement($schoolIds),
                'property_number' => $faker->unique()->numerify('PROP-####-####'),
                'serial_number' => $faker->unique()->numerify('SN-######'),
                'acquisition_cost' => $assetCost,
                'acquisition_date' => $faker->date(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Section: Buildings
        // First building specs
        $bClassIds = [];
        for ($i = 0; $i < 3; $i++) {
            $bClassIds[] = DB::table('building_classifications')->insertGetId([
                'name' => $faker->unique()->word . ' Bldg Class',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $bTypeIds = [];
        foreach ($bClassIds as $bcId) {
            $bTypeIds[] = DB::table('building_types')->insertGetId([
                'building_classification_id' => $bcId,
                'name' => $faker->unique()->word . ' Bldg Type',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $bSpecIds = [];
        foreach ($bTypeIds as $btId) {
            $bSpecIds[] = DB::table('building_specs')->insertGetId([
                'building_type_id' => $btId,
                'description' => $faker->sentence,
                'storeys' => $faker->numberBetween(1, 4),
                'classrooms' => $faker->numberBetween(2, 20),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        for ($i = 0; $i < 100; $i++) {
            DB::table('building_records')->insert([
                'school_id' => $faker->randomElement($schoolIds),
                'region' => 'REGION IX',
                'division' => 'Division of Zamboanga City',
                'office_type' => 'School',
                'address' => $faker->address,
                'location' => $faker->latitude . ', ' . $faker->longitude,
                'building_spec_id' => $faker->randomElement($bSpecIds),
                'occupancy_nature' => 'Owned',
                'date_constructed' => $faker->date(),
                'acquisition_date' => $faker->date(),
                'property_number' => $faker->unique()->numerify('BLDG-####-####'),
                'acquisition_cost' => $faker->randomFloat(2, 100000, 5000000),
                'estimated_useful_life' => 25,
                'appraised_value' => $faker->randomFloat(2, 100000, 5000000),
                'appraisal_date' => $faker->date(),
                'remarks' => 'Sample Building Data',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
