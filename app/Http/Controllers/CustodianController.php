<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class CustodianController extends Controller
{
    /**
     * Display the profile page for a specific custodian.
     */
    public function profile($id)
    {
        $custodian = DB::table('custodians')->where('id', $id)->first();

        if (!$custodian) {
            abort(404, 'Custodian not found');
        }

        // Aggregated assignment stats
        $stats = DB::table('asset_assignments as aa')
            ->where('aa.custodian_id', $id)
            ->selectRaw('COUNT(aa.id) as total_assets, COALESCE(SUM(aa.acquisition_cost), 0) as total_value')
            ->first();

        // Schools and offices associated (via custodian's school_id)
        $schools = DB::table('custodians as c')
            ->leftJoin('schools as s', 'c.school_id', '=', 's.school_id')
            ->where('c.id', $id)
            ->whereNotNull('s.id')
            ->select('s.id', 's.name', DB::raw('(SELECT COUNT(*) FROM asset_assignments WHERE custodian_id = c.id) as asset_count'))
            ->get();

        // All assets assigned to this custodian
        $assets = DB::table('asset_assignments as aa')
            ->join('asset_sources as asrc', 'aa.asset_source_id', '=', 'asrc.id')
            ->join('items as i', 'asrc.item_id', '=', 'i.id')
            ->join('categories as cat', 'i.category_id', '=', 'cat.id')
            ->leftJoin('custodians as c', 'aa.custodian_id', '=', 'c.id')
            ->leftJoin('offices as o', 'c.office_id', '=', 'o.id')
            ->leftJoin('schools as s', 'c.school_id', '=', 's.school_id')
            ->where('aa.custodian_id', $id)
            ->select(
                'aa.id',
                'aa.property_number',
                'aa.acquisition_date',
                'aa.acquisition_cost as asset_cost',
                'aa.condition',
                'aa.created_at as assigned_at',
                'i.name as item_name',
                'cat.name as category_name',
                'asrc.brand',
                'asrc.model',
                'asrc.serial_number',
                'o.name as office_name',
                's.name as school_name'
            )
            ->orderByDesc('aa.acquisition_date')
            ->get();

        // Fetch transfer/return/unserviceable history for this custodian's assets
        $assignmentIds = $assets->pluck('id')->toArray();
        $transfers = collect();
        if (!empty($assignmentIds)) {
            $transfers = DB::table('asset_transfers as at')
                ->leftJoin('offices as fo', 'at.from_office_id', '=', 'fo.id')
                ->leftJoin('offices as to', 'at.to_office_id', '=', 'to.id')
                ->leftJoin('custodians as fc', 'at.from_custodian_id', '=', 'fc.id')
                ->leftJoin('custodians as tc', 'at.to_custodian_id', '=', 'tc.id')
                ->whereIn('at.asset_assignment_id', $assignmentIds)
                ->select(
                    'at.asset_assignment_id',
                    'at.transfer_type',
                    'at.transfer_date',
                    'at.remarks',
                    'at.authorized_by',
                    'fo.name as from_office',
                    'to.name as to_office',
                    DB::raw("CONCAT(fc.first_name, ' ', fc.last_name) as from_custodian"),
                    DB::raw("CONCAT(tc.first_name, ' ', tc.last_name) as to_custodian")
                )
                ->orderBy('at.transfer_date', 'desc')
                ->get()
                ->groupBy('asset_assignment_id');
        }

        return view('admin.custodians.profile', compact('custodian', 'stats', 'schools', 'assets', 'transfers'));
    }
}
