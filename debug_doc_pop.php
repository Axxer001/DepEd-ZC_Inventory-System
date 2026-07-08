<?php
// Quick debug script - run via: php debug_doc_pop.php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

$app->boot();

// Test ICS - assignment 60 (CYNTHIA), transfer 95
$assignmentId = 60;
$transferId = 95;
$type = 'ICS';

$transfer = DB::table('asset_transfers')->where('id', $transferId)->first();
if ($transfer) {
    $assignmentId = $transfer->asset_assignment_id;
}

$assignment = DB::table('asset_assignments as ad')
    ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
    ->join('items as i', 'asrc.item_id', '=', 'i.id')
    ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
    ->select(
        'ad.id',
        'ad.property_number',
        'ad.serial_number',
        'ad.acquisition_cost',
        'ad.acquisition_date',
        'ad.employee_id',
        'asrc.quantity',
        'asrc.unit_of_measurement',
        'asrc.asset_cost',
        'asrc.description',
        'asrc.estimated_useful_life',
        'asrc.condition',
        'i.name as item_name'
    )
    ->where('ad.id', $assignmentId)
    ->first();

echo "Assignment data:\n";
echo json_encode((array)$assignment, JSON_PRETTY_PRINT) . "\n\n";

// Now test the spreadsheet population
$filePath = __DIR__ . '/../' . $type . '.xlsx';
echo "Template path: $filePath\n";
echo "File exists: " . (file_exists($filePath) ? 'YES' : 'NO') . "\n\n";

if (file_exists($filePath)) {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('D6', 'DepEd, Division of Zamboanga City');
    $sheet->setCellValue('B11', $assignment->quantity ?? '');
    $sheet->setCellValue('C11', $assignment->unit_of_measurement ?? '');
    $sheet->setCellValue('D11', $assignment->asset_cost ?? '');
    $totalCost = ($assignment->asset_cost && $assignment->quantity) ? ($assignment->asset_cost * $assignment->quantity) : '';
    $sheet->setCellValue('E11', $totalCost);
    $sheet->setCellValue('F11', $assignment->description ?? '');
    $sheet->setCellValue('H11', $assignment->property_number ?? '');
    $sheet->setCellValue('I11', $assignment->estimated_useful_life ?? '');

    // Save and re-read to verify
    $outPath = __DIR__ . '/../debug_output_ICS.xlsx';
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($outPath);
    echo "Saved to: $outPath\n\n";

    // Re-read and print cells
    $wb2 = IOFactory::load($outPath);
    $ws2 = $wb2->getActiveSheet();
    $cells = ['D6','B11','C11','D11','E11','F11','H11','I11'];
    foreach ($cells as $cell) {
        echo "$cell = " . var_export($ws2->getCell($cell)->getValue(), true) . "\n";
    }
}
