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

        // Asset assignments for this school (match via offices FK)
        $assetStats = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('offices', 'ad.office_id', '=', 'offices.id')
            ->where('offices.school_id', $id)
            ->selectRaw('COUNT(ad.id) as total_assets, COALESCE(SUM(asrc.asset_cost), 0) as total_asset_value')
            ->first();

        // Recent assets assigned to this school (up to 10)
        $recentAssets = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('offices', 'ad.office_id', '=', 'offices.id')
            ->where('offices.school_id', $id)
            ->select(
                'ad.id',
                'ad.property_number',
                'ad.acquisition_date',
                'asrc.asset_cost',
                'items.name as item_name',
                'categories.name as category_name'
            )
            ->orderByDesc('ad.acquisition_date')
            ->limit(10)
            ->get();

        return view('schools.profile', compact(
            'school', 'buildingStats', 'buildings', 'assetStats', 'recentAssets'
        ));
    }
}
