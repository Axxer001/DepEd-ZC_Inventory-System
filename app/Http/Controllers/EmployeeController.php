<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\School;
use App\Models\Office;
use App\Models\Classification;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('admin.custodians'); // Keep view name for now to avoid breaking too many things
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'sex' => 'nullable|string|in:Male,Female',
            'employee_id' => 'required|string|max:255|unique:employees,employee_id',
            'position' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'status' => 'required|string',
            'school_id' => 'nullable|exists:schools,id',
            'office_id' => 'nullable|exists:offices,id',
        ]);

        $employee = Employee::create([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'sex' => $validated['sex'] ?? null,
            'employee_id' => $validated['employee_id'],
            'position' => $validated['position'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'status' => $validated['status'],
            'school_id' => $validated['school_id'],
            'office_id' => $validated['office_id'],
        ]);

        \App\Models\EmployeeHistory::create([
            'employee_id' => $employee->id,
            'action' => 'Created',
            'description' => 'Employee record created.',
        ]);

        return redirect()->route('admin.employee-management')->with('success', 'Employee registered successfully.');
    }

    public function managementIndex()
    {
        return view('admin.employee-management');
    }

    public function managementProfile($id)
    {
        $custodian = DB::table('employees')->where('id', $id)->first();

        if (!$custodian) {
            abort(404, 'Employee not found');
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
                'ad.acquisition_date as assigned_at',
                'i.name as item_name',
                'cat.name as category_name',
                'asrc.condition',
                DB::raw('COALESCE(s.name, o.name) as school_name')
            )
            ->orderByDesc('ad.acquisition_date')
            ->get();

        $schools = DB::table('employees as e')
            ->leftJoin('schools as s', 'e.school_id', '=', 's.id')
            ->leftJoin('offices as o', 'e.office_id', '=', 'o.id')
            ->where('e.id', $id)
            ->where(function ($query) {
                $query->whereNotNull('e.school_id')
                      ->orWhereNotNull('e.office_id');
            })
            ->select(
                DB::raw('COALESCE(s.name, o.name) as name'),
                DB::raw('(SELECT COUNT(*) FROM asset_assignments WHERE employee_id = e.id) as asset_count')
            )
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

        $histories = \App\Models\EmployeeHistory::where('employee_id', $id)->orderByDesc('created_at')->get();

        // Build asset events: receives (assigned_at) + transfers (transfer_date)
        $assetEvents = collect();
        foreach ($assets as $asset) {
            $assetEvents->push((object)[
                'type'          => 'received',
                'event_date'    => $asset->assigned_at,
                'item_name'     => $asset->item_name,
                'category_name' => $asset->category_name,
                'property_number' => $asset->property_number,
                'asset_cost'    => $asset->asset_cost,
                'to_custodian'  => null,
                'to_office'     => null,
            ]);
            if (isset($transfers[$asset->id])) {
                foreach ($transfers[$asset->id] as $t) {
                    $assetEvents->push((object)[
                        'type'          => 'transferred',
                        'event_date'    => $t->transfer_date ?? $t->created_at,
                        'item_name'     => $asset->item_name,
                        'category_name' => $asset->category_name,
                        'property_number' => $asset->property_number,
                        'asset_cost'    => $asset->asset_cost,
                        'to_custodian'  => $t->to_custodian ?? null,
                        'to_office'     => $t->to_office ?? null,
                    ]);
                }
            }
        }
        $assetEvents = $assetEvents->sortByDesc('event_date')->values();

        return view('admin.employee-management-profile', compact('custodian', 'stats', 'schools', 'assets', 'transfers', 'histories', 'assetEvents'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'sex' => 'nullable|string|in:Male,Female',
            'employee_id' => 'required|string|max:255|unique:employees,employee_id,'.$id,
            'position' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'status' => 'required|string',
            'school_id' => 'nullable|exists:schools,id',
            'office_id' => 'nullable|exists:offices,id',
        ]);

        $changes = [];
        if ($employee->first_name !== $validated['first_name'] || $employee->last_name !== $validated['last_name']) {
            $changes[] = 'name changed';
        }
        if ($employee->sex !== ($validated['sex'] ?? null)) {
            $changes[] = "sex changed to '" . ($validated['sex'] ?? 'none') . "'";
        }
        if ($employee->position !== $validated['position']) {
            $changes[] = "position changed from '{$employee->position}' to '{$validated['position']}'";
        }
        if ($employee->date_of_birth !== ($validated['date_of_birth'] ?? null)) {
            $changes[] = "date of birth changed to '" . ($validated['date_of_birth'] ?? 'none') . "'";
        }
        if ($employee->school_id != $validated['school_id'] || $employee->office_id != $validated['office_id']) {
            $changes[] = 'station reassigned';
        }
        if ($employee->status !== $validated['status']) {
            $changes[] = "status changed to {$validated['status']}";
        }

        if ($request->has('return_assets') && $request->input('return_assets') == '1') {
            \App\Models\AssetAssignment::where('employee_id', $employee->id)->update(['employee_id' => null]);
            $changes[] = 'all assigned assets returned to inventory';
        }

        $employee->update([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'sex' => $validated['sex'] ?? null,
            'employee_id' => $validated['employee_id'],
            'position' => $validated['position'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'status' => $validated['status'],
            'school_id' => $validated['school_id'],
            'office_id' => $validated['office_id'],
        ]);

        if (count($changes) > 0) {
            \App\Models\EmployeeHistory::create([
                'employee_id' => $employee->id,
                'action' => 'Updated',
                'description' => ucfirst(implode(', ', $changes)) . '.',
            ]);
        }

        return redirect()->route('admin.employee-management.profile', $id)->with('success', 'Employee updated successfully.');
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
                'ad.acquisition_date as assigned_at',
                'i.name as item_name',
                'cat.name as category_name',
                'asrc.condition',
                DB::raw('COALESCE(s.name, o.name) as school_name')
            )
            ->orderByDesc('ad.acquisition_date')
            ->get();

        // Fetch assigned station directly from the employee record (not through asset_assignments)
        // so employees with 0 assets also show their school/office.
        $schools = DB::table('employees as e')
            ->leftJoin('schools as s', 'e.school_id', '=', 's.id')
            ->leftJoin('offices as o', 'e.office_id', '=', 'o.id')
            ->where('e.id', $id)
            ->where(function ($query) {
                $query->whereNotNull('e.school_id')
                      ->orWhereNotNull('e.office_id');
            })
            ->select(
                DB::raw('COALESCE(s.name, o.name) as name'),
                DB::raw('(SELECT COUNT(*) FROM asset_assignments WHERE employee_id = e.id) as asset_count')
            )
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

        $histories = \App\Models\EmployeeHistory::where('employee_id', $id)->orderByDesc('created_at')->get();

        // Build asset events: receives (assigned_at) + transfers (transfer_date)
        $assetEvents = collect();
        foreach ($assets as $asset) {
            $assetEvents->push((object)[
                'type'          => 'received',
                'event_date'    => $asset->assigned_at,
                'item_name'     => $asset->item_name,
                'category_name' => $asset->category_name,
                'property_number' => $asset->property_number,
                'asset_cost'    => $asset->asset_cost,
                'to_custodian'  => null,
                'to_office'     => null,
            ]);
            if (isset($transfers[$asset->id])) {
                foreach ($transfers[$asset->id] as $t) {
                    $assetEvents->push((object)[
                        'type'          => 'transferred',
                        'event_date'    => $t->transfer_date ?? $t->created_at,
                        'item_name'     => $asset->item_name,
                        'category_name' => $asset->category_name,
                        'property_number' => $asset->property_number,
                        'asset_cost'    => $asset->asset_cost,
                        'to_custodian'  => $t->to_custodian ?? null,
                        'to_office'     => $t->to_office ?? null,
                    ]);
                }
            }
        }
        $assetEvents = $assetEvents->sortByDesc('event_date')->values();

        return view('admin.custodians.profile', compact('custodian', 'stats', 'schools', 'assets', 'transfers', 'histories', 'assetEvents'));
    }

    /**
     * API: Search employees for selectors.
     */
    public function searchEmployees(Request $request): JsonResponse
    {
        $q = (string)$request->string('q')->trim();

        $query = Employee::query()->with(['office', 'school']);

        if ($q !== '') {
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
                'sex'                => $e->sex,
                'date_of_birth'      => $e->date_of_birth,
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
        $q    = (string)$request->string('q')->trim();
        $type = (string)$request->string('type', 'all');

        $schools = collect();
        $offices = collect();

        if ($q === '') {
            if (in_array($type, ['school', 'all'])) {
                $schools = School::orderBy('name')->get()
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
                $offices = Office::orderBy('name')->get()
                    ->map(fn($o) => [
                        'id'          => $o->id,
                        'entity_type' => 'office',
                        'entity_id'   => $o->office_id ?? $o->id,
                        'name'        => $o->name,
                        'type'        => $o->type,
                        'location'    => $o->location,
                    ]);
            }
            return response()->json($schools->merge($offices)->values());
        }

        if (in_array($type, ['school', 'all'])) {
            $schools = School::where('name', 'LIKE', "%{$q}%")
                ->orWhere('school_id', 'LIKE', "%{$q}%")
                ->limit(200)->get()
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
                ->limit(200)->get()
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

    /**
     * API: Search classifications.
     */
    public function searchClassifications(Request $request): JsonResponse
    {
        $q = (string)$request->string('q')->trim();
        
        $query = Classification::query();
        if ($q !== '') {
            $query->where('name', 'LIKE', "%{$q}%");
        }
        
        $results = $query->orderBy('name')->get()->map(fn($c) => [
            'id'   => $c->id,
            'name' => $c->name,
        ]);
        
        return response()->json($results);
    }

    /**
     * API: Search categories.
     */
    public function searchCategories(Request $request): JsonResponse
    {
        $q = (string)$request->string('q')->trim();
        $classId = $request->input('classification_id');
        $className = $request->input('classification_name');

        $query = Category::query()->with('classification');

        if ($classId) {
            $query->where('classification_id', $classId);
        } elseif ($className) {
            $query->whereHas('classification', function($query) use ($className) {
                $query->where('name', $className);
            });
        }

        if ($q !== '') {
            $query->where('name', 'LIKE', "%{$q}%");
        }

        $results = $query->orderBy('name')->get()->map(fn($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'classification_id' => $cat->classification_id,
            'classification_name' => $cat->classification?->name,
        ]);

        return response()->json($results);
    }

    /**
     * API: Search acquisition sources for selectors.
     */
    public function searchAcquisitionSources(Request $request): JsonResponse
    {
        $q = (string)$request->string('q')->trim();

        $query = \App\Models\AcquisitionSource::query();

        if ($q !== '') {
            $query->where('name', 'LIKE', "%{$q}%");
        }

        $results = $query
            ->orderBy('name')
            ->limit(500)
            ->get()
            ->map(fn($src) => [
                'id'               => $src->id,
                'name'             => $src->name,
                'source_type'      => $src->source_type,
                'contact_person'   => $src->contact_person,
                'contact_position' => $src->contact_position,
            ]);

        return response()->json($results);
    }

    public function uploadPhoto(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if it exists
            if ($employee->photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($employee->photo_path);
            }

            $path = $request->file('photo')->store('employee-photos', 'public');
            $employee->photo_path = $path;
            $employee->save();

            return redirect()->back()->with('success', 'Profile photo updated successfully!');
        }

        return redirect()->back()->with('error', 'Failed to upload photo.');
    }
}
