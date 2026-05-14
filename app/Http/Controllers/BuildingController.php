<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BuildingRecord;

class BuildingController extends Controller
{
    public function profile($id)
    {
        $building = DB::table('building_records as br')
            ->join('schools', 'br.school_id', '=', 'schools.id')
            ->join('building_specs as bs', 'br.building_spec_id', '=', 'bs.id')
            ->join('building_types as bt', 'bs.building_type_id', '=', 'bt.id')
            ->join('building_classifications as bc', 'bt.building_classification_id', '=', 'bc.id')
            ->leftJoin('districts', 'schools.district_id', '=', 'districts.id')
            ->select(
                'br.*',
                'schools.name as school_name',
                'schools.school_id as school_identifier',
                'districts.name as district_name',
                'bs.description as spec_description',
                'bs.storeys',
                'bs.classrooms',
                'bt.name as type_name',
                'bc.name as classification_name'
            )
            ->where('br.id', $id)
            ->first();

        if (!$building) {
            abort(404, 'Building not found');
        }

        // Generate dummy timeline data
        $timeline = [
            [
                'date' => $building->date_constructed ?? 'N/A',
                'type' => 'Construction',
                'description' => 'Original construction completed.',
                'user' => 'System'
            ],
            [
                'date' => $building->acquisition_date ?? 'N/A',
                'type' => 'Recording',
                'description' => 'Building officially recorded in the inventory.',
                'user' => 'Admin'
            ]
        ];

        $documents = collect(); // empty for now

        return view('buildings.profile', compact('building', 'timeline', 'documents'));
    }
}
