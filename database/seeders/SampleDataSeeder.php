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

        // 1. Ensure Base Data
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

        // Classifications & Categories
        $classIds = [];
        for ($i = 0; $i < 3; $i++) {
            $classIds[] = DB::table('classifications')->insertGetId([
                'name' => $faker->unique()->word . ' Class',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $catIds = [];
        foreach ($classIds as $classId) {
            for ($j = 0; $j < 2; $j++) {
                $catIds[] = DB::table('categories')->insertGetId([
                    'classification_id' => $classId,
                    'name' => $faker->unique()->word . ' Category',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Items
        $itemIds = [];
        foreach ($catIds as $catId) {
            for ($k = 0; $k < 3; $k++) {
                $itemIds[] = DB::table('items')->insertGetId([
                    'category_id' => $catId,
                    'name' => $faker->unique()->word . ' Item',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
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
            $modeIds[] = DB::table('procurement_modes')->insertGetId([
                'name' => $mode,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Generating 100 Sample Records per Section

        // Section: Offices
        $officeIds = [];
        for ($i = 0; $i < 100; $i++) {
            $officeIds[] = DB::table('offices')->insertGetId([
                'school_id' => $faker->randomElement($schoolIds),
                'name' => 'Office of ' . $faker->jobTitle,
                'office_code' => strtoupper(Str::random(4)),
                'room_number' => $faker->numerify('Room ###'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Section: Supplier Personnel (Acquisition Contacts)
        $contactIds = [];
        for ($i = 0; $i < 100; $i++) {
            $contactIds[] = DB::table('acquisition_contacts')->insertGetId([
                'acquisition_source_id' => $faker->randomElement($sourceIds),
                'name' => $faker->name,
                'position' => $faker->jobTitle,
                'contact_number' => $faker->phoneNumber,
                'email' => $faker->unique()->safeEmail,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Section: Custodians
        $custodianIds = [];
        for ($i = 0; $i < 100; $i++) {
            $custodianIds[] = DB::table('custodians')->insertGetId([
                'first_name' => $faker->firstName,
                'middle_name' => $faker->lastName,
                'last_name' => $faker->lastName,
                'position' => $faker->jobTitle,
                'contact_number' => $faker->phoneNumber,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Section: Assets (Sources and Assignments)
        for ($i = 0; $i < 100; $i++) {
            $assetSourceId = DB::table('asset_sources')->insertGetId([
                'item_id' => $faker->randomElement($itemIds),
                'description' => $faker->sentence,
                'brand' => $faker->word,
                'model' => $faker->word,
                'serial_number' => $faker->unique()->numerify('SN-######'),
                'unit_of_measurement' => $faker->randomElement(['Unit', 'Pc', 'Set']),
                'acquisition_source_id' => $faker->randomElement($sourceIds),
                'asset_cost' => $faker->randomFloat(2, 500, 50000),
                'quantity' => 1,
                'estimated_useful_life' => $faker->numberBetween(3, 15),
                'acceptance_date' => $faker->date(),
                'remarks' => 'Sample Asset Data',
                'created_at' => now(),
                'updated_at' => now(),
                'procurement_mode_id' => $faker->randomElement($modeIds),
                'acquisition_contact_id' => $faker->randomElement($contactIds),
            ]);

            DB::table('asset_assignments')->insert([
                'asset_source_id' => $assetSourceId,
                'custodian_id' => $faker->randomElement($custodianIds),
                'office_id' => $faker->randomElement($officeIds),
                'condition' => 'Serviceable',
                'office_school_type' => 'School',
                'school_id' => $faker->randomElement($schoolIds),
                'nature_of_occupancy' => 'Issued',
                'location' => $faker->word,
                'property_number' => $faker->unique()->numerify('PROP-####-####'),
                'acquisition_cost' => $faker->randomFloat(2, 500, 50000),
                'acquisition_date' => $faker->date(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Section: Buildings
        // First building specs
        $bClassIds = [];
        for($i=0;$i<3;$i++){
            $bClassIds[] = DB::table('building_classifications')->insertGetId(['name' => $faker->unique()->word . ' Bldg Class', 'created_at' => now(), 'updated_at' => now()]);
        }
        $bTypeIds = [];
        foreach($bClassIds as $bcId){
            $bTypeIds[] = DB::table('building_types')->insertGetId(['building_classification_id' => $bcId, 'name' => $faker->unique()->word . ' Bldg Type', 'created_at' => now(), 'updated_at' => now()]);
        }
        $bSpecIds = [];
        foreach($bTypeIds as $btId){
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
