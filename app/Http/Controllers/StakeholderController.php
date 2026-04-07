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
            ->select('id', 'parent_id', 'name', 'type', 'school_id', 'source_type', 'entity_type', 'position')
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
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|max:255',
            'parent_id'   => 'nullable|integer|exists:stakeholders,id',
            'school_id'   => 'nullable|integer|exists:schools,id',
            'source_type' => 'nullable|string|in:Government,Contractor,Donor,NGO,Other',
            'entity_type' => 'nullable|string|in:School,District,Division,Individual,External',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';

        // Rule 1: Duplicate source prevention (case-insensitive)
        if ($request->type === 'Distributor' && empty($request->parent_id)) {
            $existing = DB::table('stakeholders')
                ->whereRaw('LOWER(name) = ?', [strtolower(trim($request->name))])
                ->where('type', 'Distributor')
                ->first();
            if ($existing) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => "A distributor named '{$existing->name}' already exists.", 'existing_id' => $existing->id], 409);
                }
                return back()->withErrors(["A distributor named '{$existing->name}' already exists."]);
            }
        }

        $id = DB::table('stakeholders')->insertGetId([
            'parent_id'   => $request->parent_id,
            'name'        => trim($request->name),
            'type'        => trim($request->type),
            'school_id'   => $request->school_id,
            'source_type' => $request->source_type,
            'entity_type' => $request->entity_type ?? 'School',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $parentLabel = $request->parent_id
            ? DB::table('stakeholders')->where('id', $request->parent_id)->value('name')
            : 'None (Main Category)';

        DB::table('system_logs')->insert([
            'user'        => $userName,
            'activity'    => "Created stakeholder '{$request->name}' (Type: {$request->type}, Parent: {$parentLabel})",
            'module'      => 'Stakeholders',
            'action_type' => 'Create',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Stakeholder '{$request->name}' created successfully.",
                'id'      => $id,
            ]);
        }

        return back()->with('success', "Stakeholder '{$request->name}' created successfully.");
    }

    /**
     * Store a single Individual or Office recipient (Tracks B & C).
     */
    public function storeIndividualRecipient(Request $request)
    {
        $request->validate([
            'type'       => 'required|string',
            'org_name'   => 'nullable|string|max:255',
            'person_name'=> 'nullable|string|max:255',
            'position'   => 'nullable|string|max:255',
            'is_office'  => 'nullable',
        ]);

        $userName = auth()->user() ? auth()->user()->name : 'System';
        $isOffice = $request->input('is_office') == '1';

        if ($isOffice) {
            // Track C — Office / Department
            $officeName = trim($request->org_name ?? '');
            if (empty($officeName)) {
                return back()->withErrors(['Office name is required.']);
            }
            $entityType = $request->input('office_entity_type', 'Division');

            // Duplicate check (case-insensitive)
            $existing = DB::table('stakeholders')
                ->whereRaw('LOWER(name) = ?', [strtolower($officeName)])
                ->where('type', 'Recipient')
                ->whereNull('parent_id')
                ->first();

            if ($existing) {
                return redirect('/inventory-setup?step=2&mode=add')
                    ->with('success', "Office '{$existing->name}' already exists and was not duplicated.");
            }

            $id = DB::table('stakeholders')->insertGetId([
                'name'        => $officeName,
                'type'        => 'Recipient',
                'entity_type' => $entityType,
                'source_type' => null,
                'position'    => null,
                'person_name' => null,
                'parent_id'   => null,
                'school_id'   => null,
                'status'      => 'Active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::table('system_logs')->insert([
                'user'        => $userName,
                'activity'    => "Registered office recipient '{$officeName}' (Entity: {$entityType})",
                'module'      => 'Stakeholders',
                'action_type' => 'Create',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return redirect('/inventory-setup?step=2&mode=add')
                ->with('success', "Office '{$officeName}' registered successfully as a recipient.");

        } else {
            // Track B — Individual (Position-Based)
            $personName = trim($request->person_name ?? '');
            $position   = trim($request->position ?? '');
            if (empty($personName) || empty($position)) {
                return back()->withErrors(['Full name and position are both required.']);
            }

            $entityType  = $request->input('individual_entity_type', 'School');
            $linkedSchool = trim($request->org_name ?? '');

            // Resolve school_id if a linked school name was provided
            $schoolId = null;
            if (!empty($linkedSchool)) {
                $school = DB::table('schools')->whereRaw('LOWER(name) = ?', [strtolower($linkedSchool)])->first();
                $schoolId = $school ? $school->id : null;
            }

            // The 'name' column stores a readable display: "Position — PersonName"
            $displayName = "{$position} — {$personName}";

            // Loose duplicate check: same person name + same position
            $existing = DB::table('stakeholders')
                ->whereRaw('LOWER(person_name) = ?', [strtolower($personName)])
                ->whereRaw('LOWER(position) = ?', [strtolower($position)])
                ->where('type', 'Recipient')
                ->first();

            if ($existing) {
                return redirect('/inventory-setup?step=2&mode=add')
                    ->with('success', "'{$personName}' ({$position}) already exists and was not duplicated.");
            }

            $id = DB::table('stakeholders')->insertGetId([
                'name'        => $displayName,
                'type'        => 'Recipient',
                'entity_type' => $entityType,
                'source_type' => null,
                'position'    => $position,
                'person_name' => $personName,
                'parent_id'   => null,
                'school_id'   => $schoolId,
                'status'      => 'Active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::table('system_logs')->insert([
                'user'        => $userName,
                'activity'    => "Registered individual recipient '{$personName}' as {$position} (Entity: {$entityType})",
                'module'      => 'Stakeholders',
                'action_type' => 'Create',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return redirect('/inventory-setup?step=2&mode=add')
                ->with('success', "'{$personName}' ({$position}) registered successfully as a recipient.");
        }
    }

    /**
     * Store a group of stakeholders (Main entity + personnel).
     */
    public function storeGroup(Request $request)
    {
        $request->validate([
            'org_name'    => 'nullable|string|max:255',
            'personnel'   => 'nullable|array',
            'personnel.*' => 'nullable|string|max:255',
            'type'        => 'required|string|max:255',
            'source_type' => 'nullable|string|in:Government,Contractor,Donor,NGO,Other',
            'entity_type' => 'nullable|string|in:School,District,Division,Individual,External',
        ]);

        $userName   = auth()->user() ? auth()->user()->name : 'System';
        $type       = trim($request->type);
        $orgName    = trim($request->org_name);
        $sourceType = $request->source_type;
        $entityType = $request->entity_type ?? ($type === 'Distributor' ? 'External' : 'School');

        $addedCount = 0;
        
        if (!empty($orgName)) {
            $parent = DB::table('stakeholders')
                ->where('name', $orgName)
                ->where('type', $type)
                ->whereNull('parent_id')
                ->first();

            if ($parent) {
                // If exists and this is a Distributor, update source_type if not already set
                if ($type === 'Distributor' && $sourceType && empty($parent->source_type)) {
                    DB::table('stakeholders')->where('id', $parent->id)->update(['source_type' => $sourceType, 'updated_at' => now()]);
                }
                $parentId = $parent->id ?? null;
            } else {
                $parentId = DB::table('stakeholders')->insertGetId([
                    'name'        => $orgName,
                    'type'        => $type,
                    'source_type' => $sourceType,
                    'entity_type' => $entityType,
                    'parent_id'   => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                
                DB::table('system_logs')->insert([
                    'user' => $userName,
                    'activity' => "Created main {$type} '{$orgName}'",
                    'module' => 'Stakeholders',
                    'action_type' => 'Create',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (!empty($request->personnel)) {
                foreach ($request->personnel as $personName) {
                    $personName = trim($personName);
                    if (empty($personName)) continue;
                    
                    $exists = DB::table('stakeholders')
                        ->where('name', $personName)
                        ->where('parent_id', $parentId)
                        ->where('type', $type)
                        ->exists();
                        
                    if (!$exists) {
                        DB::table('stakeholders')->insert([
                            'name'        => $personName,
                            'type'        => $type,
                            'source_type' => $sourceType,
                            'entity_type' => $entityType,
                            'parent_id'   => $parentId,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                        $addedCount++;
                    }
                }
                if ($addedCount > 0) {
                    DB::table('system_logs')->insert([
                        'user' => $userName,
                        'activity' => "Added {$addedCount} sub-categor(ies) to {$type} '{$orgName}'",
                        'module' => 'Stakeholders',
                        'action_type' => 'Create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // --- Cross-Registration Logic (Phase 3) ---
        $crossAdded = 0;
        $copyParents = $request->input('copy_parents', []);
        $copyChildren = $request->input('copy_children', []);
        
        if (!empty($copyChildren)) {
            $childrenEntities = DB::table('stakeholders')->whereIn('id', $copyChildren)->get();
            foreach ($childrenEntities as $child) {
                if ($child->parent_id && !in_array($child->parent_id, $copyParents)) {
                    $copyParents[] = $child->parent_id;
                }
            }
        }
        
        if (!empty($copyParents)) {
            $parentsToCopy = DB::table('stakeholders')->whereIn('id', $copyParents)->get();
            $parentMap = [];
            
            foreach ($parentsToCopy as $oldParent) {
                $existingNewParent = DB::table('stakeholders')
                    ->where('name', $oldParent->name)
                    ->where('type', $type)
                    ->whereNull('parent_id')
                    ->first();
                    
                if ($existingNewParent) {
                    $parentMap[$oldParent->id] = $existingNewParent->id;
                } else {
                    $newParentId = DB::table('stakeholders')->insertGetId([
                        'name'        => $oldParent->name,
                        'type'        => $type,
                        'source_type' => $sourceType,
                        'entity_type' => $entityType,
                        'parent_id'   => null,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $parentMap[$oldParent->id] = $newParentId;
                    $crossAdded++;
                }
            }
            
            if (!empty($copyChildren) && isset($childrenEntities)) {
                foreach ($childrenEntities as $oldChild) {
                    if (isset($parentMap[$oldChild->parent_id])) {
                        $newParentId = $parentMap[$oldChild->parent_id];
                        $existingNewChild = DB::table('stakeholders')
                            ->where('name', $oldChild->name)
                            ->where('type', $type)
                            ->where('parent_id', $newParentId)
                            ->exists();
                            
                        if (!$existingNewChild) {
                            DB::table('stakeholders')->insert([
                                'name'        => $oldChild->name,
                                'type'        => $type,
                                'source_type' => $sourceType,
                                'entity_type' => $entityType,
                                'parent_id'   => $newParentId,
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);
                            $crossAdded++;
                        }
                    }
                }
            }
            
            if ($crossAdded > 0) {
                $opposingType = ($type === 'Distributor') ? 'Recipient' : 'Distributor';
                DB::table('system_logs')->insert([
                    'user' => $userName,
                    'activity' => "Cross-registered {$crossAdded} entities from {$opposingType} to {$type}",
                    'module' => 'Stakeholders',
                    'action_type' => 'Create',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (empty($orgName) && empty($copyParents) && empty($copyChildren)) {
            return back()->withErrors(['The main organization field cannot be empty unless you have selected existing entities to copy from the right panel.']);
        }

        $msgParts = [];
        if (!empty($orgName)) {
            $baseMsg = "Successfully saved {$orgName}!";
            if ($addedCount > 0) $baseMsg .= " Added {$addedCount} new sub-category/personnel.";
            $msgParts[] = $baseMsg;
        }
        
        if ($crossAdded > 0) {
            $msgParts[] = "Cross-registered {$crossAdded} opposite entities.";
        }

        if (empty($msgParts)) {
            $msgParts[] = "No new entities were created (they may already exist).";
        }

        return redirect('/inventory-setup?step=2&mode=add')->with('success', implode(' ', $msgParts));
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

    /**
     * Retrieves and filters entities from the opposite type for cross-registration.
     * Prevents displaying entities that already exist in the target type.
     */
    public static function getCrossRegistrationEntities($targetType)
    {
        $oppositeType = ($targetType === 'Distributor') ? 'Recipient' : 'Distributor';

        // Fetch target entities to build existence maps
        $targetMains = \Illuminate\Support\Facades\DB::table('stakeholders')->where('type', $targetType)->whereNull('parent_id')->get();
        $targetSubs = \Illuminate\Support\Facades\DB::table('stakeholders')->where('type', $targetType)->whereNotNull('parent_id')->get();
        
        $targetMainMap = [];
        foreach ($targetMains as $m) $targetMainMap[strtolower($m->name)] = $m->id;
        
        $targetSubMap = [];
        foreach ($targetSubs as $s) {
            $targetSubMap[$s->parent_id . '_' . strtolower($s->name)] = true;
        }

        // Fetch opposite entities (potential copies)
        $oppositeMainsQuery = \Illuminate\Support\Facades\DB::table('stakeholders')->where('type', $oppositeType)->whereNull('parent_id')->orderBy('name')->get();
        $oppositeSubsQuery = \Illuminate\Support\Facades\DB::table('stakeholders')->where('type', $oppositeType)->whereNotNull('parent_id')->orderBy('name')->get();

        // Filter sub-categories that already exist in the target type under the same parent name
        $filteredSubs = $oppositeSubsQuery->filter(function($sub) use ($oppositeMainsQuery, $targetMainMap, $targetSubMap) {
            $parent = $oppositeMainsQuery->firstWhere('id', $sub->parent_id);
            if (!$parent) return true;

            $parentNameLower = strtolower($parent->name);
            if (isset($targetMainMap[$parentNameLower])) {
                $targetParentId = $targetMainMap[$parentNameLower];
                $subKey = $targetParentId . '_' . strtolower($sub->name);
                if (isset($targetSubMap[$subKey])) {
                    return false; // Sub already exists in target
                }
            }
            return true;
        })->values();

        // Filter main categories if they are fully registered already
        $filteredMains = $oppositeMainsQuery->filter(function($main) use ($targetMainMap, $oppositeSubsQuery, $filteredSubs) {
            $nameLower = strtolower($main->name);
            
            if (isset($targetMainMap[$nameLower])) {
                // Parent already exists. Check if it has any surviving sub-categories
                $originalSubsCount = $oppositeSubsQuery->where('parent_id', $main->id)->count();
                if ($originalSubsCount == 0) {
                    return false; // Parent exists and had no subs to copy
                }
                
                $survivingSubsCount = $filteredSubs->where('parent_id', $main->id)->count();
                if ($survivingSubsCount == 0) {
                    return false; // Parent exists and ALL its subs are already copied
                }
            }
            return true;
        })->values();

        return [
            'oppositeMains' => $filteredMains,
            'oppositeSubs' => $filteredSubs,
        ];
    }
}
