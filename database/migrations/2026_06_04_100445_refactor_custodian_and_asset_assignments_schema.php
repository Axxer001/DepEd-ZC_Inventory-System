<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Schema Refactor:
     * 1. Add school_id + office_id to custodians (custodian carries their assignment context).
     * 2. Drop school_id, office_id, and nature_of_occupancy from asset_assignments
     *    (school/office are now resolved via custodian_id, occupancy is irrelevant for assets).
     */
    public function up(): void
    {
        // ── 1. CUSTODIANS — add school_id and office_id ──────────────────────
        Schema::table('custodians', function (Blueprint $table) {
            // school_id: string code like "105001" referencing schools.school_id (not PK)
            $table->string('school_id')->nullable()->after('position')->index();
            // office_id: FK to offices.id for division office staff
            $table->unsignedBigInteger('office_id')->nullable()->after('school_id');
            $table->foreign('office_id')->references('id')->on('offices')->onDelete('set null');
        });

        // ── 2. Migrate existing school_id / office_id data from asset_assignments ──
        // Propagate school_id and office_id to custodians where they are null but the
        // assignment has a value, using the most recent assignment per custodian.
        if (Schema::hasColumn('asset_assignments', 'school_id')) {
            DB::statement("
                UPDATE custodians c
                INNER JOIN (
                    SELECT custodian_id,
                           MAX(school_id)  AS school_id,
                           MAX(office_id)  AS office_id
                    FROM   asset_assignments
                    WHERE  custodian_id IS NOT NULL
                    GROUP  BY custodian_id
                ) aa ON c.id = aa.custodian_id
                SET c.school_id = COALESCE(c.school_id, aa.school_id),
                    c.office_id = COALESCE(c.office_id, aa.office_id)
            ");
        }

        // ── 3. ASSET_ASSIGNMENTS — drop migrated & deprecated columns ─────────
        Schema::table('asset_assignments', function (Blueprint $table) {
            // Drop the FK first before dropping the column
            if (Schema::hasColumn('asset_assignments', 'office_id')) {
                // Check if the foreign key exists before dropping
                try {
                    $table->dropForeign(['office_id']);
                } catch (\Exception $e) {
                    // FK may not exist if it was never created cleanly, safe to ignore
                }
                $table->dropColumn('office_id');
            }
            if (Schema::hasColumn('asset_assignments', 'school_id')) {
                $table->dropColumn('school_id');
            }
            if (Schema::hasColumn('asset_assignments', 'nature_of_occupancy')) {
                $table->dropColumn('nature_of_occupancy');
            }
        });
    }

    public function down(): void
    {
        // ── Restore asset_assignments columns ─────────────────────────────────
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->string('school_id')->nullable()->after('custodian_id');
            $table->unsignedBigInteger('office_id')->nullable()->after('school_id');
            $table->string('nature_of_occupancy')->default('')->after('office_id');
            $table->foreign('office_id')->references('id')->on('offices')->onDelete('set null');
        });

        // ── Remove custodians columns ──────────────────────────────────────────
        Schema::table('custodians', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropColumn(['school_id', 'office_id']);
        });
    }
};
