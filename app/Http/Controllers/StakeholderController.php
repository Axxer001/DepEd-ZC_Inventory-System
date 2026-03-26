<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StakeholderController extends Controller
{
    /**
     * Return the stakeholder management page.
     */
    public function index()
    {
        $stakeholders = DB::table('stakeholders')
            ->leftJoin('schools', 'stakeholders.school_id', '=', 'schools.id')
            ->select(
                'stakeholders.*',
                'schools.school_id as school_code',
                'schools.name as school_name'
            )
            ->orderBy('stakeholders.name')
            ->get();

        return view('admin.stakeholders', compact('stakeholders'));
    }

    /**
     * Return all stakeholders as JSON (for AJAX dropdowns).
     */
    public function list()
    {
        $stakeholders = DB::table('stakeholders')
            ->select('id', 'parent_id', 'name', 'type', 'school_id')
            ->orderBy('name')
            ->get();

        return response()->json($stakeholders);
    }

    /**
     * Store a new stakeholder (Main Category or Sub-category).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:stakeholders,id',
            'school_id' => 'nullable|integer|exists:schools,id',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';

        $id = DB::table('stakeholders')->insertGetId([
            'parent_id' => $request->parent_id,
            'name' => trim($request->name),
            'type' => trim($request->type),
            'school_id' => $request->school_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $parentLabel = $request->parent_id
            ? DB::table('stakeholders')->where('id', $request->parent_id)->value('name')
            : 'None (Main Category)';

        DB::table('system_logs')->insert([
            'user' => $userName,
            'activity' => "Created stakeholder '{$request->name}' (Type: {$request->type}, Parent: {$parentLabel})",
            'module' => 'Stakeholders',
            'action_type' => 'Create',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Stakeholder '{$request->name}' created successfully.",
                'id' => $id,
            ]);
        }

        return back()->with('success', "Stakeholder '{$request->name}' created successfully.");
    }

    /**
     * Update an existing stakeholder.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $old = DB::table('stakeholders')->where('id', $id)->first();

        if (!$old) {
            return response()->json(['success' => false, 'message' => 'Stakeholder not found.'], 404);
        }

        DB::table('stakeholders')->where('id', $id)->update([
            'name' => trim($request->name),
            'type' => trim($request->type),
            'updated_at' => now(),
        ]);

        $oldName = $old->name ?? 'Unknown';
        DB::table('system_logs')->insert([
            'user' => $userName,
            'activity' => "Updated stakeholder '{$oldName}' → '{$request->name}'",
            'module' => 'Stakeholders',
            'action_type' => 'Update',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => "Stakeholder updated."]);
        }

        return back()->with('success', "Stakeholder updated.");
    }

    /**
     * Delete a stakeholder (cascades to children via FK).
     */
    public function destroy(Request $request, $id)
    {
        $userName = auth()->user() ? auth()->user()->name : 'System';
        $stakeholder = DB::table('stakeholders')->where('id', $id)->first();

        if (!$stakeholder) {
            return response()->json(['success' => false, 'message' => 'Stakeholder not found.'], 404);
        }

        $stakeholderType = $stakeholder->type ?? 'Unknown';
        $stakeholderName = $stakeholder->name ?? 'Unknown';

        // Prevent deleting System Warehouse
        if ($stakeholderType === 'System') {
            return response()->json(['success' => false, 'message' => 'Cannot delete the System Warehouse stakeholder.'], 403);
        }

        DB::table('stakeholders')->where('id', $id)->delete();

        DB::table('system_logs')->insert([
            'user' => $userName,
            'activity' => "Deleted stakeholder '{$stakeholderName}' (Type: {$stakeholderType})",
            'module' => 'Stakeholders',
            'action_type' => 'Delete',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => "Stakeholder '{$stakeholderName}' deleted."]);
        }

        return back()->with('success', "Stakeholder '{$stakeholderName}' deleted.");
    }
}
