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
            ->where('asrc.acquisition_source_id', $id)
            ->select(
                'asrc.id',
                'i.name as item_name',
                'asrc.description',
                'asrc.quantity',
                'asrc.asset_cost',
                'asrc.acceptance_date',
                'asrc.created_at'
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

        if ($request->has('q') && $request->q !== '') {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $sources = $query->select('id', 'name', 'source_type', 'contact_person', 'contact_position')->limit(50)->get();

        return response()->json($sources);
    }
}
