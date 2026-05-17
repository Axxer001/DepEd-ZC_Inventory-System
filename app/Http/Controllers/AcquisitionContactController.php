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
}
