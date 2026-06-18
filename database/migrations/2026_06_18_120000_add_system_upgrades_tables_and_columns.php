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
        // 1. Update employees status enum to include Resigned and Suspended
        try {
            DB::statement("ALTER TABLE employees MODIFY COLUMN status ENUM('Active','Inactive','On Leave','Retired','Resigned','Suspended') NOT NULL DEFAULT 'Active'");
        } catch (\Exception $e) {
            // Log/ignore if table/column does not exist
        }

        // 2. Add role column to users table
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('user')->after('approved'); // super_admin, admin, user
            });
        }

        // 3. Add password column to pending_registrations table
        if (Schema::hasTable('pending_registrations') && !Schema::hasColumn('pending_registrations', 'password')) {
            Schema::table('pending_registrations', function (Blueprint $table) {
                $table->string('password')->nullable()->after('email');
            });
        }

        // 4. Create standard polymorphic notifications table
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // 5. Add indexes for performance & scalability if not already present
        try {
            Schema::table('asset_assignments', function (Blueprint $table) {
                $table->index(['employee_id', 'asset_source_id']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('asset_assignments', function (Blueprint $table) {
                $table->index('property_number');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('employees', function (Blueprint $table) {
                $table->index(['status', 'school_id']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('employees', function (Blueprint $table) {
                $table->index(['status', 'office_id']);
            });
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop notifications table
        Schema::dropIfExists('notifications');

        // Drop password column from pending_registrations
        if (Schema::hasTable('pending_registrations') && Schema::hasColumn('pending_registrations', 'password')) {
            Schema::table('pending_registrations', function (Blueprint $table) {
                $table->dropColumn('password');
            });
        }

        // Drop role column from users
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }

        // Revert status column
        try {
            DB::statement("ALTER TABLE employees MODIFY COLUMN status ENUM('Active','Inactive','On Leave','Retired') NOT NULL DEFAULT 'Active'");
        } catch (\Exception $e) {}
    }
};
