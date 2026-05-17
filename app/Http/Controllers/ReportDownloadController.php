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

        $tab = $filters['tab'] ?? 'distribution';

        if ($tab === 'source') {
            $query = DB::table('asset_sources')
                ->leftJoin('items', 'asset_sources.item_id', '=', 'items.id')
                ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
                ->leftJoin('classifications', 'categories.classification_id', '=', 'classifications.id')
                ->leftJoin('acquisition_sources', 'asset_sources.acquisition_source_id', '=', 'acquisition_sources.id')
                ->leftJoin('procurement_modes as pm', 'asset_sources.procurement_mode_id', '=', 'pm.id')
                ->leftJoin('acquisition_contacts as ac', 'asset_sources.acquisition_contact_id', '=', 'ac.id')
                ->select(
                    'asset_sources.*',
                    'items.name as article',
                    'categories.name as category',
                    'classifications.name as classification',
                    'acquisition_sources.name as acq_source',
                    'pm.name as mode_of_acquisition',
                    'ac.name as source_personnel',
                    'ac.position as personnel_position',
                    DB::raw('(asset_sources.asset_cost * asset_sources.quantity) as acquisition_cost'),
                    DB::raw("'Region IX' as region"),
                    DB::raw("'Division of Zamboanga City' as division"),
                    DB::raw("NULL as office_school_type"),
                    DB::raw("NULL as school_id"),
                    DB::raw("NULL as location"),
                    DB::raw("NULL as nature_of_occupancy"),
                    DB::raw("NULL as location"),
                    DB::raw("NULL as property_number"),
                    DB::raw("NULL as acquisition_date")
                );
        } else {
            $query = DB::table('asset_assignments')
                ->leftJoin('asset_sources', 'asset_assignments.asset_source_id', '=', 'asset_sources.id')
                ->leftJoin('items', 'asset_sources.item_id', '=', 'items.id')
                ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
                ->leftJoin('classifications', 'categories.classification_id', '=', 'classifications.id')
                ->leftJoin('acquisition_sources', 'asset_sources.acquisition_source_id', '=', 'acquisition_sources.id')
                ->leftJoin('procurement_modes as pm', 'asset_sources.procurement_mode_id', '=', 'pm.id')
                ->leftJoin('acquisition_contacts as ac', 'asset_sources.acquisition_contact_id', '=', 'ac.id')
                ->leftJoin('custodians as cus', 'asset_assignments.custodian_id', '=', 'cus.id')
                ->select(
                    'asset_assignments.*',
                    DB::raw("'Region IX' as region"),
                    DB::raw("'Division of Zamboanga City' as division"),
                    'asset_sources.description',
                    'asset_sources.unit_of_measurement',
                    'asset_sources.asset_cost',
                    'asset_sources.quantity',
                    'asset_sources.quantity as source_qty',
                    'asset_sources.estimated_useful_life',
                    'pm.name as mode_of_acquisition',
                    'ac.name as source_personnel',
                    'ac.position as personnel_position',
                    'asset_sources.acceptance_date',
                    'asset_sources.remarks',
                    'items.name as article',
                    'categories.name as category',
                    'classifications.name as classification',
                    'acquisition_sources.name as acq_source',
                    'cus.first_name as custodian_first_name',
                    'cus.middle_name as custodian_middle_name',
                    'cus.last_name as custodian_last_name',
                    'cus.position as custodian_position',
                    'cus.contact_number as custodian_contact_number'
                );
        }

        if ($type === 'RPCPPE') {
            $col = ($tab === 'source') ? 'asset_sources.asset_cost' : 'asset_assignments.acquisition_cost';
            $query->where($col, '>=', 50000);
        } elseif ($type === 'RPCSP') {
            $col = ($tab === 'source') ? 'asset_sources.asset_cost' : 'asset_assignments.acquisition_cost';
            $query->where($col, '<', 50000);
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
        if (!empty($filters['schoolName']) && $tab === 'distribution') {
            $query->where('asset_assignments.location', $filters['schoolName']);
        }
        if (!empty($filters['source'])) {
            $query->where('acquisition_sources.name', $filters['source']);
        }
        if (!empty($filters['mode'])) {
            $query->where('pm.name', $filters['mode']);
        }
        if (!empty($filters['dateAcquired'])) {
            $query->whereDate('asset_sources.acceptance_date', $filters['dateAcquired']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search, $tab) {
                $q->where('asset_sources.description', 'LIKE', "%$search%")
                  ->orWhere('items.name', 'LIKE', "%$search%");
                if ($tab === 'distribution') {
                    $q->orWhere('asset_assignments.property_number', 'LIKE', "%$search%");
                }
            });
        }

        // Sorting by Cost
        $sortCost = $filters['sortCost'] ?? null;
        if ($sortCost === 'low_to_high') {
            $col = ($tab === 'source') ? 'asset_sources.asset_cost' : 'asset_assignments.acquisition_cost';
            $query->orderBy($col, 'asc');
        } elseif ($sortCost === 'high_to_low') {
            $col = ($tab === 'source') ? 'asset_sources.asset_cost' : 'asset_assignments.acquisition_cost';
            $query->orderBy($col, 'desc');
        } else {
            $query->orderBy($tab === 'source' ? 'asset_sources.id' : 'asset_assignments.id', 'asc');
        }

        // Data Integrity: Empty Column check
        if (!empty($filters['emptyCol'])) {
            $eCol = $filters['emptyCol'];
            $dbCol = null;
            
            if ($eCol === 'article') $dbCol = 'items.name';
            elseif ($eCol === 'category') $dbCol = 'categories.name';
            elseif ($eCol === 'classification') $dbCol = 'classifications.name';
            elseif ($eCol === 'description') $dbCol = 'asset_sources.description';
            elseif ($eCol === 'unit_of_measurement') $dbCol = 'asset_sources.unit_of_measurement';
            elseif ($eCol === 'acq_source') $dbCol = 'acquisition_sources.name';
            elseif ($eCol === 'mode_of_acquisition') $dbCol = 'pm.name';
            elseif ($eCol === 'acceptance_date') $dbCol = 'asset_sources.acceptance_date';
            
            // Distribution-specific columns
            if ($tab === 'distribution') {
                if ($eCol === 'property_number') $dbCol = 'asset_assignments.property_number';
                elseif ($eCol === 'school_id') $dbCol = 'asset_assignments.school_id';
                elseif ($eCol === 'school_name') $dbCol = 'asset_assignments.location';
                elseif ($eCol === 'occupancy') $dbCol = 'asset_assignments.nature_of_occupancy';
                elseif ($eCol === 'location') $dbCol = 'asset_assignments.location';
                elseif ($eCol === 'acquisition_date') $dbCol = 'asset_assignments.acquisition_date';
            }
            
            if ($dbCol) {
                $query->where(function($q) use ($dbCol, $eCol) {
                    $q->whereNull($dbCol)
                      ->orWhere($dbCol, '')
                      ->orWhere($dbCol, '0')
                      ->orWhere($dbCol, 'unclassified')
                      ->orWhere($dbCol, 'uncategorized')
                      ->orWhere($dbCol, 'Unclassified')
                      ->orWhere($dbCol, 'Uncategorized');
                });
            }
        }

        return $query;
    }

    public function getPreview(Request $request)
    {
        $query = $this->buildQuery($request);
        $rows = $query->limit(500)->get();
        return response()->json(['rows' => $rows]);
    }

    public function getAssetSuggestions(Request $request)
    {
        $search = $request->input('q');
        $tab = $request->input('tab', 'distribution');
        
        if (empty($search)) return response()->json([]);

        if ($tab === 'source') {
            $results = DB::table('asset_sources')
                ->leftJoin('items', 'asset_sources.item_id', '=', 'items.id')
                ->where('asset_sources.description', 'LIKE', "%$search%")
                ->orWhere('items.name', 'LIKE', "%$search%")
                ->select(DB::raw('COALESCE(asset_sources.description, items.name) as suggestion'))
                ->distinct()
                ->limit(10)
                ->pluck('suggestion');
        } else {
            $results = DB::table('asset_assignments')
                ->leftJoin('asset_sources', 'asset_assignments.asset_source_id', '=', 'asset_sources.id')
                ->leftJoin('items', 'asset_sources.item_id', '=', 'items.id')
                ->where('asset_assignments.property_number', 'LIKE', "%$search%")
                ->orWhere('asset_sources.description', 'LIKE', "%$search%")
                ->orWhere('items.name', 'LIKE', "%$search%")
                ->select(DB::raw('COALESCE(asset_assignments.property_number, asset_sources.description, items.name) as suggestion'))
                ->distinct()
                ->limit(10)
                ->pluck('suggestion');
        }

        return response()->json($results);
    }

    public function getEditPreview(Request $request)
    {
        $query = $this->buildQuery($request);
        // Explicitly select the IDs needed for updating, overriding any conflicts
        $query->addSelect(
            'asset_assignments.id as dist_id',
            'asset_sources.id as src_id',
            'items.id as item_id',
            'acquisition_sources.id as acq_source_id'
        );
        $rows = $query->limit(500)->get();
        return response()->json(['rows' => $rows]);
    }

    public function getFilterOptions(Request $request)
    {
        $type = $request->input('report_type');

        $baseQuery = DB::table('asset_sources')
            ->leftJoin('asset_assignments', 'asset_sources.id', '=', 'asset_assignments.asset_source_id')
            ->leftJoin('items', 'asset_sources.item_id', '=', 'items.id')
            ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('classifications', 'categories.classification_id', '=', 'classifications.id')
            ->leftJoin('acquisition_sources', 'asset_sources.acquisition_source_id', '=', 'acquisition_sources.id')
            ->leftJoin('procurement_modes as pm', 'asset_sources.procurement_mode_id', '=', 'pm.id');

        if ($type === 'RPCPPE') {
            $baseQuery->where('asset_sources.asset_cost', '>=', 50000);
        } elseif ($type === 'RPCSP') {
            $baseQuery->where('asset_sources.asset_cost', '<', 50000);
        }

        $classifications = (clone $baseQuery)->whereNotNull('classifications.name')->pluck('classifications.name')->unique()->sort()->values();
        $categories = (clone $baseQuery)->whereNotNull('categories.name')->pluck('categories.name')->unique()->sort()->values();
        $items = (clone $baseQuery)->whereNotNull('items.name')->pluck('items.name')->unique()->sort()->values();
        $schools = (clone $baseQuery)->whereNotNull('asset_assignments.location')->where('asset_assignments.location', '!=', '')->pluck('asset_assignments.location')->unique()->sort()->values();
        $sources = (clone $baseQuery)->whereNotNull('acquisition_sources.name')->pluck('acquisition_sources.name')->unique()->sort()->values();
        $modes = (clone $baseQuery)->whereNotNull('pm.name')->pluck('pm.name')->unique()->sort()->values();

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
                $sheet->setCellValue('E' . $currentRow, $row->location);
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

    private function buildBuildingsQuery(Request $request)
    {
        $filters = $request->input('filters', []);
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?: [];
        }

        $query = DB::table('building_records')
            ->leftJoin('schools', 'building_records.school_id', '=', 'schools.id')
            ->leftJoin('building_specs', 'building_records.building_spec_id', '=', 'building_specs.id')
            ->leftJoin('building_types', 'building_specs.building_type_id', '=', 'building_types.id')
            ->leftJoin('building_classifications', 'building_types.building_classification_id', '=', 'building_classifications.id')
            ->select(
                'building_records.*',
                'schools.school_id as school_identifier',
                'schools.name as office_name',
                'building_specs.storeys',
                'building_specs.classrooms',
                'building_specs.description',
                'building_types.name as article',
                'building_classifications.name as classification'
            );

        if (!empty($filters['classification'])) {
            $classifications = is_array($filters['classification']) ? $filters['classification'] : [$filters['classification']];
            $query->whereIn('building_classifications.name', $classifications);
        }
        if (!empty($filters['officeType'])) {
            $types = is_array($filters['officeType']) ? $filters['officeType'] : [$filters['officeType']];
            $query->whereIn('building_records.office_type', $types);
        }
        if (!empty($filters['schoolName'])) {
            $query->where(function($q) use ($filters) {
                $q->where('schools.name', 'LIKE', '%' . $filters['schoolName'] . '%')
                  ->orWhere('building_records.property_number', 'LIKE', '%' . $filters['schoolName'] . '%');
            });
        }
        if (!empty($filters['location'])) {
            $locs = is_array($filters['location']) ? $filters['location'] : [$filters['location']];
            $query->whereIn('building_records.location', $locs);
        }
        if (!empty($filters['dateAcquired'])) {
            $query->whereDate('building_records.acquisition_date', $filters['dateAcquired']);
        }

        // Data Integrity: Empty Column check
        if (!empty($filters['emptyCol'])) {
            $eCol = $filters['emptyCol'];
            $dbCol = null;
            
            // Map frontend key to DB column
            $mapping = [
                'region' => 'region',
                'division' => 'division',
                'office_type' => 'office_type',
                'school_identifier' => 'school_identifier',
                'office_name' => 'office_name',
                'address' => 'address',
                'storeys' => 'building_specs.storeys',
                'classrooms' => 'building_specs.classrooms',
                'article' => 'building_types.name',
                'description' => 'building_specs.description',
                'classification' => 'building_classifications.name',
                'occupancy_nature' => 'occupancy_nature',
                'location' => 'location',
                'date_constructed' => 'date_constructed',
                'acquisition_date' => 'acquisition_date',
                'property_number' => 'property_number',
                'acquisition_cost' => 'acquisition_cost',
                'estimated_useful_life' => 'estimated_useful_life',
                'appraised_value' => 'appraised_value',
                'appraisal_date' => 'appraisal_date',
            ];

            $dbCol = $mapping[$eCol] ?? null;
            
            if ($dbCol) {
                $query->where(function($q) use ($dbCol) {
                    $q->whereNull($dbCol)
                      ->orWhere($dbCol, '')
                      ->orWhere($dbCol, '0')
                      ->orWhere($dbCol, '0.00')
                      ->orWhere($dbCol, 'unclassified')
                      ->orWhere($dbCol, 'uncategorized')
                      ->orWhere($dbCol, 'Unclassified')
                      ->orWhere($dbCol, 'Uncategorized');
                });
            }
        }

        // Sorting by Cost
        $sortCost = $filters['sortCost'] ?? null;
        if ($sortCost === 'low_to_high') {
            $query->orderBy('acquisition_cost', 'asc');
        } elseif ($sortCost === 'high_to_low') {
            $query->orderBy('acquisition_cost', 'desc');
        } else {
            $query->orderBy('id', 'asc');
        }

        return $query;
    }

    public function getBuildingsPreview(Request $request)
    {
        $query = $this->buildBuildingsQuery($request);
        $rows = $query->limit(500)->get();
        return response()->json(['rows' => $rows]);
    }

    public function getBuildingsFilterOptions(Request $request)
    {
        $baseQuery = DB::table('building_records')
            ->leftJoin('schools', 'building_records.school_id', '=', 'schools.id')
            ->leftJoin('building_specs', 'building_records.building_spec_id', '=', 'building_specs.id')
            ->leftJoin('building_types', 'building_specs.building_type_id', '=', 'building_types.id')
            ->leftJoin('building_classifications', 'building_types.building_classification_id', '=', 'building_classifications.id');

        $classifications = (clone $baseQuery)->whereNotNull('building_classifications.name')->where('building_classifications.name', '!=', '')->pluck('building_classifications.name')->unique()->sort()->values();
        $office_types    = (clone $baseQuery)->whereNotNull('building_records.office_type')->where('building_records.office_type', '!=', '')->pluck('building_records.office_type')->unique()->sort()->values();
        $schools         = (clone $baseQuery)->whereNotNull('schools.name')->where('schools.name', '!=', '')->pluck('schools.name')->unique()->sort()->values();
        $articles        = (clone $baseQuery)->whereNotNull('building_types.name')->where('building_types.name', '!=', '')->pluck('building_types.name')->unique()->sort()->values();
        $occupancies     = (clone $baseQuery)->whereNotNull('building_records.occupancy_nature')->where('building_records.occupancy_nature', '!=', '')->pluck('building_records.occupancy_nature')->unique()->sort()->values();
        $locations       = (clone $baseQuery)->whereNotNull('building_records.location')->where('building_records.location', '!=', '')->pluck('building_records.location')->unique()->sort()->values();

        return response()->json([
            'classifications' => $classifications,
            'office_types'    => $office_types,
            'schools'         => $schools,
            'articles'        => $articles,
            'occupancies'     => $occupancies,
            'locations'       => $locations,
            // legacy keys kept for backward compat
            'officeTypes'     => $office_types,
        ]);
    }


    public function getSchoolsPreview(Request $request)
    {
        $filters = $request->input('filters', []);
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?: [];
        }

        $query = DB::table('schools')
            ->leftJoin('districts', 'schools.district_id', '=', 'districts.id')
            ->leftJoin('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
            ->select(
                'schools.*',
                'districts.name as district_name',
                'quadrants.name as quadrant_name'
            )
            ->addSelect([
                'total_bldg_cost' => DB::table('building_records')
                    ->whereColumn('school_id', 'schools.id')
                    ->selectRaw('COALESCE(SUM(acquisition_cost), 0)'),
                'total_ppe_cost' => DB::table('asset_assignments')
                    ->where(function($q) {
                        $q->whereColumn('location', 'schools.name')
                          ->orWhereColumn('school_id', 'schools.school_id');
                    })
                    ->where('acquisition_cost', '>=', 50000)
                    ->selectRaw('COALESCE(SUM(acquisition_cost), 0)'),
                'total_semi_ppe_cost' => DB::table('asset_assignments')
                    ->where(function($q) {
                        $q->whereColumn('location', 'schools.name')
                          ->orWhereColumn('school_id', 'schools.school_id');
                    })
                    ->where('acquisition_cost', '<', 50000)
                    ->selectRaw('COALESCE(SUM(acquisition_cost), 0)'),
            ]);

        if (!empty($filters['quadrant'])) {
            $quads = is_array($filters['quadrant']) ? $filters['quadrant'] : [$filters['quadrant']];
            $query->whereIn('quadrants.name', $quads);
        }
        if (!empty($filters['district'])) {
            $districts = is_array($filters['district']) ? $filters['district'] : [$filters['district']];
            $query->whereIn('districts.name', $districts);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('schools.school_id', 'LIKE', "%$search%")
                  ->orWhere('schools.name', 'LIKE', "%$search%")
                  ->orWhereRaw("CONCAT_WS(' - ', schools.school_id, schools.name) LIKE ?", ["%$search%"]);
            });
        }

        // Sorting
        $sort = $filters['sort'] ?? null;
        if ($sort === 'name_asc') {
            $query->orderBy('schools.name', 'asc');
        } elseif ($sort === 'name_desc') {
            $query->orderBy('schools.name', 'desc');
        } elseif ($sort === 'id_asc') {
            $query->orderBy('schools.school_id', 'asc');
        } elseif ($sort === 'id_desc') {
            $query->orderBy('schools.school_id', 'desc');
        } elseif ($sort === 'cost_asc') {
            $query->orderByRaw('(total_bldg_cost + total_ppe_cost + total_semi_ppe_cost) asc');
        } elseif ($sort === 'cost_desc') {
            $query->orderByRaw('(total_bldg_cost + total_ppe_cost + total_semi_ppe_cost) desc');
        } elseif ($sort === 'bldg_asc') {
            $query->orderBy('total_bldg_cost', 'asc');
        } elseif ($sort === 'bldg_desc') {
            $query->orderBy('total_bldg_cost', 'desc');
        } elseif ($sort === 'ppe_asc') {
            $query->orderBy('total_ppe_cost', 'asc');
        } elseif ($sort === 'ppe_desc') {
            $query->orderBy('total_ppe_cost', 'desc');
        } elseif ($sort === 'semi_asc') {
            $query->orderBy('total_semi_ppe_cost', 'asc');
        } elseif ($sort === 'semi_desc') {
            $query->orderBy('total_semi_ppe_cost', 'desc');
        } else {
            $query->orderBy('schools.name', 'asc');
        }

        $rows = $query->get();
        return response()->json(['rows' => $rows]);
    }

    public function getSchoolsFilterOptions(Request $request)
    {
        $quadrant = $request->input('quadrant');
        
        $districtQuery = DB::table('districts');
        if ($quadrant) {
            $districtQuery->join('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
                         ->where('quadrants.name', $quadrant);
        }
        
        $districts = $districtQuery->whereNotNull('districts.name')
                                  ->where('districts.name', '!=', '')
                                  ->pluck('districts.name')
                                  ->map(function($name) { return trim($name); })
                                  ->unique()
                                  ->sort()
                                  ->values();
        $quadrants = DB::table('quadrants')->pluck('name')->unique()->sort()->values();

        // For search autocomplete
        $allSchools = DB::table('schools')
            ->select('school_id', 'name')
            ->get()
            ->map(function($s) {
                return "{$s->school_id} - {$s->name}";
            });

        return response()->json([
            'districts' => $districts,
            'quadrants' => $quadrants,
            'allSchools' => $allSchools
        ]);
    }

    public function getOfficesPreview(Request $request)
    {
        $filters = $request->input('filters', []);
        if (is_string($filters)) { $filters = json_decode($filters, true) ?: []; }

        $query = DB::table('offices')
            ->leftJoin('schools', 'offices.school_id', '=', 'schools.id')
            ->select(
                'offices.id',
                'offices.name',
                'offices.office_code',
                'offices.room_number',
                'schools.name as school_name'
            )
            ->addSelect([
                'total_ppe_cost' => DB::table('asset_assignments as ad2')
                    ->join('asset_sources as asrc2', 'ad2.asset_source_id', '=', 'asrc2.id')
                    ->whereColumn('ad2.office_id', 'offices.id')
                    ->where('asrc2.asset_cost', '>=', 50000)
                    ->selectRaw('COALESCE(SUM(asrc2.asset_cost), 0)'),
                'total_semi_ppe_cost' => DB::table('asset_assignments as ad3')
                    ->join('asset_sources as asrc3', 'ad3.asset_source_id', '=', 'asrc3.id')
                    ->whereColumn('ad3.office_id', 'offices.id')
                    ->where('asrc3.asset_cost', '<', 50000)
                    ->selectRaw('COALESCE(SUM(asrc3.asset_cost), 0)'),
                'total_assets' => DB::table('asset_assignments as ad4')
                    ->whereColumn('ad4.office_id', 'offices.id')
                    ->selectRaw('COUNT(*)'),
            ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('offices.name', 'LIKE', "%$search%")
                  ->orWhere('offices.office_code', 'LIKE', "%$search%")
                  ->orWhere('schools.name', 'LIKE', "%$search%");
            });
        }

        $rows = $query->orderBy('offices.name')->get();
        return response()->json(['rows' => $rows]);
    }

    public function getOfficesFilterOptions(Request $request)
    {
        $schools = DB::table('schools')->pluck('name')->unique()->sort()->values();
        return response()->json([
            'schools' => $schools
        ]);
    }

    public function getCustodiansPreview(Request $request)
    {
        $filters = $request->input('filters', []);
        if (is_string($filters)) { $filters = json_decode($filters, true) ?: []; }

        $query = DB::table('custodians')
            ->select(
                'custodians.*'
            )
            ->addSelect([
                'total_assets' => DB::table('asset_assignments')
                    ->whereColumn('custodian_id', 'custodians.id')
                    ->selectRaw('COUNT(*)'),
                'total_value' => DB::table('asset_assignments as ad')
                    ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
                    ->whereColumn('ad.custodian_id', 'custodians.id')
                    ->selectRaw('COALESCE(SUM(asrc.asset_cost), 0)'),
            ]);

        if (!empty($filters['status'])) {
            $query->where('custodians.status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%$search%")
                  ->orWhere('last_name', 'LIKE', "%$search%")
                  ->orWhere('employee_id', 'LIKE', "%$search%")
                  ->orWhere('position', 'LIKE', "%$search%");
            });
        }

        $rows = $query->orderBy('last_name')->get();
        return response()->json(['rows' => $rows]);
    }

    public function getCustodiansFilterOptions(Request $request)
    {
        $positions = DB::table('custodians')->distinct()->pluck('position')->filter()->values();
        $statuses = DB::table('custodians')->distinct()->pluck('status')->filter()->values();

        return response()->json([
            'positions' => $positions,
            'statuses' => $statuses
        ]);
    }
}


