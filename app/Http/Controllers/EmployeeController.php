<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\School;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('admin.custodians'); // Keep view name for now to avoid breaking too many things
    }

    public function profile($id)
    {
        $employee = Employee::with(['school', 'office', 'assetAssignments.assetSource.item'])->findOrFail($id);
        return view('admin.custodian-profile', compact('employee'));
    }

    /**
     * API: Search employees for selectors.
     */
    public function searchEmployees(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim();

        if (empty($q)) {
            return response()->json([]);
        }

        $results = Employee::query()
            ->with(['office', 'school'])
            ->where(function ($query) use ($q) {
                $query->whereRaw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) LIKE ?", ["%{$q}%"])
                      ->orWhere('employee_id', 'LIKE', "%{$q}%");
            })
            ->limit(15)
            ->get()
            ->map(fn($e) => [
                'id'                 => $e->id,
                'full_name'          => $e->full_name,
                'employee_id'        => $e->employee_id,
                'position'           => $e->position,
                'status'             => $e->status,
                'location_type'      => $e->school_id ? 'school' : 'office',
                'location_id'        => $e->school?->school_id ?? $e->office?->office_id,
                'location_name'      => $e->school?->name ?? $e->office?->name,
                'location_type_label'=> $e->school?->type ?? $e->office?->type,
                'location'           => $e->school?->location ?? $e->office?->location,
            ]);

        return response()->json($results);
    }

    /**
     * API: Search locations (Schools/Offices) for selectors.
     */
    public function searchLocations(Request $request): JsonResponse
    {
        $q    = $request->string('q')->trim();
        $type = $request->string('type', 'all');

        if (empty($q)) {
            return response()->json([]);
        }

        $schools = collect();
        $offices = collect();

        if (in_array($type, ['school', 'all'])) {
            $schools = School::where('name', 'LIKE', "%{$q}%")
                ->orWhere('school_id', 'LIKE', "%{$q}%")
                ->limit(10)->get()
                ->map(fn($s) => [
                    'id'          => $s->id,
                    'entity_type' => 'school',
                    'entity_id'   => $s->school_id,
                    'name'        => $s->name,
                    'type'        => $s->type,
                    'location'    => $s->location,
                ]);
        }

        if (in_array($type, ['office', 'all'])) {
            $offices = Office::where('name', 'LIKE', "%{$q}%")
                ->orWhere('office_id', 'LIKE', "%{$q}%")
                ->limit(10)->get()
                ->map(fn($o) => [
                    'id'          => $o->id,
                    'entity_type' => 'office',
                    'entity_id'   => $o->office_id,
                    'name'        => $o->name,
                    'type'        => $o->type,
                    'location'    => $o->location,
                ]);
        }

        return response()->json($schools->merge($offices)->values());
    }
}
