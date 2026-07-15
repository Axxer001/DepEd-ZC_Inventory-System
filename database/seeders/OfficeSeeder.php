<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('offices')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $mdPath = database_path('seeders/data/sdo_employees_may_2026.md');

        if (!file_exists($mdPath)) {
            $this->command->error("OfficeSeeder data file not found at {$mdPath}");
            return;
        }

        $lines = file($mdPath);

        $officeCodeMap = [
            'ACCOUNTING UNIT' => '095-ACCOUNTING-UNIT',
            'CASH UNIT' => '095-CASH-UNIT',
            'PERSONNEL UNIT' => '095-PERSONNEL-UNIT',
            'RECORDS UNIT' => '095-RECORDS-UNIT',
            'PROPERTY AND SUPPLY UNIT' => '095-PROPERTY-SUPPLY-UNIT',
            'PROCUREMENT UNIT' => '095-PROCUREMENT-UNIT',
            'ADMINISTRATIVE SECTION' => '095-ADMINISTRATIVE-SECTION',
            'OFFICE OF THE ASSISTANT SCHOOLS DIVISION SUPERINTENDENT' => '095-OASDS',
            'BUDGET UNIT' => '095-BUDGET-UNIT',
            'CURRICULUM IMPLEMENTATION DIVISION' => '095-CID',
            'PLANNING AND RESEARCH UNIT' => '095-PLANNING-RESEARCH-UNIT',
            'INFORMATION AND COMMUNICATION TECHNOLOGY UNIT' => '095-ICT',
            'LEGAL UNIT' => '095-LEGAL-UNIT',
            'OFFICE OF THE SCHOOLS DIVISION SUPERINTENDENT' => '095-OSDS',
            'SGOD - PLANNING AND RESEARCH SECTION' => '095-SGOD-PRS',
            'SGOD - SCHOOL MANAGEMENT MONITORING AND EVALUATION SECTION' => '095-SGOD-SMMES',
            'SGOD - SOCIAL MOBILIZATION AND NETWORKING SECTION' => '095-SGOD-SMNS',
            'SCHOOL GOVERNANCE AND OPERATIONS DIVISION' => '095-SGOD',
            'SGOD - EDUCATION FACILITIES SECTION' => '095-SGOD-EFS',
            'SGOD - HEALTH AND NUTRITION SECTION' => '095-SGOD-HNS',
            'SGOD-HUMAN RESOURCE DEVELOPMENT SECTION' => '095-SGOD-HRDS',
        ];

        $uniqueOffices = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] !== '|') {
                continue;
            }

            $parts = explode('|', $line);
            if (count($parts) < 10) {
                continue;
            }

            $no = trim($parts[1]);
            if ($no === 'No.' || str_contains($no, '---')) {
                continue;
            }

            $officeName = trim($parts[2]);
            if (!empty($officeName)) {
                $uniqueOffices[$officeName] = true;
            }
        }

        $officeCount = 0;
        foreach (array_keys($uniqueOffices) as $name) {
            $code = $officeCodeMap[$name] ?? ('095-' . strtoupper(preg_replace('/[^A-Za-z0-9]/', '-', $name)));
            DB::table('offices')->insert([
                'name' => $name,
                'office_id' => $code,
                'type' => 'office',
                'location' => 'Deped SDO - Baliwasan Chico rd., Zamboanga City',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $officeCount++;
        }

        $this->command->info("Successfully seeded {$officeCount} offices!");
    }
}
