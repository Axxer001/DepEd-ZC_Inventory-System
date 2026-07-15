<?php

namespace App\Http\Controllers;

use App\Models\AcquisitionSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcquisitionSourceController extends Controller
{
    public function managementIndex()
    {
        return view('admin.source-management');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:acquisition_sources,name',
            'source_type' => 'required|in:Internal,External',
            'contact_person' => 'nullable|string|max:255',
            'contact_position' => 'nullable|string|max:255',
        ]);

        AcquisitionSource::create($validated);

        return redirect()->route('admin.sources')->with('success', 'Source created successfully.');
    }

    public function managementProfile($id)
    {
        $source = AcquisitionSource::findOrFail($id);

        $stats = DB::table('asset_sources as asrc')
            ->where('asrc.acquisition_source_id', $id)
            ->selectRaw('COUNT(asrc.id) as total_assets, COALESCE(SUM(asrc.asset_cost * asrc.quantity), 0) as total_value')
            ->first();

        $assets = DB::table('asset_sources as asrc')
            ->join('asset_assignments as ad', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items as i', 'asrc.item_id', '=', 'i.id')
            ->join('categories as cat', 'i.category_id', '=', 'cat.id')
            ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
            ->leftJoin('schools as s', 'e.school_id', '=', 's.id')
            ->leftJoin('offices as o', 'e.office_id', '=', 'o.id')
            ->where('asrc.acquisition_source_id', $id)
            ->select(
                'ad.id',
                'ad.property_number',
                'ad.serial_number',
                'ad.acquisition_date',
                'asrc.asset_cost',
                'asrc.acceptance_date',
                'asrc.created_at as registered_at',
                'i.name as item_name',
                'cat.name as category_name',
                'asrc.condition',
                'asrc.quantity',
                DB::raw('COALESCE(s.name, o.name) as location_name'),
                DB::raw("CONCAT(COALESCE(e.first_name,''), ' ', COALESCE(e.last_name,'')) as custodian_name")
            )
            ->orderByDesc('asrc.acceptance_date')
            ->paginate(50, ['*'], 'assets_page');

        $history = DB::table('asset_sources as asrc')
            ->join('items as i', 'asrc.item_id', '=', 'i.id')
            ->join('acquisition_sources as aqs', 'asrc.acquisition_source_id', '=', 'aqs.id')
            ->leftJoin('procurement_modes as pm', 'asrc.procurement_mode_id', '=', 'pm.id')
            ->leftJoin('asset_assignments as ad', 'ad.asset_source_id', '=', 'asrc.id')
            ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
            ->leftJoin('schools as s', 'ad.school_id', '=', 's.id')
            ->leftJoin('offices as o', 'ad.office_id', '=', 'o.id')
            ->where('asrc.acquisition_source_id', $id)
            ->select(
                'asrc.id',
                'i.name as item_name',
                'aqs.name as source_name',
                DB::raw('COALESCE(asrc.contact_person, aqs.contact_person) as source_personnel'),
                'pm.name as mode_of_procurement',
                'ad.property_number',
                'ad.serial_number',
                'ad.acquisition_date',
                'asrc.quantity',
                'asrc.asset_cost',
                'asrc.acceptance_date',
                'asrc.created_at',
                DB::raw("CONCAT(COALESCE(e.first_name,''), ' ', COALESCE(e.last_name,'')) as employee_name"),
                's.name as school_name',
                'o.name as office_name'
            )
            ->orderByDesc('asrc.created_at')
            ->paginate(50, ['*'], 'history_page');

        return view('admin.source-management-profile', compact('source', 'stats', 'assets', 'history'));
    }

    public function update(Request $request, $id)
    {
        $source = AcquisitionSource::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:acquisition_sources,name,' . $id,
            'source_type' => 'required|in:Internal,External',
            'contact_person' => 'nullable|string|max:255',
            'contact_position' => 'nullable|string|max:255',
        ]);

        $source->update($validated);

        return redirect()->back()->with('success', 'Source updated successfully.');
    }

    public function apiSearch(Request $request)
    {
        $query = AcquisitionSource::query();

        $user = auth()->user();
        if ($user && $user->isSchoolSystem()) {
            $schoolId = $user->school_id;
            $query->whereExists(function ($sub) use ($schoolId) {
                $sub->select(DB::raw(1))
                    ->from('asset_sources')
                    ->join('asset_assignments', 'asset_assignments.asset_source_id', '=', 'asset_sources.id')
                    ->whereColumn('asset_sources.acquisition_source_id', 'acquisition_sources.id')
                    ->where(function ($q) use ($schoolId) {
                        $q->where('asset_assignments.school_id', $schoolId)
                          ->orWhereExists(function ($sub2) use ($schoolId) {
                              $sub2->select(DB::raw(1))
                                  ->from('employees')
                                  ->whereColumn('employees.id', 'asset_assignments.employee_id')
                                  ->where('employees.school_id', $schoolId);
                          });
                    });
            });
        }

        if ($request->has('q') && $request->q !== '') {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $sources = $query->select('id', 'name', 'source_type', 'contact_person', 'contact_position')->limit(50)->get();

        return response()->json($sources);
    }
}
