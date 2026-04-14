<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipientRegistryController extends Controller
{
    /**
     * Handle adding a recipient to the batch list.
     * This resolves all 4 cases (school/external × with/without personnel)
     * and returns the card data for the frontend list.
     */
    public function add(Request $request)
    {
        $request->validate([
            'entity_type'   => 'required|in:school,external',
            'school_id'     => 'nullable|integer|exists:schools,id',
            'external_name' => 'nullable|string|max:255',
            'person_name'   => 'nullable|string|max:255',
            'position'      => 'nullable|string|max:255',
        ]);

        $entityType   = $request->entity_type;    // 'school' | 'external'
        $schoolId     = $request->school_id;       // int or null
        $externalName = trim($request->external_name ?? '');
        $personName   = trim($request->person_name ?? '');
        $position     = trim($request->position ?? '');
        $hasPersonnel = $personName !== '';

        $stakeholder = null;
        $isNew       = false;

        // ── SCHOOL ──────────────────────────────────────────────────────────
        if ($entityType === 'school') {
            if (!$schoolId) {
                return response()->json(['error' => 'School is required.'], 422);
            }

            $school = DB::table('schools')->where('id', $schoolId)->first();
            if (!$school) {
                return response()->json(['error' => 'School not found.'], 422);
            }

            if (!$hasPersonnel) {
                // Case A: School without personnel → register the school itself
                $stakeholder = DB::table('stakeholders')
                    ->where('school_id', $schoolId)
                    ->where('entity_type', 'School')
                    ->whereNull('person_name')
                    ->first();

                if (!$stakeholder) {
                    $id = DB::table('stakeholders')->insertGetId([
                        'name'        => $school->name,
                        'type'        => 'Recipient',
                        'entity_type' => 'School',
                        'school_id'   => $schoolId,
                        'status'      => 'Active',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $stakeholder = DB::table('stakeholders')->find($id);
                    $isNew = true;
                }

                return response()->json([
                    'id'           => $stakeholder->id,
                    'display_name' => $school->name,
                    'sub_label'    => 'School Recipient',
                    'is_new'       => $isNew,
                ]);

            } else {
                // Case B: School with personnel → register the personnel only
                $stakeholder = DB::table('stakeholders')
                    ->where('school_id', $schoolId)
                    ->whereNotNull('person_name')
                    ->whereRaw('LOWER(person_name) = ?', [strtolower($personName)])
                    ->first();

                if (!$stakeholder) {
                    $id = DB::table('stakeholders')->insertGetId([
                        'name'        => $personName,
                        'person_name' => $personName,
                        'type'        => 'Recipient',
                        'entity_type' => 'Individual',
                        'school_id'   => $schoolId,
                        'position'    => $position ?: null,
                        'status'      => 'Active',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $stakeholder = DB::table('stakeholders')->find($id);
                    $isNew = true;
                }

                $subLabel = ($position ? $position . ' • ' : '') . $school->name;

                return response()->json([
                    'id'           => $stakeholder->id,
                    'display_name' => $personName,
                    'sub_label'    => $subLabel,
                    'is_new'       => $isNew,
                ]);
            }
        }

        // ── EXTERNAL ────────────────────────────────────────────────────────
        if ($entityType === 'external') {
            if (!$externalName) {
                return response()->json(['error' => 'External recipient name is required.'], 422);
            }

            // Always resolve (or create) the External org first
            $externalOrg = DB::table('stakeholders')
                ->whereRaw('LOWER(name) = ?', [strtolower($externalName)])
                ->where('entity_type', 'External')
                ->whereNull('parent_id')
                ->whereNull('person_name')
                ->first();

            $externalOrgIsNew = false;
            if (!$externalOrg) {
                $orgId = DB::table('stakeholders')->insertGetId([
                    'name'        => $externalName,
                    'type'        => 'Recipient',
                    'entity_type' => 'External',
                    'status'      => 'Active',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $externalOrg = DB::table('stakeholders')->find($orgId);
                $externalOrgIsNew = true;
            }

            if (!$hasPersonnel) {
                // Case C: External org without personnel
                return response()->json([
                    'id'           => $externalOrg->id,
                    'display_name' => $externalName,
                    'sub_label'    => 'External Recipient',
                    'is_new'       => $externalOrgIsNew,
                ]);
            } else {
                // Case D: External org with personnel → register person under the org
                $personalStakeholder = DB::table('stakeholders')
                    ->where('parent_id', $externalOrg->id)
                    ->whereNotNull('person_name')
                    ->whereRaw('LOWER(person_name) = ?', [strtolower($personName)])
                    ->first();

                if (!$personalStakeholder) {
                    $personId = DB::table('stakeholders')->insertGetId([
                        'name'        => $personName,
                        'person_name' => $personName,
                        'type'        => 'Recipient',
                        'entity_type' => 'Individual',
                        'parent_id'   => $externalOrg->id,
                        'position'    => $position ?: null,
                        'status'      => 'Active',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $personalStakeholder = DB::table('stakeholders')->find($personId);
                    $isNew = true;
                }

                $subLabel = ($position ? $position . ' • ' : '') . $externalName;

                return response()->json([
                    'id'           => $personalStakeholder->id,
                    'display_name' => $personName,
                    'sub_label'    => $subLabel,
                    'is_new'       => $isNew,
                ]);
            }
        }

        return response()->json(['error' => 'Invalid entity type.'], 422);
    }
}
