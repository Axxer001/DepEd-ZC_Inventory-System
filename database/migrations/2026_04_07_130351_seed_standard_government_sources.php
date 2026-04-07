<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migration C: Seed standard government sources & the Division Office.
     *
     * Pre-seeding these prevents users from creating near-duplicates like:
     *   "LGU", "LGU ZC", "Local Government Unit Zamboanga" → all the same entity
     *
     * Also seeds the DepEd Division Office of Zamboanga City as a recipient
     * so it can immediately receive assets without manual creation.
     */
    public function up(): void
    {
        $now = now();

        // === DISTRIBUTORS (Sources) ===
        $governmentSources = [
            [
                'name'        => 'DepEd Central Office',
                'type'        => 'Distributor',
                'source_type' => 'Government',
                'entity_type' => 'Division',
                'position'    => null,
                'person_name' => null,
                'parent_id'   => null,
                'school_id'   => null,
                'status'      => 'Active',
            ],
            [
                'name'        => 'Department of Budget and Management (DBM)',
                'type'        => 'Distributor',
                'source_type' => 'Government',
                'entity_type' => 'External',
                'position'    => null,
                'person_name' => null,
                'parent_id'   => null,
                'school_id'   => null,
                'status'      => 'Active',
            ],
            [
                'name'        => 'Local Government Unit — Zamboanga City (LGU-ZC)',
                'type'        => 'Distributor',
                'source_type' => 'Government',
                'entity_type' => 'External',
                'position'    => null,
                'person_name' => null,
                'parent_id'   => null,
                'school_id'   => null,
                'status'      => 'Active',
            ],
            [
                'name'        => 'Special Education Fund (SEF)',
                'type'        => 'Distributor',
                'source_type' => 'Government',
                'entity_type' => 'External',
                'position'    => null,
                'person_name' => null,
                'parent_id'   => null,
                'school_id'   => null,
                'status'      => 'Active',
            ],
            [
                'name'        => 'General Appropriations Act (GAA)',
                'type'        => 'Distributor',
                'source_type' => 'Government',
                'entity_type' => 'External',
                'position'    => null,
                'person_name' => null,
                'parent_id'   => null,
                'school_id'   => null,
                'status'      => 'Active',
            ],
            [
                'name'        => 'Department of Interior and Local Government (DILG)',
                'type'        => 'Distributor',
                'source_type' => 'Government',
                'entity_type' => 'External',
                'position'    => null,
                'person_name' => null,
                'parent_id'   => null,
                'school_id'   => null,
                'status'      => 'Active',
            ],
            [
                'name'        => 'System Warehouse',
                'type'        => 'System',
                'source_type' => 'Government',
                'entity_type' => 'Division',
                'position'    => null,
                'person_name' => null,
                'parent_id'   => null,
                'school_id'   => null,
                'status'      => 'Active',
            ],
        ];

        foreach ($governmentSources as $source) {
            DB::table('stakeholders')->insert(array_merge($source, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // === RECIPIENTS — Division Office ===
        DB::table('stakeholders')->insert([
            'name'        => 'DepEd Division Office — Zamboanga City',
            'type'        => 'Recipient',
            'source_type' => null,
            'entity_type' => 'Division',
            'position'    => null,
            'person_name' => null,
            'parent_id'   => null,
            'school_id'   => null,   // No school linkage — it IS the division
            'status'      => 'Active',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
    }

    public function down(): void
    {
        // Remove only the seeded records (safe: matches by exact name)
        $seededNames = [
            'DepEd Central Office',
            'Department of Budget and Management (DBM)',
            'Local Government Unit — Zamboanga City (LGU-ZC)',
            'Special Education Fund (SEF)',
            'General Appropriations Act (GAA)',
            'Department of Interior and Local Government (DILG)',
            'System Warehouse',
            'DepEd Division Office — Zamboanga City',
        ];
        DB::table('stakeholders')->whereIn('name', $seededNames)->delete();
    }
};
