<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('employee_histories')->truncate();
        DB::table('employees')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $mdPath = database_path('seeders/data/sdo_employees_may_2026.md');

        if (!file_exists($mdPath)) {
            $this->command->error("EmployeeSeeder data file not found at {$mdPath}");
            return;
        }

        $lines = file($mdPath);

        // Build a name to ID map for offices in the DB
        $officeIdsByName = DB::table('offices')->pluck('id', 'name')->toArray();

        $employeeCount = 0;

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
            $lastName = trim($parts[3]);
            $firstName = trim($parts[4]);
            $middleName = trim($parts[5]);
            $sexRaw = strtoupper(trim($parts[6]));
            $position = trim($parts[7]);
            $dobRaw = trim($parts[8]);
            $empNumRaw = trim($parts[9]);

            // Map sex
            $sex = null;
            if ($sexRaw === 'MALE') {
                $sex = 'Male';
            } elseif ($sexRaw === 'FEMALE') {
                $sex = 'Female';
            }

            // Map date of birth
            $dob = null;
            if (!empty($dobRaw)) {
                try {
                    $dob = Carbon::parse($dobRaw)->format('Y-m-d');
                } catch (\Exception $e) {
                    // Fail silently or set to null
                }
            }

            // Map employee number
            $employeeId = !empty($empNumRaw) ? $empNumRaw : null;

            // Retrieve corresponding office ID
            $officeId = $officeIdsByName[$officeName] ?? null;

            // Insert Employee
            $empDbId = DB::table('employees')->insertGetId([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'middle_name' => !empty($middleName) ? $middleName : null,
                'sex' => $sex,
                'employee_id' => $employeeId,
                'position' => !empty($position) ? $position : null,
                'date_of_birth' => $dob,
                'school_id' => null,
                'office_id' => $officeId,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create Employee History entry
            DB::table('employee_histories')->insert([
                'employee_id' => $empDbId,
                'action' => 'Created',
                'description' => 'Employee record created.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $employeeCount++;
        }

        $this->command->info("Successfully seeded {$employeeCount} employees!");
    }
}
