<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SchoolController extends Controller
{
    public function profile($id)
    {
        $school = DB::table('schools as s')
            ->leftJoin('districts as d', 's.district_id', '=', 'd.id')
            ->leftJoin('quadrants as q', 'd.quadrant_id', '=', 'q.id')
            ->select(
                's.*',
                'd.name as district_name',
                'q.name as quadrant_name'
            )
            ->where('s.id', $id)
            ->first();

        if (!$school) {
            abort(404, 'School not found');
        }

        // Total buildings count and cost
        $buildingStats = DB::table('building_records')
            ->where('school_id', $id)
            ->selectRaw('COUNT(*) as total_buildings, COALESCE(SUM(acquisition_cost), 0) as total_bldg_cost, COALESCE(SUM(appraised_value), 0) as total_appraised')
            ->first();

        // Building records for this school
        $buildings = DB::table('building_records as br')
            ->join('building_specs as bs', 'br.building_spec_id', '=', 'bs.id')
            ->join('building_types as bt', 'bs.building_type_id', '=', 'bt.id')
            ->join('building_classifications as bc', 'bt.building_classification_id', '=', 'bc.id')
            ->select(
                'br.id',
                'br.property_number',
                'br.date_constructed',
                'br.acquisition_cost',
                'br.occupancy_nature',
                'br.appraised_value',
                'br.estimated_useful_life',
                'bs.storeys',
                'bs.classrooms',
                'bs.description as spec_description',
                'bt.name as type_name',
                'bc.name as classification_name'
            )
            ->where('br.school_id', $id)
            ->get();

        // Asset assignments for this school (match via employees FK or direct school_id)
        $assetStats = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
            ->where(function ($query) use ($id) {
                $query->where('e.school_id', $id)
                      ->orWhere('ad.school_id', $id);
            })
            ->selectRaw('COUNT(ad.id) as total_assets, COALESCE(SUM(asrc.asset_cost), 0) as total_asset_value')
            ->first();

        // Recent assets assigned to this school (up to 10)
        $recentAssets = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
            ->where(function ($query) use ($id) {
                $query->where('e.school_id', $id)
                      ->orWhere('ad.school_id', $id);
            })
            ->select(
                'ad.id',
                'ad.property_number',
                'ad.acquisition_date',
                'asrc.asset_cost',
                'asrc.condition',
                'items.name as item_name',
                'categories.name as category_name',
                DB::raw("CONCAT(e.first_name, ' ', e.last_name) as custodian_name")
            )
            ->orderByDesc('ad.acquisition_date')
            ->paginate(50, ['*'], 'assets_page');

        // Fetch all asset assignment IDs that have ever been assigned to this school
        $allAssignedIds = DB::table('asset_transfers')
            ->where('to_school_id', $id)
            ->orWhere('from_school_id', $id)
            ->pluck('asset_assignment_id')
            ->merge(
                DB::table('asset_assignments')
                    ->where('school_id', $id)
                    ->pluck('id')
            )
            ->unique()
            ->values();

        $historicalAssets = collect();
        if ($allAssignedIds->isNotEmpty()) {
            $historicalAssets = DB::table('asset_assignments as ad')
                ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
                ->join('items as i', 'asrc.item_id', '=', 'i.id')
                ->join('categories as cat', 'i.category_id', '=', 'cat.id')
                ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
                ->whereIn('ad.id', $allAssignedIds)
                ->select(
                    'ad.id',
                    'ad.employee_id',
                    'ad.school_id',
                    'ad.property_number',
                    'ad.serial_number',
                    'ad.acquisition_date',
                    'asrc.asset_cost',
                    'i.name as item_name',
                    'cat.name as category_name',
                    'asrc.condition',
                    DB::raw("CONCAT(e.first_name, ' ', e.last_name) as custodian_name")
                )
                ->get();
        }

        $transfers = collect();
        if ($historicalAssets->isNotEmpty()) {
            $transfers = DB::table('asset_transfers as at')
                ->leftJoin('schools as to_sch', 'at.to_school_id', '=', 'to_sch.id')
                ->leftJoin('offices as to_off', 'at.to_office_id', '=', 'to_off.id')
                ->leftJoin('employees as to_emp', 'at.to_custodian_id', '=', 'to_emp.id')
                ->whereIn('at.asset_assignment_id', $historicalAssets->pluck('id'))
                ->select(
                    'at.*',
                    'to_sch.name as to_school',
                    'to_off.name as to_office',
                    DB::raw("TRIM(CONCAT(COALESCE(to_emp.first_name, ''), ' ', COALESCE(to_emp.last_name, ''))) as to_custodian")
                )
                ->orderBy('at.transfer_date', 'asc')
                ->orderBy('at.created_at', 'asc')
                ->orderBy('at.id', 'asc')
                ->get()
                ->groupBy('asset_assignment_id');
        }

        // Build asset events: receives + transfers
        $assetEvents = collect();
        foreach ($historicalAssets as $asset) {
            $assetTransfers = $transfers->get($asset->id, collect());
            
            // If there are no transfers at all, and it is currently assigned to this school
            if ($assetTransfers->isEmpty() && $asset->school_id == $id) {
                $assetEvents->push((object)[
                    'type'          => 'received',
                    'event_date'    => $asset->acquisition_date,
                    'item_name'     => $asset->item_name,
                    'category_name' => $asset->category_name,
                    'property_number' => $asset->property_number,
                    'serial_number'   => $asset->serial_number,
                    'asset_cost'    => $asset->asset_cost,
                    'to_custodian'  => $asset->custodian_name ?: null,
                    'to_school'     => null,
                    'to_office'     => null,
                ]);
                continue;
            }

            $hasAnyEvent = false;
            foreach ($assetTransfers as $t) {
                // If they received the asset
                if ($t->to_school_id == $id) {
                    $assetEvents->push((object)[
                        'type'          => 'received',
                        'event_date'    => $t->transfer_date ?? $t->created_at,
                        'item_name'     => $asset->item_name,
                        'category_name' => $asset->category_name,
                        'property_number' => $asset->property_number,
                        'serial_number'   => $asset->serial_number,
                        'asset_cost'    => $asset->asset_cost,
                        'to_custodian'  => $t->to_custodian ?? null,
                        'to_school'     => null,
                        'to_office'     => null,
                    ]);
                    $hasAnyEvent = true;
                }
                // If they transferred/returned the asset away
                elseif ($t->from_school_id == $id) {
                    $assetEvents->push((object)[
                        'type'          => 'transferred',
                        'event_date'    => $t->transfer_date ?? $t->created_at,
                        'item_name'     => $asset->item_name,
                        'category_name' => $asset->category_name,
                        'property_number' => $asset->property_number,
                        'serial_number'   => $asset->serial_number,
                        'asset_cost'    => $asset->asset_cost,
                        'to_custodian'  => $t->to_custodian ?? null,
                        'to_school'     => $t->to_school ?? null,
                        'to_office'     => $t->to_office ?? null,
                    ]);
                    $hasAnyEvent = true;
                }
            }

            // Fallback
            if (!$hasAnyEvent && $asset->school_id == $id) {
                $assetEvents->push((object)[
                    'type'          => 'received',
                    'event_date'    => $asset->acquisition_date,
                    'item_name'     => $asset->item_name,
                    'category_name' => $asset->category_name,
                    'property_number' => $asset->property_number,
                    'serial_number'   => $asset->serial_number,
                    'asset_cost'    => $asset->asset_cost,
                    'to_custodian'  => $asset->custodian_name ?: null,
                    'to_school'     => null,
                    'to_office'     => null,
                ]);
            }
        }
        $assetEvents = $assetEvents->sortBy('event_date')->values();

        return view('schools.profile', compact(
            'school', 'buildingStats', 'buildings', 'assetStats', 'recentAssets', 'assetEvents'
        ));
    }
}
