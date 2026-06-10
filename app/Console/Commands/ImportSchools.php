<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportSchools extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:schools {--file= : Path to the schools Excel directory file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import schools directory from Excel sheet to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->option('file') ?: base_path('../SCHOOL DIRECTORY FOR DEPED ZC INVENTORY.xlsx');

        if (!file_exists($filePath)) {
            $this->error("Excel file not found at: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Loading Excel file: {$filePath}");
        $spreadsheet = IOFactory::load($filePath);
        $this->info("Excel file loaded successfully.");

        // Fetch districts from the database to map district_id
        $districts = DB::table('districts')->get();
        $districtMap = [];
        foreach ($districts as $d) {
            $normalized = strtolower(trim(str_replace('district', '', strtolower($d->name))));
            $districtMap[$normalized] = $d->id;
        }

        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        // --- 1. PUBLIC SHEET ---
        if ($spreadsheet->sheetNameExists('PUBLIC')) {
            $this->info("Processing PUBLIC sheet...");
            $sheet = $spreadsheet->getSheetByName('PUBLIC');
            $highestRow = $sheet->getHighestRow();
            $currentLegislativeDistrict = 'LEGISLATIVE DISTRICT 1 - WEST COAST';

            for ($row = 5; $row <= $highestRow; $row++) {
                $c1 = trim($sheet->getCell('A' . $row)->getValue() ?? '');
                $c2 = trim($sheet->getCell('B' . $row)->getValue() ?? '');
                $c3 = trim($sheet->getCell('C' . $row)->getValue() ?? '');
                $c4 = trim($sheet->getCell('D' . $row)->getValue() ?? '');

                if (empty($c1) && empty($c2)) {
                    continue;
                }

                // Check if this is a Legislative District header row
                if (str_contains(strtoupper($c1), 'LEGISLATIVE DISTRICT')) {
                    $currentLegislativeDistrict = $c1;
                    $this->info("Switching to: {$currentLegislativeDistrict}");
                    continue;
                }

                if (!ctype_digit($c1)) {
                    $skippedCount++;
                    continue;
                }

                $schoolId = $c1;
                $schoolName = strtoupper($c2);
                $location = trim($currentLegislativeDistrict . ', ' . $c4);
                $districtId = $this->findDistrictId($c4, $location, $districtMap);

                $result = $this->upsertSchool([
                    'school_id' => $schoolId,
                    'name' => $schoolName,
                    'type' => 'Public',
                    'location' => $location,
                    'district_id' => $districtId,
                ]);

                if ($result === 'inserted') {
                    $importedCount++;
                } else {
                    $updatedCount++;
                }
            }
        }

        // --- 2. PRIVATE SHEET ---
        if ($spreadsheet->sheetNameExists('PRIVATE')) {
            $this->info("Processing PRIVATE sheet...");
            $sheet = $spreadsheet->getSheetByName('PRIVATE');
            $highestRow = $sheet->getHighestRow();

            for ($row = 7; $row <= $highestRow; $row++) {
                $c2 = trim($sheet->getCell('B' . $row)->getValue() ?? '');
                $c3 = trim($sheet->getCell('C' . $row)->getValue() ?? '');
                $c4 = trim($sheet->getCell('D' . $row)->getValue() ?? '');
                $c12 = trim($sheet->getCell('L' . $row)->getValue() ?? '');

                if (empty($c2) && empty($c3)) {
                    continue;
                }

                if (!ctype_digit($c2)) {
                    $skippedCount++;
                    continue;
                }

                $schoolId = $c2;
                $schoolName = strtoupper($c3);
                $location = $c4;
                $districtId = $this->findDistrictId($c12, $location, $districtMap);

                $result = $this->upsertSchool([
                    'school_id' => $schoolId,
                    'name' => $schoolName,
                    'type' => 'Private',
                    'location' => $location,
                    'district_id' => $districtId,
                ]);

                if ($result === 'inserted') {
                    $importedCount++;
                } else {
                    $updatedCount++;
                }
            }
        }

        // --- 3. SUCLUC SHEET ---
        if ($spreadsheet->sheetNameExists('SUCLUC')) {
            $this->info("Processing SUCLUC sheet...");
            $sheet = $spreadsheet->getSheetByName('SUCLUC');
            $highestRow = $sheet->getHighestRow();

            for ($row = 7; $row <= $highestRow; $row++) {
                $c2 = trim($sheet->getCell('B' . $row)->getValue() ?? '');
                $c3 = trim($sheet->getCell('C' . $row)->getValue() ?? '');
                $c4 = trim($sheet->getCell('D' . $row)->getValue() ?? '');
                $c12 = trim($sheet->getCell('L' . $row)->getValue() ?? '');

                if (empty($c2) && empty($c3)) {
                    continue;
                }

                if (!ctype_digit($c2)) {
                    $skippedCount++;
                    continue;
                }

                $schoolId = $c2;
                $schoolName = strtoupper($c3);
                $location = $c4;
                $districtId = $this->findDistrictId($c12, $location, $districtMap);

                $result = $this->upsertSchool([
                    'school_id' => $schoolId,
                    'name' => $schoolName,
                    'type' => 'SUC/LUC',
                    'location' => $location,
                    'district_id' => $districtId,
                ]);

                if ($result === 'inserted') {
                    $importedCount++;
                } else {
                    $updatedCount++;
                }
            }
        }

        $this->info("Import process finished.");
        $this->info("Total Imported (New): {$importedCount}");
        $this->info("Total Updated (Existing): {$updatedCount}");
        $this->info("Total Skipped (Non-data/Headers): {$skippedCount}");

        return Command::SUCCESS;
    }

    /**
     * Find district ID by value or address content.
     */
    private function findDistrictId($value, $address, $districtMap)
    {
        if (empty($value)) {
            $value = '';
        }

        $value = strtolower(trim($value));

        if ($value === 'central') {
            $value = 'zamboanga central';
        }

        // Strip "district" if present
        $value = str_replace('district', '', $value);
        $value = trim($value);

        if (isset($districtMap[$value])) {
            return $districtMap[$value];
        }

        // Try searching inside address
        if (!empty($address)) {
            $addressLower = strtolower($address);
            foreach ($districtMap as $name => $id) {
                if (str_contains($addressLower, $name)) {
                    return $id;
                }
            }
        }

        return null;
    }

    /**
     * Upsert a school record without breaking references.
     */
    private function upsertSchool(array $data)
    {
        $existing = DB::table('schools')->where('school_id', $data['school_id'])->first();

        if ($existing) {
            DB::table('schools')->where('school_id', $data['school_id'])->update([
                'name' => $data['name'],
                'type' => $data['type'],
                'location' => $data['location'],
                'district_id' => $data['district_id'],
                'updated_at' => now(),
            ]);
            return 'updated';
        } else {
            DB::table('schools')->insert([
                'school_id' => $data['school_id'],
                'name' => $data['name'],
                'type' => $data['type'],
                'location' => $data['location'],
                'district_id' => $data['district_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return 'inserted';
        }
    }
}
