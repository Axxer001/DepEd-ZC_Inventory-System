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
            ->leftJoin('schools as s', 'o.school_id', '=', 's.id')
            ->leftJoin('districts as d', 's.district_id', '=', 'd.id')
            ->leftJoin('quadrants as q', 'd.quadrant_id', '=', 'q.id')
            ->select(
                'o.*',
                's.name as school_name',
                's.school_id as school_identifier',
                'd.name as district_name',
                'q.name as quadrant_name'
            )
            ->where('o.id', $id)
            ->first();

        if (!$office) {
            abort(404, 'Office not found');
        }

        // Total buildings stats for the school this office belongs to
        $buildingStats = DB::table('building_records')
            ->where('school_id', $office->school_id)
            ->selectRaw('COUNT(*) as total_buildings, COALESCE(SUM(acquisition_cost), 0) as total_bldg_cost')
            ->first();

        // Building records for the school this office belongs to
        $buildings = DB::table('building_records as br')
            ->join('building_specs as bs', 'br.building_spec_id', '=', 'bs.id')
            ->join('building_types as bt', 'bs.building_type_id', '=', 'bt.id')
            ->select(
                'br.id',
                'br.property_number',
                'br.date_constructed',
                'br.acquisition_cost',
                'br.occupancy_nature',
                'bs.storeys',
                'bs.classrooms',
                'bs.description as spec_description',
                'bt.name as type_name'
            )
            ->where('br.school_id', $office->school_id)
            ->get();

        // Asset assignments for this office
        $assetStats = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->where('ad.office_id', $id)
            ->selectRaw('COUNT(ad.id) as total_assets, COALESCE(SUM(ad.acquisition_cost), 0) as total_asset_value')
            ->first();

        // Recent assets assigned to this office
        $recentAssets = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->where('ad.office_id', $id)
            ->select(
                'ad.id',
                'ad.property_number',
                'ad.acquisition_date',
                'ad.acquisition_cost as asset_cost',
                'items.name as item_name',
                'categories.name as category_name',
                'ad.condition'
            )
            ->orderByDesc('ad.acquisition_date')
            ->get();

        // Fetch custodians who have assets assigned in this office
        $custodians = DB::table('asset_assignments as ad')
            ->join('custodians as c', 'ad.custodian_id', '=', 'c.id')
            ->where('ad.office_id', $id)
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

        return view('admin.offices.profile', compact(
            'office', 'buildingStats', 'buildings', 'assetStats', 'recentAssets', 'custodians'
        ));
    }
}
