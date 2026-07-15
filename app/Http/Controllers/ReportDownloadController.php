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
                ->leftJoin('asset_assignments', 'asset_assignments.asset_source_id', '=', 'asset_sources.id')
                ->leftJoin('items', 'asset_sources.item_id', '=', 'items.id')
                ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
                ->leftJoin('classifications', 'categories.classification_id', '=', 'classifications.id')
                ->leftJoin('acquisition_sources', 'asset_sources.acquisition_source_id', '=', 'acquisition_sources.id')
                ->leftJoin('procurement_modes as pm', 'asset_sources.procurement_mode_id', '=', 'pm.id')
                ->select(
                    'asset_sources.*',
                    'asset_assignments.id as id',
                    'asset_sources.id as asset_source_id',
                    'asset_sources.condition as remarks',
                    'items.name as article',
                    'categories.name as category',
                    'classifications.name as classification',
                    'acquisition_sources.name as acq_source',
                    'pm.name as mode_of_acquisition',
                    'acquisition_sources.contact_person as source_personnel',
                    'acquisition_sources.contact_position as personnel_position',
                    DB::raw('(asset_sources.asset_cost * asset_sources.quantity) as acquisition_cost'),
                    DB::raw("'Region IX' as region"),
                    DB::raw("'Division of Zamboanga City' as division"),
                    DB::raw("NULL as school_type"),
                    DB::raw("NULL as school_id"),
                    DB::raw("NULL as office_school_name"),
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
                ->leftJoin('employees as cus', 'asset_assignments.employee_id', '=', 'cus.id')
                ->leftJoin('offices as cus_off', 'cus.office_id', '=', 'cus_off.id')
                ->leftJoin('schools as cus_sch', 'cus.school_id', '=', 'cus_sch.id')
                ->leftJoin('offices as direct_off', 'asset_assignments.office_id', '=', 'direct_off.id')
                ->leftJoin('schools as direct_sch', 'asset_assignments.school_id', '=', 'direct_sch.id')
                ->select(
                    'asset_assignments.*',
                    DB::raw("'Region IX' as region"),
                    DB::raw("'Division of Zamboanga City' as division"),
                    DB::raw('COALESCE(direct_sch.school_id, direct_off.office_id, cus_sch.school_id, cus_off.office_id) as school_id'),
                    DB::raw('COALESCE(direct_sch.type, direct_off.type, cus_sch.type, cus_off.type) as school_type'),
                    DB::raw('COALESCE(direct_sch.name, direct_off.name, cus_sch.name, cus_off.name) as office_school_name'),
                    DB::raw('COALESCE(direct_sch.location, direct_off.location, cus_sch.location, cus_off.location) as location'),
                    DB::raw("NULL as nature_of_occupancy"),
                    'asset_sources.description',
                    'asset_sources.unit_of_measurement',
                    'asset_sources.asset_cost',
                    'asset_sources.quantity',
                    'asset_sources.quantity as source_qty',
                    'asset_sources.estimated_useful_life',
                    'pm.name as mode_of_acquisition',
                    'acquisition_sources.contact_person as source_personnel',
                    'acquisition_sources.contact_position as personnel_position',
                    'asset_sources.acceptance_date',
                    'asset_sources.condition as remarks',
                    'items.name as article',
                    'categories.name as category',
                    'classifications.name as classification',
                    'acquisition_sources.name as acq_source',
                    'cus.employee_id as custodian_employee_id',
                    DB::raw("CONCAT(cus.first_name, ' ', COALESCE(cus.middle_name, ''), ' ', cus.last_name) as custodian_name"),
                    'cus.position as custodian_position',
                    'cus.status as custodian_status'
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
            $query->where(function($q) use ($filters) {
                $q->where('cus_sch.name', 'LIKE', '%' . $filters['schoolName'] . '%')
                  ->orWhere('direct_sch.name', 'LIKE', '%' . $filters['schoolName'] . '%');
            });
        }
        if (!empty($filters['district']) && $tab === 'distribution') {
            $query->where(function($q) use ($filters) {
                $q->whereIn('cus_sch.district_id', function($sub) use ($filters) {
                    $sub->select('id')->from('districts')->where('name', $filters['district']);
                })->orWhereIn('direct_sch.district_id', function($sub) use ($filters) {
                    $sub->select('id')->from('districts')->where('name', $filters['district']);
                });
            });
        }
        if (!empty($filters['quadrant']) && $tab === 'distribution') {
            $query->where(function($q) use ($filters) {
                $q->whereIn('cus_sch.district_id', function($sub) use ($filters) {
                    $sub->select('id')->from('districts')->whereIn('quadrant_id', function($q2) use ($filters) {
                        $q2->select('id')->from('quadrants')->where('name', $filters['quadrant']);
                    });
                })->orWhereIn('direct_sch.district_id', function($sub) use ($filters) {
                    $sub->select('id')->from('districts')->whereIn('quadrant_id', function($q2) use ($filters) {
                        $q2->select('id')->from('quadrants')->where('name', $filters['quadrant']);
                    });
                });
            });
        }
        if (!empty($filters['officeName']) && $tab === 'distribution') {
            $query->where(function($q) use ($filters) {
                $q->where('cus_off.name', 'LIKE', '%' . $filters['officeName'] . '%')
                  ->orWhere('direct_off.name', 'LIKE', '%' . $filters['officeName'] . '%');
            });
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

        if (!empty($filters['status'])) {
            $status = $filters['status'];
            if ($status === 'distributed') {
                if ($tab === 'source') {
                    $query->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                          ->from('asset_assignments')
                          ->whereColumn('asset_assignments.asset_source_id', 'asset_sources.id')
                          ->where(function($sq) {
                              $sq->whereNotNull('asset_assignments.employee_id')
                                 ->orWhereNotNull('asset_assignments.school_id')
                                 ->orWhereNotNull('asset_assignments.office_id');
                          });
                    });
                } else {
                    $query->where(function($q) {
                        $q->whereNotNull('asset_assignments.employee_id')
                          ->orWhereNotNull('asset_assignments.school_id')
                          ->orWhereNotNull('asset_assignments.office_id');
                    });
                }
            } elseif ($status === 'not_distributed') {
                if ($tab === 'source') {
                    $query->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                          ->from('asset_assignments')
                          ->whereColumn('asset_assignments.asset_source_id', 'asset_sources.id')
                          ->where(function($sq) {
                              $sq->whereNotNull('asset_assignments.employee_id')
                                 ->orWhereNotNull('asset_assignments.school_id')
                                 ->orWhereNotNull('asset_assignments.office_id');
                          });
                    });
                } else {
                    $query->whereNull('asset_assignments.employee_id')
                          ->whereNull('asset_assignments.school_id')
                          ->whereNull('asset_assignments.office_id');
                }
            } else {
                $conditionMap = [
                    'serviceable' => 'Good Condition',
                    'to_repair' => 'Needs Repair',
                    'unserviceable' => 'Unserviceable',
                ];
                if (isset($conditionMap[$status])) {
                    $query->where('asset_sources.condition', $conditionMap[$status]);
                }
            }
        }

        if (!empty($filters['expiry'])) {
            $expiry = $filters['expiry'];
            if ($expiry === 'expired') {
                $query->whereRaw('DATE_ADD(asset_sources.acceptance_date, INTERVAL asset_sources.estimated_useful_life YEAR) <= CURDATE()');
            } elseif ($expiry === 'nearing_expiry') {
                $query->whereRaw('DATE_ADD(asset_sources.acceptance_date, INTERVAL asset_sources.estimated_useful_life YEAR) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 MONTH)');
            } elseif ($expiry === 'active') {
                $query->whereRaw('DATE_ADD(asset_sources.acceptance_date, INTERVAL asset_sources.estimated_useful_life YEAR) > DATE_ADD(CURDATE(), INTERVAL 6 MONTH)');
            }
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
            elseif ($eCol === 'useful_life') $dbCol = 'asset_sources.estimated_useful_life';
            elseif ($eCol === 'personnel') $dbCol = 'acquisition_sources.contact_person';
            elseif ($eCol === 'condition') $dbCol = 'asset_sources.condition';
            
            // Distribution-specific columns
            if ($tab === 'distribution') {
                if ($eCol === 'property_number') $dbCol = 'asset_assignments.property_number';
                elseif ($eCol === 'serial_number') $dbCol = 'asset_assignments.serial_number';
                elseif ($eCol === 'school_id') $dbCol = 'cus.school_id';
                elseif ($eCol === 'school_name') $dbCol = 'schools.name';
                elseif ($eCol === 'location') $dbCol = 'schools.location';
                elseif ($eCol === 'acquisition_date') $dbCol = 'asset_assignments.acquisition_date';
                elseif ($eCol === 'employee_id') $dbCol = 'asset_assignments.employee_id';
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

        // --- School-scope enforcement ---
        // School-system users may only see assets that belong to their own school.
        // Distribution tab: filter by school_id on asset_assignments or via employee school.
        // Source tab: filter asset_sources to those with at least one assignment in the school.
        $user = auth()->user();
        if ($user && $user->isSchoolSystem()) {
            $schoolId = $user->school_id;
            if ($tab === 'distribution') {
                $query->where(function ($q) use ($schoolId) {
                    $q->where('asset_assignments.school_id', $schoolId)
                      ->orWhereExists(function ($sub) use ($schoolId) {
                          $sub->select(DB::raw(1))
                              ->from('employees')
                              ->whereColumn('employees.id', 'asset_assignments.employee_id')
                              ->where('employees.school_id', $schoolId);
                      });
                });
            } else {
                // source tab: scope to asset_sources assigned to this school
                $query->whereExists(function ($sub) use ($schoolId) {
                    $sub->select(DB::raw(1))
                        ->from('asset_assignments')
                        ->whereColumn('asset_assignments.asset_source_id', 'asset_sources.id')
                        ->where(function ($q2) use ($schoolId) {
                            $q2->where('asset_assignments.school_id', $schoolId)
                               ->orWhereExists(function ($sub2) use ($schoolId) {
                                   $sub2->select(DB::raw(1))
                                        ->from('employees')
                                        ->whereColumn('employees.id', 'asset_assignments.employee_id')
                                        ->where('employees.school_id', $schoolId);
                               });
                        });
                });
            }
        }

        return $query;
    }

    public function getPreview(Request $request)
    {
        $query = $this->buildQuery($request);
        $rows = $query->limit(500)->get();
        return response()->json(['rows' => $rows])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
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
        return response()->json(['rows' => $rows])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
    }

    public function getFilterOptions(Request $request)
    {
        $type = $request->input('report_type');

        $baseQuery = DB::table('asset_sources')
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

        // Get schools that actually have assigned assets of this type
        $schoolsQuery = DB::table('asset_assignments')
            ->join('asset_sources', 'asset_assignments.asset_source_id', '=', 'asset_sources.id')
            ->join('employees as e', 'asset_assignments.employee_id', '=', 'e.id')
            ->join('schools as s', 'e.school_id', '=', 's.id');

        if ($type === 'RPCPPE') {
            $schoolsQuery->where('asset_sources.asset_cost', '>=', 50000);
        } elseif ($type === 'RPCSP') {
            $schoolsQuery->where('asset_sources.asset_cost', '<', 50000);
        }
        $schools = $schoolsQuery->whereNotNull('s.name')->where('s.name', '!=', '')->pluck('s.name')->unique()->sort()->values();

        // Get offices that actually have assigned assets of this type
        $officesQuery = DB::table('asset_assignments')
            ->join('asset_sources', 'asset_assignments.asset_source_id', '=', 'asset_sources.id')
            ->join('employees as e', 'asset_assignments.employee_id', '=', 'e.id')
            ->join('offices as o', 'e.office_id', '=', 'o.id');

        if ($type === 'RPCPPE') {
            $officesQuery->where('asset_sources.asset_cost', '>=', 50000);
        } elseif ($type === 'RPCSP') {
            $officesQuery->where('asset_sources.asset_cost', '<', 50000);
        }
        $offices = $officesQuery->whereNotNull('o.name')->where('o.name', '!=', '')->pluck('o.name')->unique()->sort()->values();

        $quadrants = DB::table('quadrants')->whereNotNull('name')->where('name', '!=', '')->pluck('name')->unique()->sort()->values();
        $districts = DB::table('districts')->whereNotNull('name')->where('name', '!=', '')->pluck('name')->unique()->sort()->values();
        $sources = (clone $baseQuery)->whereNotNull('acquisition_sources.name')->pluck('acquisition_sources.name')->unique()->sort()->values();
        $modes = (clone $baseQuery)->whereNotNull('pm.name')->pluck('pm.name')->unique()->sort()->values();

        $units = DB::table('asset_sources')
            ->whereNotNull('unit_of_measurement')
            ->where('unit_of_measurement', '!=', '')
            ->distinct()
            ->orderBy('unit_of_measurement')
            ->pluck('unit_of_measurement');

        return response()->json([
            'classifications' => $classifications,
            'categories' => $categories,
            'items' => $items,
            'schools' => $schools,
            'offices' => $offices,
            'quadrants' => $quadrants,
            'districts' => $districts,
            'sources' => $sources,
            'modes' => $modes,
            'units' => $units
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
                $sheet->setCellValue('C' . $currentRow, $row->school_type);
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
                    ->leftJoin('employees', 'asset_assignments.employee_id', '=', 'employees.id')
                    ->where(function($q) {
                        $q->whereColumn('employees.school_id', 'schools.id')
                          ->orWhereColumn('asset_assignments.school_id', 'schools.id');
                    })
                    ->where('acquisition_cost', '>=', 50000)
                    ->selectRaw('COALESCE(SUM(acquisition_cost), 0)'),
                'total_semi_ppe_cost' => DB::table('asset_assignments')
                    ->leftJoin('employees', 'asset_assignments.employee_id', '=', 'employees.id')
                    ->where(function($q) {
                        $q->whereColumn('employees.school_id', 'schools.id')
                          ->orWhereColumn('asset_assignments.school_id', 'schools.id');
                    })
                    ->where('acquisition_cost', '<', 50000)
                    ->selectRaw('COALESCE(SUM(acquisition_cost), 0)'),
            ]);

        if (!empty($filters['legislative_district'])) {
            $ld = $filters['legislative_district'];
            $query->join('legislative_districts', 'quadrants.legislative_district_id', '=', 'legislative_districts.id')
                  ->where('legislative_districts.name', $ld);
        }
        if (!empty($filters['quadrant'])) {
            $quads = is_array($filters['quadrant']) ? $filters['quadrant'] : [$filters['quadrant']];
            $query->whereIn('quadrants.name', $quads);
        }
        if (!empty($filters['district'])) {
            $districts = is_array($filters['district']) ? $filters['district'] : [$filters['district']];
            $query->whereIn('districts.name', $districts);
        }
        if (!empty($filters['type'])) {
            $query->where('schools.type', $filters['type']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('schools.school_id', 'LIKE', "%$search%")
                  ->orWhere('schools.name', 'LIKE', "%$search%")
                  ->orWhereRaw("CONCAT_WS(' - ', schools.school_id, schools.name) LIKE ?", ["%$search%"]);
            });
        }

        // Costing sort (combined PPE + Semi-PPE + Building)
        $costing = $filters['costing'] ?? null;
        if ($costing === 'high_low') {
            $query->orderByRaw('(total_bldg_cost + total_ppe_cost + total_semi_ppe_cost) DESC');
        } elseif ($costing === 'low_high') {
            $query->orderByRaw('(total_bldg_cost + total_ppe_cost + total_semi_ppe_cost) ASC');
        }

        // Name sort
        $sort = $filters['sort'] ?? 'az';
        match ($sort) {
            'za'    => $query->orderBy('schools.name', 'desc'),
            default => $query->orderBy('schools.name', 'asc'),
        };

        $rows = $query->get();
        return response()->json(['rows' => $rows]);
    }

    public function getSchoolsFilterOptions(Request $request)
    {
        $legislativeDistricts = DB::table('legislative_districts')
            ->pluck('name')->filter()->sort()->values();

        $quadrants = DB::table('quadrants')
            ->pluck('name')->unique()->sort()->values();

        $districts = DB::table('districts')
            ->whereNotNull('name')->where('name', '!=', '')
            ->pluck('name')
            ->map(fn($n) => trim($n))
            ->unique()->sort()->values();

        $types = DB::table('schools')
            ->distinct()->whereNotNull('type')->where('type', '!=', '')
            ->pluck('type')->sort()->values();

        // For search autocomplete
        $allSchools = DB::table('schools')
            ->select('school_id', 'name')
            ->get()
            ->map(fn($s) => "{$s->school_id} - {$s->name}");

        return response()->json([
            'legislative_districts' => $legislativeDistricts,
            'quadrants'             => $quadrants,
            'districts'             => $districts,
            'types'                 => $types,
            'allSchools'            => $allSchools,
        ]);
    }

    public function getOfficesPreview(Request $request)
    {
        $filters = $request->input('filters', []);
        if (is_string($filters)) { $filters = json_decode($filters, true) ?: []; }

        $query = DB::table('offices')
            ->select(
                'offices.id',
                'offices.name',
                'offices.type',
                'offices.location',
                'offices.office_id'
            )
            ->addSelect([
                'total_ppe_cost' => DB::table('asset_assignments as ad2')
                    ->join('asset_sources as asrc2', 'ad2.asset_source_id', '=', 'asrc2.id')
                    ->join('employees as c2', 'ad2.employee_id', '=', 'c2.id')
                    ->whereColumn('c2.office_id', 'offices.id')
                    ->where('asrc2.asset_cost', '>=', 50000)
                    ->selectRaw('COALESCE(SUM(asrc2.asset_cost), 0)'),
                'total_semi_ppe_cost' => DB::table('asset_assignments as ad3')
                    ->join('asset_sources as asrc3', 'ad3.asset_source_id', '=', 'asrc3.id')
                    ->join('employees as c3', 'ad3.employee_id', '=', 'c3.id')
                    ->whereColumn('c3.office_id', 'offices.id')
                    ->where('asrc3.asset_cost', '<', 50000)
                    ->selectRaw('COALESCE(SUM(asrc3.asset_cost), 0)'),
                'total_assets' => DB::table('asset_assignments as ad4')
                    ->join('employees as c4', 'ad4.employee_id', '=', 'c4.id')
                    ->whereColumn('c4.office_id', 'offices.id')
                    ->selectRaw('COUNT(*)'),
            ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('offices.name', 'LIKE', "%$search%")
                  ->orWhere('offices.type', 'LIKE', "%$search%")
                  ->orWhere('offices.location', 'LIKE', "%$search%");
            });
        }

        // Costing sort (combined PPE + Semi-PPE)
        $costing = $filters['costing'] ?? null;
        if ($costing === 'high_low') {
            $query->orderByRaw('(total_ppe_cost + total_semi_ppe_cost) DESC');
        } elseif ($costing === 'low_high') {
            $query->orderByRaw('(total_ppe_cost + total_semi_ppe_cost) ASC');
        }

        // Name sort
        $sort = $filters['sort'] ?? 'az';
        match ($sort) {
            'za'    => $query->orderBy('offices.name', 'desc'),
            default => $query->orderBy('offices.name', 'asc'),
        };

        $rows = $query->get();
        return response()->json(['rows' => $rows]);
    }

    public function getOfficesFilterOptions(Request $request)
    {
        $types = DB::table('offices')->distinct()->whereNotNull('type')->where('type', '!=', '')->pluck('type')->sort()->values();
        return response()->json([
            'types' => $types
        ]);
    }

    public function getCustodiansPreview(Request $request)
    {
        $filters = $request->input('filters', []);
        if (is_string($filters)) { $filters = json_decode($filters, true) ?: []; }

        $query = DB::table('employees')
            ->leftJoin('schools as s', 'employees.school_id', '=', 's.id')
            ->leftJoin('offices as o', 'employees.office_id', '=', 'o.id')
            ->select(
                'employees.*',
                DB::raw('COALESCE(s.name, o.name) as school_name')
            );

        $user = auth()->user();
        if ($user && $user->isSchoolSystem()) {
            $query->where('employees.school_id', $user->school_id);
        }

        $query->addSelect([
                'total_assets' => DB::table('asset_assignments')
                    ->whereColumn('employee_id', 'employees.id')
                    ->selectRaw('COUNT(*)'),
                'total_value' => DB::table('asset_assignments as ad')
                    ->whereColumn('ad.employee_id', 'employees.id')
                    ->selectRaw('COALESCE(SUM(ad.acquisition_cost), 0)'),
            ]);

        if (!empty($filters['status'])) {
            $query->where('employees.status', $filters['status']);
        }
        if (!empty($filters['position'])) {
            $query->where('employees.position', $filters['position']);
        }
        if (!empty($filters['clearance'])) {
            if ($filters['clearance'] === 'cleared') {
                $query->having('total_assets', '=', 0);
            } elseif ($filters['clearance'] === 'has_assets') {
                $query->having('total_assets', '>', 0);
            }
        }
        if (!empty($filters['portfolio_value'])) {
            match ($filters['portfolio_value']) {
                'no_value'  => $query->having('total_value', '=', 0),
                'low'       => $query->having('total_value', '>', 0)->having('total_value', '<=', 50000),
                'mid'       => $query->having('total_value', '>', 50000)->having('total_value', '<=', 200000),
                'high'      => $query->having('total_value', '>', 200000),
                default     => null,
            };
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('employees.first_name', 'LIKE', "%$search%")
                  ->orWhere('employees.last_name', 'LIKE', "%$search%")
                  ->orWhere('employees.employee_id', 'LIKE', "%$search%")
                  ->orWhere('employees.position', 'LIKE', "%$search%")
                  ->orWhere('s.name', 'LIKE', "%$search%")
                  ->orWhere('o.name', 'LIKE', "%$search%");
            });
        }

        // Costing sort (portfolio value) takes priority; name sort is secondary
        $costing = $filters['costing'] ?? null;
        $sort    = $filters['sort']    ?? 'az';

        if ($costing === 'high_low') {
            $query->orderByDesc('total_value');
        } elseif ($costing === 'low_high') {
            $query->orderBy('total_value');
        }

        match ($sort) {
            'za'    => $query->orderBy('employees.last_name', 'desc'),
            default => $query->orderBy('employees.last_name'),
        };

        $rows = $query->get();
        return response()->json(['rows' => $rows]);
    }

    public function getCustodiansFilterOptions(Request $request)
    {
        $positions = DB::table('employees')->distinct()->pluck('position')->filter()->sort()->values();
        $statuses  = DB::table('employees')->distinct()->pluck('status')->filter()->values();

        return response()->json([
            'positions'       => $positions,
            'statuses'        => $statuses,
            'clearance'       => [
                ['value' => 'cleared',    'label' => 'Cleared (No Assets)'],
                ['value' => 'has_assets', 'label' => 'Has Assigned Assets'],
            ],
            'portfolio_value' => [
                ['value' => 'no_value', 'label' => 'No Portfolio Value'],
                ['value' => 'low',      'label' => '₱1 – ₱50,000'],
                ['value' => 'mid',      'label' => '₱50,001 – ₱200,000'],
                ['value' => 'high',     'label' => 'Above ₱200,000'],
            ],
            'sort'    => [
                ['value' => 'az', 'label' => 'A → Z'],
                ['value' => 'za', 'label' => 'Z → A'],
            ],
            'costing' => [
                ['value' => 'high_low', 'label' => 'High to Low'],
                ['value' => 'low_high', 'label' => 'Low to High'],
            ],
        ]);
    }

    public function downloadDocTemplate(Request $request, $type)
    {
        $recipient = $request->input('recipient', 'Document');
        
        $allowed = ['ITR', 'PTR', 'ICS', 'PAR', 'RRSP', 'RRPPE'];
        if (!in_array($type, $allowed)) {
            abort(404, 'Invalid template type');
        }

        $filePath = base_path('../' . $type . '.xlsx');
        if (!file_exists($filePath)) {
            abort(404, 'Template file not found.');
        }

        // Clean recipient name for filename, preserving spaces and alphanumeric chars
        $safeRecipient = preg_replace('/[\\\\\/:\*\?"<>\|]/', '', $recipient);
        if (empty($safeRecipient)) {
            $safeRecipient = 'Recipient';
        }

        $downloadName = $type . $safeRecipient . '.xlsx';

        $assignmentId = $request->query('assignment_id');
        $transferId = $request->query('transfer_id');

        if ($assignmentId || $transferId) {
            $transfer = null;
            if ($transferId) {
                $transfer = DB::table('asset_transfers')->where('id', $transferId)->first();
                if ($transfer) {
                    $assignmentId = $transfer->asset_assignment_id;
                }
            }

            $assignment = null;
            if ($assignmentId) {
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
            }

            if (!$assignment) {
                abort(404, 'Asset assignment or transfer not found.');
            }

            // Determine End User/Custodian Name
            $empId = null;
            if ($type === 'RRPPE' || $type === 'RRSP') {
                // Return documents
                if (!$transfer) {
                    // Try to find latest Return transfer for this assignment
                    $transfer = DB::table('asset_transfers')
                        ->where('asset_assignment_id', $assignment->id)
                        ->where('transfer_type', 'Return')
                        ->orderBy('transfer_date', 'desc')
                        ->first();
                }
                if ($transfer && $transfer->from_custodian_id) {
                    $empId = $transfer->from_custodian_id;
                } elseif ($assignment->employee_id) {
                    $empId = $assignment->employee_id;
                }
            } else {
                // Non-return documents
                if ($transfer && $transfer->to_custodian_id) {
                    $empId = $transfer->to_custodian_id;
                } elseif ($assignment->employee_id) {
                    $empId = $assignment->employee_id;
                }
            }

            $custodianName = '';
            if ($empId) {
                $employee = DB::table('employees')->where('id', $empId)->first();
                if ($employee) {
                    $parts = [];
                    if (!empty($employee->first_name)) {
                        $parts[] = trim($employee->first_name);
                    }
                    if (!empty($employee->middle_name)) {
                        $parts[] = trim($employee->middle_name);
                    }
                    if (!empty($employee->last_name)) {
                        $parts[] = trim($employee->last_name);
                    }
                    $custodianName = implode(' ', $parts);
                }
            }

            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();

            if ($type === 'ICS') {
                // D6:G6 merged — entity name (anchor D6)
                $sheet->setCellValue('D6', 'DepEd, Division of Zamboanga City');
                // Data row 12 — first fully writable row beneath merged header rows 9-11
                $sheet->setCellValue('B12', $assignment->quantity ?? '');
                $sheet->setCellValue('C12', $assignment->unit_of_measurement ?? '');
                $sheet->setCellValue('D12', $assignment->asset_cost ?? '');
                $totalCost = ($assignment->asset_cost && $assignment->quantity)
                    ? ($assignment->asset_cost * $assignment->quantity) : '';
                $sheet->setCellValue('E12', $totalCost);
                $sheet->setCellValue('F12', $assignment->description ?? '');
                $sheet->setCellValue('H12', $assignment->property_number ?? '');
                $sheet->setCellValue('I12', $assignment->estimated_useful_life ?? '');

            } elseif ($type === 'ITR') {
                // E8:H8 merged — anchor E8 (From accountable officer)
                $sheet->setCellValue('E8', 'DEPED, SDO ZAMBOANGA CITY');
                // E9:H9 merged — anchor E9 (To accountable officer / custodian name)
                $sheet->setCellValue('E9', $custodianName);
                // J9 — date (not merged)
                $sheet->setCellValue('J9', date('F d, Y'));
                // Data row 18 — first fully writable row beneath merged header rows 16-17
                $sheet->setCellValue('A18', $assignment->acquisition_date ?? '');
                $sheet->setCellValue('B18', $assignment->property_number ?? '');
                $sheet->setCellValue('D18', $assignment->property_number . ($assignment->acquisition_date ? ' / ' . $assignment->acquisition_date : ''));
                $sheet->setCellValue('E18', $assignment->description ?? '');
                $sheet->setCellValue('I18', $assignment->acquisition_cost ?? '');
                $sheet->setCellValue('J18', $assignment->condition ?? '');

            } elseif ($type === 'PAR') {
                // D11 — entity name (not merged at row 11)
                $sheet->setCellValue('D11', 'DEPED, ZAMBOANGA CITY DIVISION');
                // Data row 16 — first fully writable row beneath merged header rows 14-15
                $sheet->setCellValue('B16', $assignment->quantity ?? '');
                $sheet->setCellValue('C16', $assignment->unit_of_measurement ?? '');
                $sheet->setCellValue('D16', $assignment->description ?? '');
                $sheet->setCellValue('E16', $assignment->property_number ?? '');
                $sheet->setCellValue('F16', $assignment->acquisition_date ?? '');
                $sheet->setCellValue('G16', $assignment->acquisition_cost ?? '');

            } elseif ($type === 'PTR') {
                // H9 — date (not merged)
                $sheet->setCellValue('H9', date('F d, Y'));
                // Data row 18 — first fully writable row beneath merged header rows 16-17
                $sheet->setCellValue('A18', $assignment->acquisition_date ?? '');
                $sheet->setCellValue('B18', $assignment->property_number ?? '');
                $sheet->setCellValue('D18', $assignment->description ?? '');
                $sheet->setCellValue('H18', $assignment->acquisition_cost ?? '');
                $sheet->setCellValue('I18', $assignment->condition ?? '');


            } elseif ($type === 'RRPPE' || $type === 'RRSP') {
                $sheet->setCellValue('H4', date('F d, Y'));
                $sheet->setCellValue('H35', date('F d, Y'));
                
                // Copy 1 (Row 8)
                $sheet->setCellValue('B8', 1);
                $sheet->setCellValue('C8', $assignment->item_name ?? '');
                $sheet->setCellValue('D8', $assignment->description ?? '');
                $sheet->setCellValue('E8', $assignment->quantity ?? '');
                $sheet->setCellValue('F8', $assignment->property_number ?? '');
                $sheet->setCellValue('G8', $custodianName);
                $sheet->setCellValue('H8', $transfer->remarks ?? '');
                $sheet->setCellValue('D18', $custodianName);

                // Copy 2 (Row 39)
                $sheet->setCellValue('B39', 1);
                $sheet->setCellValue('C39', $assignment->item_name ?? '');
                $sheet->setCellValue('D39', $assignment->description ?? '');
                $sheet->setCellValue('E39', $assignment->quantity ?? '');
                $sheet->setCellValue('F39', $assignment->property_number ?? '');
                $sheet->setCellValue('G39', $custodianName);
                $sheet->setCellValue('H39', $transfer->remarks ?? '');
                $sheet->setCellValue('D51', $custodianName);
            }

            return response()->stream(function() use ($spreadsheet) {
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');
            }, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
                'Cache-Control' => 'max-age=0',
            ]);
        }

        return response()->download($filePath, $downloadName);
    }
}


