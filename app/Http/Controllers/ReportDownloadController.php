<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReportDownloadController extends Controller
{
    private function buildQuery(Request $request)
    {
        $type = $request->input('report_type');
        $filters = $request->input('filters', []);
        
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?: [];
        }

        $query = DB::table('asset_distributions')
            ->join('asset_sources', 'asset_distributions.asset_source_id', '=', 'asset_sources.id')
            ->join('items', 'asset_sources.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->join('classifications', 'categories.classification_id', '=', 'classifications.id')
            ->join('acquisition_sources', 'asset_sources.acquisition_source_id', '=', 'acquisition_sources.id')
            ->select(
                'asset_distributions.*',
                'asset_sources.description',
                'asset_sources.unit_of_measurement',
                'asset_sources.asset_cost',
                'asset_sources.quantity',
                'asset_sources.estimated_useful_life',
                'asset_sources.mode_of_acquisition',
                'asset_sources.source_personnel',
                'asset_sources.personnel_position',
                'asset_sources.acceptance_date',
                'asset_sources.remarks',
                'items.name as article',
                'categories.name as category',
                'classifications.name as classification',
                'acquisition_sources.name as acq_source'
            );

        if ($type === 'RPCPPE') {
            $query->where('asset_distributions.acquisition_cost', '>=', 50000);
        } elseif ($type === 'RPCSP') {
            $query->where('asset_distributions.acquisition_cost', '<', 50000);
        }

        if (!empty($filters['classification'])) {
            $query->where('classifications.name', $filters['classification']);
        }
        if (!empty($filters['category'])) {
            $query->where('categories.name', $filters['category']);
        }
        if (!empty($filters['article'])) {
            $query->where('items.name', $filters['article']);
        }
        if (!empty($filters['schoolName'])) {
            $query->where('asset_distributions.office_school_name', $filters['schoolName']);
        }
        if (!empty($filters['source'])) {
            $query->where('acquisition_sources.name', $filters['source']);
        }
        if (!empty($filters['mode'])) {
            $query->where('asset_sources.mode_of_acquisition', $filters['mode']);
        }
        if (!empty($filters['dateAcquired'])) {
            $query->whereDate('asset_sources.acceptance_date', $filters['dateAcquired']);
        }

        // Sorting by Cost
        $sortCost = $filters['sortCost'] ?? null;
        if ($sortCost === 'low_to_high') {
            $query->orderBy('asset_distributions.acquisition_cost', 'asc');
        } elseif ($sortCost === 'high_to_low') {
            $query->orderBy('asset_distributions.acquisition_cost', 'desc');
        } else {
            $query->orderBy('asset_distributions.id', 'asc');
        }

        return $query;
    }

    public function getPreview(Request $request)
    {
        $query = $this->buildQuery($request);
        $rows = $query->limit(500)->get();
        return response()->json(['rows' => $rows]);
    }

    public function getFilterOptions(Request $request)
    {
        $type = $request->input('report_type');

        $baseQuery = DB::table('asset_distributions')
            ->join('asset_sources', 'asset_distributions.asset_source_id', '=', 'asset_sources.id')
            ->join('items', 'asset_sources.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->join('classifications', 'categories.classification_id', '=', 'classifications.id')
            ->join('acquisition_sources', 'asset_sources.acquisition_source_id', '=', 'acquisition_sources.id');

        if ($type === 'RPCPPE') {
            $baseQuery->where('asset_distributions.acquisition_cost', '>=', 50000);
        } elseif ($type === 'RPCSP') {
            $baseQuery->where('asset_distributions.acquisition_cost', '<', 50000);
        }

        $classifications = (clone $baseQuery)->pluck('classifications.name')->unique()->sort()->values();
        $categories = (clone $baseQuery)->pluck('categories.name')->unique()->sort()->values();
        $items = (clone $baseQuery)->pluck('items.name')->unique()->sort()->values();
        $schools = (clone $baseQuery)->whereNotNull('asset_distributions.office_school_name')->where('asset_distributions.office_school_name', '!=', '')->pluck('asset_distributions.office_school_name')->unique()->sort()->values();
        $sources = (clone $baseQuery)->pluck('acquisition_sources.name')->unique()->sort()->values();
        $modes = (clone $baseQuery)->whereNotNull('asset_sources.mode_of_acquisition')->pluck('asset_sources.mode_of_acquisition')->unique()->sort()->values();

        return response()->json([
            'classifications' => $classifications,
            'categories' => $categories,
            'items' => $items,
            'schools' => $schools,
            'sources' => $sources,
            'modes' => $modes
        ]);
    }

    public function download(Request $request)
    {
        $type = $request->input('report_type');
        if (!in_array($type, ['RPCPPE', 'RPCSP', 'PIF'])) {
            return back()->withErrors('Invalid report type.');
        }

        $query = $this->buildQuery($request);
        $rows = $query->get();

        $templatePath = base_path('../' . $type . '.xlsx');
        if (!file_exists($templatePath)) {
            return back()->withErrors("Template file {$type}.xlsx not found.");
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Dynamic Classification/Category Header for RPCPPE/RPCSP
        $filters = $request->input('filters', []);
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?: [];
        }
        $classification = $filters['classification'] ?? null;
        $category = $filters['category'] ?? null;
        
        $reportTitle = $category ?: $classification;

        if ($reportTitle && ($type === 'RPCPPE' || $type === 'RPCSP')) {
            $sheet->setCellValue('B4', $reportTitle);
            $sheet->getStyle('B4')->getFont()
                ->setBold(true)
                ->setItalic(true)
                ->setUnderline(\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE)
                ->getColor()->setRGB('FF0000');
        }

        if ($type === 'PIF') {
            // Update Agency Name and Address for PIF
            $sheet->setCellValue('A2', 'Department of Education - Division of Zamboanga City');
            $sheet->setCellValue('A3', 'Baliwasan Chico Road, Zamboanga City');
            // Update "As of" Date dynamically to current creation date
            $sheet->setCellValue('A5', 'As of ' . date('F d, Y'));
        }

        $startRow = 11;
        $signatureRow = null;
        if ($type === 'RPCPPE') {
            $startRow = 15;
            $signatureRow = 22;
        } elseif ($type === 'RPCSP') {
            $startRow = 15;
            $signatureRow = 44;
        }
        
        $currentRow = $startRow;

        foreach ($rows as $row) {
            if ($type === 'RPCPPE' || $type === 'RPCSP') {
                if ($currentRow >= $signatureRow) {
                    $sheet->insertNewRowBefore($currentRow, 1);
                    $signatureRow++;
                }
                
                // Force duplication of row 15 style to EVERY row (including pre-existing 16-21) to fix template inconsistencies
                if ($currentRow > $startRow) {
                    $baseRow = 15;
                    for ($col = 'A'; $col <= 'N'; $col++) {
                        $style = $sheet->getStyle($col . $baseRow);
                        $sheet->duplicateStyle($style, $col . $currentRow);
                    }
                    $baseHeight = $sheet->getRowDimension($baseRow)->getRowHeight();
                    if ($baseHeight != -1) {
                        $sheet->getRowDimension($currentRow)->setRowHeight($baseHeight);
                    }
                }

                $sheet->setCellValue('B' . $currentRow, $row->article);
                $sheet->setCellValue('C' . $currentRow, $row->description);
                $sheet->setCellValue('D' . $currentRow, $row->property_number);
                $sheet->setCellValue('E' . $currentRow, $row->unit_of_measurement);
                $sheet->setCellValue('F' . $currentRow, $row->asset_cost);
                $sheet->setCellValue('G' . $currentRow, $row->quantity);
                $sheet->setCellValue('H' . $currentRow, $row->quantity);
                $sheet->setCellValue('I' . $currentRow, ''); // Shortage/Overage
                $sheet->setCellValue('J' . $currentRow, ''); // Shortage/Overage
                $sheet->setCellValue('K' . $currentRow, $row->remarks);
            } else {
                // Asset PIF Mapping (24 columns)
                if ($type === 'PIF' && $currentRow > $startRow) {
                    $baseRow = 11;
                    for ($col = 'A'; $col <= 'X'; $col++) {
                        $style = $sheet->getStyle($col . $baseRow);
                        $sheet->duplicateStyle($style, $col . $currentRow);
                    }
                    $baseHeight = $sheet->getRowDimension($baseRow)->getRowHeight();
                    if ($baseHeight != -1) {
                        $sheet->getRowDimension($currentRow)->setRowHeight($baseHeight);
                    }
                }
                
                $sheet->setCellValue('A' . $currentRow, $row->region);
                $sheet->setCellValue('B' . $currentRow, $row->division);
                $sheet->setCellValue('C' . $currentRow, $row->office_school_type);
                $sheet->setCellValue('D' . $currentRow, $row->school_id);
                $sheet->setCellValue('E' . $currentRow, $row->office_school_name);
                $sheet->setCellValue('F' . $currentRow, $row->classification);
                $sheet->setCellValue('G' . $currentRow, $row->category);
                $sheet->setCellValue('H' . $currentRow, $row->article);
                $sheet->setCellValue('I' . $currentRow, $row->description);
                $sheet->setCellValue('J' . $currentRow, $row->unit_of_measurement);
                $sheet->setCellValue('K' . $currentRow, $row->asset_cost);
                $sheet->setCellValue('L' . $currentRow, $row->quantity);
                $sheet->setCellValue('M' . $currentRow, $row->estimated_useful_life);
                $sheet->setCellValue('N' . $currentRow, $row->property_number);
                $sheet->setCellValue('O' . $currentRow, $row->nature_of_occupancy);
                $sheet->setCellValue('P' . $currentRow, $row->location);
                $sheet->setCellValue('Q' . $currentRow, $row->acq_source);
                $sheet->setCellValue('R' . $currentRow, $row->mode_of_acquisition);
                $sheet->setCellValue('S' . $currentRow, $row->source_personnel);
                $sheet->setCellValue('T' . $currentRow, $row->personnel_position);
                $sheet->setCellValue('U' . $currentRow, $row->acquisition_cost); // Total Acquisition Cost
                $sheet->setCellValue('V' . $currentRow, $row->acceptance_date);
                $sheet->setCellValue('W' . $currentRow, $row->acquisition_date);
                $sheet->setCellValue('X' . $currentRow, $row->remarks);
            }
            
            $currentRow++;
        }

        // Delete excess rows between the last data row and the signature row
        if (($type === 'RPCPPE' || $type === 'RPCSP') && $signatureRow !== null) {
            if ($currentRow < $signatureRow) {
                $countToDelete = $signatureRow - $currentRow;
                $sheet->removeRow($currentRow, $countToDelete);
                $signatureRow = $currentRow; // Update it as we've shifted it up
            }
        }

        // Ensure at least 1 blank row below the last asset row before signatories
        if (($type === 'RPCPPE' || $type === 'RPCSP') && $signatureRow !== null) {
            if ($currentRow >= $signatureRow) {
                $sheet->insertNewRowBefore($signatureRow, 1);
                // The new row is blank, but we might want to clear its height or keep it standard
                $sheet->getRowDimension($signatureRow)->setRowHeight(20); 
                $signatureRow++;
            }
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $fileName = $type . '_Report_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
