<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcquisitionContactController extends Controller
{
    /**
     * Display a listing of acquisition contacts.
     */
    public function index()
    {
        return view('admin.acquisition-contacts');
    }

    /**
     * Get filter options for the acquisition contacts registry.
     */
    public function getFilters()
    {
        $sources = DB::table('acquisition_sources')->pluck('name')->unique()->filter()->values();
        $positions = DB::table('acquisition_contacts')->distinct()->pluck('position')->filter()->values();

        return response()->json([
            'sources' => $sources,
            'positions' => $positions
        ]);
    }

    /**
     * Get preview data for the acquisition contacts registry.
     */
    public function getPreview(Request $request)
    {
        $filters = $request->input('filters', []);
        
        $query = DB::table('acquisition_contacts as ac')
            ->leftJoin('acquisition_sources as as', 'ac.acquisition_source_id', '=', 'as.id')
            ->select([
                'ac.id',
                'ac.name',
                'ac.position',
                'ac.contact_number',
                'ac.email',
                'as.name as organization'
            ]);

        if (!empty($filters['source'])) {
            $query->where('as.name', $filters['source']);
        }

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $query->where(function($q) use ($s) {
                $q->where('ac.name', 'like', $s)
                  ->orWhere('ac.email', 'like', $s)
                  ->orWhere('as.name', 'like', $s);
            });
        }

        $rows = $query->orderBy('ac.name')->get();

        return response()->json(['rows' => $rows]);
    }
    /**
     * Display the profile page for a specific supplier personnel.
     */
    public function profile($id)
    {
        $contact = DB::table('acquisition_contacts as ac')
            ->leftJoin('acquisition_sources as as', 'ac.acquisition_source_id', '=', 'as.id')
            ->select([
                'ac.id',
                'ac.name',
                'ac.position',
                'ac.contact_number',
                'ac.email',
                'as.name as organization'
            ])
            ->where('ac.id', $id)
            ->first();

        if (!$contact) {
            abort(404, 'Supplier Personnel not found');
        }

        // Stats of supplied assets
        $stats = DB::table('asset_assignments as aa')
            ->join('asset_sources as asrc', 'aa.asset_source_id', '=', 'asrc.id')
            ->where('asrc.acquisition_contact_id', $id)
            ->selectRaw('COUNT(aa.id) as total_supplied, COALESCE(SUM(aa.acquisition_cost), 0) as total_value')
            ->first();

        // List of all assets supplied by this person
        $assets = DB::table('asset_assignments as aa')
            ->join('asset_sources as asrc', 'aa.asset_source_id', '=', 'asrc.id')
            ->join('items as i', 'asrc.item_id', '=', 'i.id')
            ->join('categories as cat', 'i.category_id', '=', 'cat.id')
            ->leftJoin('employees as e', 'aa.employee_id', '=', 'e.id')
            ->leftJoin('offices as o', 'e.office_id', '=', 'o.id')
            ->leftJoin('schools as s', 'e.school_id', '=', 's.id')
            ->where('asrc.acquisition_contact_id', $id)
            ->select(
                'aa.id',
                'aa.property_number',
                'aa.acquisition_date',
                'aa.acquisition_cost as asset_cost',
                'i.name as item_name',
                'cat.name as category_name',
                'aa.condition',
                'o.name as office_name',
                's.name as school_name'
            )
            ->orderByDesc('aa.acquisition_date')
            ->get();

        return view('admin.supplier-contacts.profile', compact('contact', 'stats', 'assets'));
    }
}
