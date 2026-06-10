<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\School;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('admin.custodians'); // Keep view name for now to avoid breaking too many things
    }

    public function profile($id)
    {
        $custodian = DB::table('employees')->where('id', $id)->first();

        if (!$custodian) {
            abort(404, 'Custodian not found');
        }

        $stats = DB::table('asset_assignments as ad')
            ->where('ad.employee_id', $id)
            ->selectRaw('COUNT(ad.id) as total_assets, COALESCE(SUM(ad.acquisition_cost), 0) as total_value')
            ->first();

        $assets = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items as i', 'asrc.item_id', '=', 'i.id')
            ->join('categories as cat', 'i.category_id', '=', 'cat.id')
            ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
            ->leftJoin('schools as s', 'e.school_id', '=', 's.id')
            ->leftJoin('offices as o', 'e.office_id', '=', 'o.id')
            ->where('ad.employee_id', $id)
            ->select(
                'ad.id',
                'ad.property_number',
                'ad.acquisition_date',
                'ad.acquisition_cost as asset_cost',
                'ad.created_at as assigned_at',
                'i.name as item_name',
                'cat.name as category_name',
                'asrc.condition',
                DB::raw('COALESCE(s.name, o.name) as school_name')
            )
            ->orderByDesc('ad.acquisition_date')
            ->get();

        $schools = DB::table('asset_assignments as ad')
            ->join('employees as e', 'ad.employee_id', '=', 'e.id')
            ->leftJoin('schools as s', 'e.school_id', '=', 's.id')
            ->leftJoin('offices as o', 'e.office_id', '=', 'o.id')
            ->where('ad.employee_id', $id)
            ->where(function ($query) {
                $query->whereNotNull('e.school_id')
                      ->orWhereNotNull('e.office_id');
            })
            ->select(
                DB::raw('COALESCE(s.name, o.name) as name'),
                DB::raw('COUNT(ad.id) as asset_count')
            )
            ->groupBy(DB::raw('COALESCE(s.name, o.name)'))
            ->get();

        $transfers = collect();
        if ($assets->isNotEmpty()) {
            $transfers = DB::table('asset_transfers as at')
                ->leftJoin('offices as to_off', 'at.to_office_id', '=', 'to_off.id')
                ->leftJoin('employees as to_emp', 'at.to_custodian_id', '=', 'to_emp.id')
                ->whereIn('at.asset_assignment_id', $assets->pluck('id'))
                ->select(
                    'at.*',
                    'to_off.name as to_office',
                    DB::raw("TRIM(CONCAT(COALESCE(to_emp.first_name, ''), ' ', COALESCE(to_emp.last_name, ''))) as to_custodian")
                )
                ->orderByDesc('at.transfer_date')
                ->get()
                ->groupBy('asset_assignment_id');
        }

        return view('admin.custodians.profile', compact('custodian', 'stats', 'schools', 'assets', 'transfers'));
    }

    /**
     * API: Search employees for selectors.
     */
    public function searchEmployees(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim();

        $query = Employee::query()->with(['office', 'school']);

        if (!empty($q)) {
            $query->where(function ($query) use ($q) {
                $query->whereRaw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) LIKE ?", ["%{$q}%"])
                      ->orWhere('employee_id', 'LIKE', "%{$q}%");
            });
        }

        $results = $query
            ->limit(500)
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
