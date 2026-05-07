<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReportDownloadController extends Controller
{
    private function buildQuery(Request $request)
    {
        $type = $request->input('report_type'); // 'RPCPPE' or 'RPCSP'
        $filters = $request->input('filters', []);
        
        // If filters came from a form submit, they might be JSON stringified
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?: [];
        }

        $query = DB::table('asset_distributions')
            ->join('asset_sources', 'asset_distributions.asset_source_id', '=', 'asset_sources.id')
            ->join('items', 'asset_sources.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select(
                'asset_distributions.*',
                'asset_sources.description',
                'items.name as article',
                'categories.name as classification'
            );

        if ($type === 'RPCPPE') {
            $query->where('asset_distributions.acquisition_cost', '>=', 50000);
        } elseif ($type === 'RPCSP') {
            $query->where('asset_distributions.acquisition_cost', '<', 50000);
        } elseif ($type === 'PIF') {
            // PIF includes all assets or specific logic if needed, currently no cost filter
        }

        if (!empty($filters['classification'])) {
            $query->where('categories.name', $filters['classification']);
        }
        if (!empty($filters['schoolType'])) {
            $query->where('asset_distributions.office_school_type', $filters['schoolType']);
        }
        if (!empty($filters['schoolName'])) {
            $query->where('asset_distributions.office_school_name', $filters['schoolName']);
        }
        if (!empty($filters['article'])) {
            $query->where('items.name', $filters['article']);
        }
        if (!empty($filters['location'])) {
            $query->where('asset_distributions.location', $filters['location']);
        }
        if (!empty($filters['year'])) {
            $query->whereYear('asset_distributions.acquisition_date', $filters['year']);
        }
        if (!empty($filters['month'])) {
            $query->whereMonth('asset_distributions.acquisition_date', $filters['month']);
        }

        return $query->orderBy('asset_distributions.id', 'asc');
    }

    public function getPreview(Request $request)
    {
        $query = $this->buildQuery($request);
        $rows = $query->limit(500)->get(); // Limit preview to 500 rows for performance
        return response()->json(['rows' => $rows]);
    }

    public function getFilterOptions(Request $request)
    {
        $type = $request->input('report_type');

        $baseQuery = DB::table('asset_distributions')
            ->join('asset_sources', 'asset_distributions.asset_source_id', '=', 'asset_sources.id')
            ->join('items', 'asset_sources.item_id', '=', 'items.id');

        if ($type === 'RPCPPE') {
            $baseQuery->where('asset_distributions.acquisition_cost', '>=', 50000);
        } elseif ($type === 'RPCSP') {
            $baseQuery->where('asset_distributions.acquisition_cost', '<', 50000);
        } elseif ($type === 'PIF') {
            // No specific cost filter for PIF
        }

        $schools = (clone $baseQuery)->whereNotNull('asset_distributions.office_school_name')->where('asset_distributions.office_school_name', '!=', '')->pluck('asset_distributions.office_school_name')->unique()->sort()->values();
        $articles = (clone $baseQuery)->pluck('items.name')->unique()->sort()->values();
        $locations = (clone $baseQuery)->whereNotNull('asset_distributions.location')->where('asset_distributions.location', '!=', '')->pluck('asset_distributions.location')->unique()->sort()->values();

        return response()->json([
            'schools' => $schools,
            'articles' => $articles,
            'locations' => $locations
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

        // Update Agency Name and Address
        $sheet->setCellValue('A2', 'Department of Education - Division of Zamboanga City');
        $sheet->setCellValue('A3', 'Baliwasan Chico Road, Zamboanga City');

        // Update "As of" Date dynamically to current creation date
        $sheet->setCellValue('A5', 'As of ' . date('F d, Y'));

        $startRow = 11;
        $currentRow = $startRow;

        foreach ($rows as $row) {
            $sheet->setCellValue('A' . $currentRow, $row->region);
            $sheet->setCellValue('B' . $currentRow, $row->division);
            $sheet->setCellValue('C' . $currentRow, $row->office_school_type);
            $sheet->setCellValue('D' . $currentRow, $row->school_id);
            $sheet->setCellValue('E' . $currentRow, $row->office_school_name);
            $sheet->setCellValue('F' . $currentRow, $row->article);
            $sheet->setCellValue('G' . $currentRow, $row->description);
            $sheet->setCellValue('H' . $currentRow, $row->classification);
            $sheet->setCellValue('I' . $currentRow, $row->nature_of_occupancy);
            $sheet->setCellValue('J' . $currentRow, $row->location);
            $sheet->setCellValue('K' . $currentRow, $row->acquisition_date);
            $sheet->setCellValue('L' . $currentRow, $row->property_number);
            $sheet->setCellValue('M' . $currentRow, $row->acquisition_cost);
            
            $currentRow++;
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
