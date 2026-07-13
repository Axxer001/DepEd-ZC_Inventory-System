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

        if (auth()->check() && auth()->user()->isSchoolSystem() && $building->school_id !== auth()->user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $classifications = DB::table('building_classifications')->select('id', 'name')->orderBy('name')->get();
        $types = DB::table('building_types')->select('id', 'name')->orderBy('name')->get();

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

        return view('buildings.profile', compact('building', 'timeline', 'documents', 'classifications', 'types'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->check() || !auth()->user()->approved) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'classification' => 'required|string|max:255',
            'type_name' => 'required|string|max:255',
            'occupancy_nature' => 'nullable|string|max:255',
            'storeys' => 'required|integer|min:1',
            'classrooms' => 'required|integer|min:0',
            'property_number' => 'nullable|string|max:255',
            'date_constructed' => 'nullable|date',
            'acquisition_cost' => 'nullable|numeric|min:0',
            'estimated_useful_life' => 'nullable|integer|min:0',
            'appraised_value' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $building = DB::table('building_records')->where('id', $id)->first();
        if (!$building) {
            return back()->with('error', 'Building not found');
        }

        if (auth()->user()->isSchoolSystem() && $building->school_id !== auth()->user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () use ($id, $building, $validated, $request) {
            // 1. Resolve Classification
            $classInput = trim($validated['classification']);
            $classification = DB::table('building_classifications')
                ->whereRaw('LOWER(name) = ?', [strtolower($classInput)])
                ->first();
            $finalClassId = $classification ? $classification->id : DB::table('building_classifications')->insertGetId([
                'name' => strtoupper($classInput),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Resolve Type
            $typeInput = trim($validated['type_name']);
            $type = DB::table('building_types')
                ->whereRaw('LOWER(name) = ?', [strtolower($typeInput)])
                ->where('building_classification_id', $finalClassId)
                ->first();
            $finalTypeId = $type ? $type->id : DB::table('building_types')->insertGetId([
                'building_classification_id' => $finalClassId,
                'name' => strtoupper($typeInput),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Resolve Spec
            $storeys = intval($validated['storeys']);
            $classrooms = intval($validated['classrooms']);
            
            $spec = DB::table('building_specs')
                ->where('building_type_id', $finalTypeId)
                ->where('storeys', $storeys)
                ->where('classrooms', $classrooms)
                ->first();

            $finalSpecId = $spec ? $spec->id : DB::table('building_specs')->insertGetId([
                'building_type_id' => $finalTypeId,
                'storeys' => $storeys,
                'classrooms' => $classrooms,
                'description' => "{$storeys} STOREY - {$classrooms} CLASSROOM " . strtoupper($typeInput),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Update Building Record
            DB::table('building_records')->where('id', $id)->update([
                'building_spec_id' => $finalSpecId,
                'occupancy_nature' => $validated['occupancy_nature'],
                'property_number' => $validated['property_number'],
                'date_constructed' => $validated['date_constructed'],
                'acquisition_cost' => $validated['acquisition_cost'],
                'estimated_useful_life' => $validated['estimated_useful_life'],
                'appraised_value' => $validated['appraised_value'],
                'remarks' => $validated['remarks'],
                'updated_at' => now(),
            ]);

            /** @var \App\Models\User|null $user */
            $user = auth()->user();

            // Log the activity
            DB::table('system_logs')->insert([
                'user' => $user ? $user->name : 'System',
                'action_type' => 'UPDATE',
                'module' => 'Buildings',
                'activity' => 'Updated building record ID ' . $id . ' (Spec ID ' . $finalSpecId . ')',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Building specifications updated successfully!');
    }

    public function destroy($id)
    {
        $building = BuildingRecord::findOrFail($id);
        $user = auth()->user();

        // Access check
        if ($user->isSchoolSystem()) {
            // Must belong to their own school
            if ($building->school_id !== $user->school_id) {
                abort(403, 'Unauthorized action.');
            }
            // Must be self-registered
            if ($building->origin_system_type !== 'school' || $building->registered_by_school_id !== $user->school_id) {
                abort(403, 'Unauthorized action.');
            }
            // Limited deletion window: same-day only (created today)
            if (!$building->created_at->isToday()) {
                return back()->with('error', 'Same-day deletion window has expired for this building.');
            }
        }

        $building->delete();

        // Log the action to system_logs
        DB::table('system_logs')->insert([
            'user' => $user ? $user->name : 'System',
            'action_type' => 'Delete',
            'module' => 'Buildings',
            'activity' => "Building record ID {$building->id} was soft-deleted.",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('register.building')->with('success', 'Building successfully archived.');
    }
}
