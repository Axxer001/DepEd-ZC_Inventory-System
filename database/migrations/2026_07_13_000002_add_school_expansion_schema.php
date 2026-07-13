<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Alter 'users' table
        if (!Schema::hasColumn('users', 'system_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('system_type', ['main', 'school'])->default('main')->after('role');
                $table->unsignedBigInteger('school_id')->nullable()->after('system_type');

                $table->foreign('school_id')
                    ->references('id')
                    ->on('schools')
                    ->onDelete('restrict');
            });
        }

        // Hard constraint: super_admin can never be assigned to a user where system_type = 'school'
        if (DB::connection()->getDriverName() === 'mysql') {
            $constraintExists = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'users'
                  AND CONSTRAINT_NAME = 'chk_user_school_super_admin'
            ");
            if (empty($constraintExists)) {
                DB::statement("ALTER TABLE users ADD CONSTRAINT chk_user_school_super_admin CHECK (NOT (role = 'super_admin' AND system_type = 'school'))");
            }
        }

        // 2. Convert employees.school_id from loose varchar to proper FK
        // Drop the XOR CHECK constraint if it exists first
        if (DB::connection()->getDriverName() === 'mysql') {
            $constraintExists = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'employees'
                  AND CONSTRAINT_NAME = 'chk_employee_location'
            ");
            if (!empty($constraintExists)) {
                DB::statement("ALTER TABLE employees DROP CONSTRAINT chk_employee_location");
            }
        }

        // Check the type of school_id on employees
        $isSchoolIdVarchar = false;
        if (Schema::hasColumn('employees', 'school_id')) {
            $columns = Schema::getColumns('employees');
            foreach ($columns as $col) {
                if ($col['name'] === 'school_id' && str_contains(strtolower($col['type']), 'varchar')) {
                    $isSchoolIdVarchar = true;
                    break;
                }
            }
        }

        if ($isSchoolIdVarchar || !Schema::hasColumn('employees', 'school_id')) {
            Schema::table('employees', function (Blueprint $table) {
                if (Schema::hasColumn('employees', 'school_id')) {
                    $table->dropColumn('school_id');
                }
            });

            Schema::table('employees', function (Blueprint $table) {
                $table->unsignedBigInteger('school_id')->nullable()->after('office_id');
                
                $table->foreign('school_id')
                    ->references('id')
                    ->on('schools')
                    ->onDelete('restrict');
            });
        }

        // Re-add the XOR CHECK constraint on employees table
        if (DB::connection()->getDriverName() === 'mysql') {
            $constraintExists = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
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

        // 3. Alter 'asset_assignments' table to add tracking columns
        if (!Schema::hasColumn('asset_assignments', 'origin_system_type')) {
            Schema::table('asset_assignments', function (Blueprint $table) {
                $table->enum('origin_system_type', ['main', 'school'])->default('main')->after('serial_number');
                $table->unsignedBigInteger('registered_by_school_id')->nullable()->after('origin_system_type');

                $table->foreign('registered_by_school_id')
                    ->references('id')
                    ->on('schools')
                    ->onDelete('restrict');
            });
        }

        // 4. Alter 'building_records' table to add tracking columns
        if (!Schema::hasColumn('building_records', 'origin_system_type')) {
            Schema::table('building_records', function (Blueprint $table) {
                $table->enum('origin_system_type', ['main', 'school'])->default('main')->after('appraisal_date');
                $table->unsignedBigInteger('registered_by_school_id')->nullable()->after('origin_system_type');

                $table->foreign('registered_by_school_id')
                    ->references('id')
                    ->on('schools')
                    ->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 4. Rollback 'building_records' changes
        if (Schema::hasColumn('building_records', 'origin_system_type')) {
            Schema::table('building_records', function (Blueprint $table) {
                $table->dropForeign(['registered_by_school_id']);
                $table->dropColumn(['origin_system_type', 'registered_by_school_id']);
            });
        }

        // 3. Rollback 'asset_assignments' changes
        if (Schema::hasColumn('asset_assignments', 'origin_system_type')) {
            Schema::table('asset_assignments', function (Blueprint $table) {
                $table->dropForeign(['registered_by_school_id']);
                $table->dropColumn(['origin_system_type', 'registered_by_school_id']);
            });
        }

        // 2. Rollback 'employees' changes
        if (DB::connection()->getDriverName() === 'mysql') {
            $constraintExists = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'employees'
                  AND CONSTRAINT_NAME = 'chk_employee_location'
            ");
            if (!empty($constraintExists)) {
                DB::statement("ALTER TABLE employees DROP CONSTRAINT chk_employee_location");
            }
        }

        if (Schema::hasColumn('employees', 'school_id')) {
            $columns = Schema::getColumns('employees');
            $isSchoolIdInt = false;
            foreach ($columns as $col) {
                if ($col['name'] === 'school_id' && !str_contains(strtolower($col['type']), 'varchar')) {
                    $isSchoolIdInt = true;
                    break;
                }
            }

            if ($isSchoolIdInt) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->dropForeign(['school_id']);
                    $table->dropColumn('school_id');
                });

                Schema::table('employees', function (Blueprint $table) {
                    $table->string('school_id')->nullable()->after('office_id');
                });
            }
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            $constraintExists = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
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

        // 1. Rollback 'users' changes
        if (DB::connection()->getDriverName() === 'mysql') {
            $constraintExists = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'users'
                  AND CONSTRAINT_NAME = 'chk_user_school_super_admin'
            ");
            if (!empty($constraintExists)) {
                DB::statement("ALTER TABLE users DROP CONSTRAINT chk_user_school_super_admin");
            }
        }

        if (Schema::hasColumn('users', 'system_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['school_id']);
                $table->dropColumn(['system_type', 'school_id']);
            });
        }
    }
};
