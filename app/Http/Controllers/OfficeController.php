<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfficeController extends Controller
{
    public function profile($id)
    {
        // Fetch the office details with its school, district, and quadrant
        $office = DB::table('offices as o')
            ->leftJoin('districts as d', function($j) {
                // offices are now standalone — no direct school_id FK
                $j->whereRaw('1=0'); // placeholder; district resolved via employees
            })
            ->select(
                'o.id', 'o.name', 'o.office_id', 'o.type', 'o.location'
            )
            ->where('o.id', $id)
            ->first();

        if (!$office) {
            abort(404, 'Office not found');
        }

        // Offices are standalone units — buildings belong to schools, not offices.
        $buildingStats = (object)['total_buildings' => 0, 'total_bldg_cost' => 0];
        $buildings     = collect();

        // Asset assignments for employees based in this office or direct office_id
        $assetStats = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
            ->where(function ($query) use ($id) {
                $query->where('e.office_id', $id)
                      ->orWhere('ad.office_id', $id);
            })
            ->selectRaw('COUNT(ad.id) as total_assets, COALESCE(SUM(ad.acquisition_cost), 0) as total_asset_value')
            ->first();

        // Recent assets for employees based in this office or direct office_id
        $recentAssets = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
            ->where(function ($query) use ($id) {
                $query->where('e.office_id', $id)
                      ->orWhere('ad.office_id', $id);
            })
            ->select(
                'ad.id',
                'ad.property_number',
                'ad.serial_number',
                'ad.acquisition_date',
                'ad.acquisition_cost as asset_cost',
                'items.name as item_name',
                'categories.name as category_name',
                'asrc.condition'
            )
            ->orderByDesc('ad.acquisition_date')
            ->paginate(50, ['*'], 'assets_page');

        // Employees based in this office with their asset counts
        $custodians = DB::table('employees as c')
            ->leftJoin('asset_assignments as ad', 'ad.employee_id', '=', 'c.id')
            ->where('c.office_id', $id)
            ->select(
                'c.id',
                'c.first_name',
                'c.last_name',
                'c.employee_id',
                'c.position',
                DB::raw('COUNT(ad.id) as total_assigned_assets')
            )
            ->groupBy('c.id', 'c.first_name', 'c.last_name', 'c.employee_id', 'c.position')
            ->get();

        // Fetch all asset assignment IDs that have ever been assigned to this office
        $allAssignedIds = DB::table('asset_transfers')
            ->where('to_office_id', $id)
            ->orWhere('from_office_id', $id)
            ->pluck('asset_assignment_id')
            ->merge(
                DB::table('asset_assignments')
                    ->where('office_id', $id)
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
                    'ad.office_id',
                    'ad.property_number',
                    'ad.serial_number',
                    'ad.acquisition_date',
                    'ad.acquisition_cost as asset_cost',
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
            
            // If there are no transfers at all, and it is currently assigned to this office
            if ($assetTransfers->isEmpty() && $asset->office_id == $id) {
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
                if ($t->to_office_id == $id) {
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
                elseif ($t->from_office_id == $id) {
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
            if (!$hasAnyEvent && $asset->office_id == $id) {
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

        return view('admin.offices.profile', compact(
            'office', 'buildingStats', 'buildings', 'assetStats', 'recentAssets', 'custodians', 'assetEvents'
        ));
    }
}
