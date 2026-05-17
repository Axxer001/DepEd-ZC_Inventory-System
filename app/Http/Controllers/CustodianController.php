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

        // Schools and offices associated (via assignments)
        $schools = DB::table('asset_assignments as aa')
            ->leftJoin('schools as s', 'aa.school_id', '=', 's.id')
            ->where('aa.custodian_id', $id)
            ->whereNotNull('s.id')
            ->select('s.id', 's.name', DB::raw('COUNT(aa.id) as asset_count'))
            ->groupBy('s.id', 's.name')
            ->get();

        // All assets assigned to this custodian
        $assets = DB::table('asset_assignments as aa')
            ->join('asset_sources as asrc', 'aa.asset_source_id', '=', 'asrc.id')
            ->join('items as i', 'asrc.item_id', '=', 'i.id')
            ->join('categories as cat', 'i.category_id', '=', 'cat.id')
            ->leftJoin('offices as o', 'aa.office_id', '=', 'o.id')
            ->leftJoin('schools as s', 'aa.school_id', '=', 's.id')
            ->where('aa.custodian_id', $id)
            ->select(
                'aa.id',
                'aa.property_number',
                'aa.acquisition_date',
                'aa.acquisition_cost as asset_cost',
                'aa.condition',
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

        return view('admin.custodians.profile', compact('custodian', 'stats', 'schools', 'assets'));
    }
}
