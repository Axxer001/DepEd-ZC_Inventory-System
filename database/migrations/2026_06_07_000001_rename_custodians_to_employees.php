<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 0. Drop all FK constraints that reference custodians before renaming ──
        foreach (['asset_distributions_custodian_id_foreign', 'asset_assignments_custodian_id_foreign'] as $fk) {
            try {
                Schema::table('asset_assignments', function (Blueprint $table) use ($fk) {
                    $table->dropForeign($fk);
                });
            } catch (\Exception $e) {}
        }

        foreach ([
            'asset_transfer_history_from_custodian_id_foreign',
            'asset_transfers_from_custodian_id_foreign',
            'asset_transfer_history_to_custodian_id_foreign',
            'asset_transfers_to_custodian_id_foreign',
        ] as $fk) {
            try {
                Schema::table('asset_transfers', function (Blueprint $table) use ($fk) {
                    $table->dropForeign($fk);
                });
            } catch (\Exception $e) {}
        }

        // Check if school_id in employees is still string/varchar
        $isSchoolIdString = false;
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'school_id')) {
            try {
                $type = Schema::getColumnType('employees', 'school_id');
                if (in_array(strtolower($type), ['string', 'varchar'])) {
                    $isSchoolIdString = true;
                }
            } catch (\Exception $e) {}
        }

        // ── 1. Rename table ───────────────────────────────────────────────────────
        if (Schema::hasTable('custodians') && !Schema::hasTable('employees')) {
            Schema::rename('custodians', 'employees');
        }

        // ── 2. Add temporary school_fk and migrate data only if we haven't done it yet ──
        if (Schema::hasTable('employees') && ($isSchoolIdString || Schema::hasTable('custodians'))) {
            if (!Schema::hasColumn('employees', 'school_fk')) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->unsignedBigInteger('school_fk')->nullable()->after('office_id');
                    $table->foreign('school_fk')->references('id')->on('schools')->nullOnDelete();
                });
            }

            // ── 3. Migrate data: string school_id code → schools.id PK ───────────────
            DB::statement("
                UPDATE employees e
                INNER JOIN schools s ON e.school_id = s.school_id
                SET e.school_fk = s.id
                WHERE e.school_id IS NOT NULL AND e.school_id != ''
            ");

            // ── 4. Drop old string school_id column (may have an index) ───────────────
            try {
                Schema::table('employees', function (Blueprint $table) {
                    $table->dropIndex(['school_id']);
                });
            } catch (\Exception $e) {}

            if (Schema::hasColumn('employees', 'school_id')) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->dropColumn('school_id');
                });
            }

            // ── 5. Rename school_fk → school_id via raw DDL ───────────────────────────
            DB::statement("ALTER TABLE employees CHANGE school_fk school_id BIGINT UNSIGNED NULL");
        }

        // ── 6. Re-bind office_id FK (old name referenced custodians) ─────────────
        foreach (['custodians_office_id_foreign', 'employees_office_id_foreign'] as $fk) {
            try {
                Schema::table('employees', function (Blueprint $table) use ($fk) {
                    $table->dropForeign($fk);
                });
            } catch (\Exception $e) {}
        }

        try {
            Schema::table('employees', function (Blueprint $table) {
                $table->foreign('office_id')->references('id')->on('offices')->nullOnDelete();
            });
        } catch (\Exception $e) {}

        // ── 7. Convert status to strict ENUM ──────────────────────────────────────
        try {
            DB::statement("ALTER TABLE employees MODIFY COLUMN status ENUM('Active','Inactive','On Leave','Retired') NOT NULL DEFAULT 'Active'");
        } catch (\Exception $e) {}

        // ── 7.5. Clean up any invalid data where employee belongs to both (prioritize office_id) ──
        DB::statement("
            UPDATE employees
            SET school_id = NULL
            WHERE office_id IS NOT NULL AND school_id IS NOT NULL
        ");

        // ── 8. XOR CHECK constraint: employee belongs to office OR school, not both ─
        $constraintExists = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = 'depedzc_inventory'
              AND TABLE_NAME = 'employees'
              AND CONSTRAINT_NAME = 'chk_employee_location'
        ");

        if (empty($constraintExists)) {
            DB::statement("
                ALTER TABLE employees
                ADD CONSTRAINT chk_employee_location
                CHECK (NOT (office_id IS NOT NULL AND school_id IS NOT NULL))
            ");
        }
    }

    public function down(): void
    {
        // Drop XOR constraint
        try {
            DB::statement("ALTER TABLE employees DROP CONSTRAINT chk_employee_location");
        } catch (\Exception $e) {}

        // Restore status to varchar
        try {
            DB::statement("ALTER TABLE employees MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Active'");
        } catch (\Exception $e) {}

        // Drop proper school FK and restore string column
        foreach (['employees_school_fk_foreign', 'employees_school_id_foreign'] as $fk) {
            try {
                Schema::table('employees', function (Blueprint $table) use ($fk) {
                    $table->dropForeign($fk);
                });
            } catch (\Exception $e) {}
        }

        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'school_id')) {
            try {
                Schema::table('employees', function (Blueprint $table) {
                    $table->dropColumn('school_id');
                });
            } catch (\Exception $e) {}
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'school_id')) {
            try {
                Schema::table('employees', function (Blueprint $table) {
                    $table->string('school_id')->nullable()->after('office_id')->index();
                });
            } catch (\Exception $e) {}
        }

        // Rename employees back to custodians
        if (Schema::hasTable('employees') && !Schema::hasTable('custodians')) {
            Schema::rename('employees', 'custodians');
        }
    }
};
