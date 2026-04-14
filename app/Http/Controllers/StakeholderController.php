<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StakeholderController extends Controller
{
    /**
     * Show the stakeholder admin page.
     */
    public function index(Request $request)
    {
        $stakeholders = DB::table('stakeholders')
            ->select('id', 'parent_id', 'name', 'type', 'school_id', 'entity_type', 'position')
            ->orderBy('name')
            ->get();

        return view('admin.stakeholders', compact('stakeholders'));
    }

    /**
     * Return stakeholders as JSON for API use.
     */
    public function list(Request $request)
    {
        $stakeholders = DB::table('stakeholders')
            ->select('id', 'parent_id', 'name', 'type', 'school_id', 'entity_type', 'position', 'status')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return response()->json($stakeholders);
    }

    /**
     * Store a new stakeholder (used by admin panel).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|max:255',
            'parent_id'   => 'nullable|integer|exists:stakeholders,id',
            'school_id'   => 'nullable|integer|exists:schools,id',
            'entity_type' => 'nullable|string|in:School,District,Division,Individual,External',
        ]);

        $id = DB::table('stakeholders')->insertGetId([
            'parent_id'   => $request->parent_id,
            'name'        => trim($request->name),
            'type'        => trim($request->type),
            'school_id'   => $request->school_id,
            'entity_type' => $request->entity_type ?? 'School',
            'status'      => 'Active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json(['id' => $id, 'message' => 'Stakeholder created.']);
    }

    /**
     * Update an existing stakeholder.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'        => 'sometimes|string|max:255',
            'type'        => 'sometimes|string|max:255',
            'entity_type' => 'nullable|string|in:School,District,Division,Individual,External',
            'status'      => 'nullable|string|in:Active,Transferred,Resigned,Inactive',
        ]);

        DB::table('stakeholders')->where('id', $id)->update(array_merge(
            $request->only(['name', 'type', 'entity_type', 'position', 'person_name', 'status']),
            ['updated_at' => now()]
        ));

        return response()->json(['message' => 'Stakeholder updated.']);
    }

    /**
     * Delete a stakeholder.
     */
    public function destroy($id)
    {
        DB::table('stakeholders')->where('id', $id)->delete();
        return response()->json(['message' => 'Stakeholder deleted.']);
    }
}
