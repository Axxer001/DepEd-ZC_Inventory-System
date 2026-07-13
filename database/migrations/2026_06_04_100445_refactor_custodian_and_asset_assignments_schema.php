<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Schema Refactor:
     * school_id + office_id are now defined directly on employees in earlier migrations.
     * This migration only handles dropping the migrated & deprecated columns from asset_assignments.
     */
    public function up(): void
    {
        // ── Drop migrated & deprecated columns from asset_assignments ─────────
        Schema::table('asset_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('asset_assignments', 'office_id')) {
                try {
                    $table->dropForeign(['office_id']);
                } catch (\Exception $e) {}
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
    }
};
