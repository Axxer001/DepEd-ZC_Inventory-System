<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('schools')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $mdPath = database_path('seeders/data/public_schools_directory.md');

        if (!file_exists($mdPath)) {
            $this->command->error("SchoolsSeeder data file not found at {$mdPath}");
            return;
        }

        $lines = file($mdPath);

        $districtMap = [
            'TETUAN DISTRICT' => 1,
            'LABUAN DISTRICT' => 2,
            'AYALA DISTRICT' => 3,
            'BALIWASAN DISTRICT' => 4,
            'STA. MARIA DISTRICT' => 5,
            'ZAMBOANGA CENTRAL DISTRICT' => 6,
            'VITALI DISTRICT' => 7,
            'CURUAN DISTRICT' => 8,
            'MANICAHAN DISTRICT' => 9,
            'PUTIK DISTRICT' => 10,
            'MERCEDES DISTRICT' => 11,
            'TALON-TALON DISTRICT' => 12,
        ];

        $schoolsToInsert = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] !== '|') {
                continue;
            }

            $parts = explode('|', $line);
            if (count($parts) < 6) {
                continue;
            }

            $no = trim($parts[1]);
            if ($no === 'No.' || str_contains($no, '---')) {
                continue;
            }

            $schoolId = trim($parts[2]);
            $schoolName = trim($parts[3]);
            $districtName = strtoupper(trim($parts[4]));
            $schoolType = trim($parts[5]);

            $districtId = $districtMap[$districtName] ?? null;

            $schoolsToInsert[] = [
                'school_id' => $schoolId,
                'name' => $schoolName,
                'district_id' => $districtId,
                'type' => $schoolType,
                'location' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (count($schoolsToInsert) > 0) {
            DB::table('schools')->insert($schoolsToInsert);
            $this->command->info("Successfully seeded " . count($schoolsToInsert) . " schools!");
        }
    }
}
