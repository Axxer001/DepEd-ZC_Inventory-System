<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('name');
            $table->string('office_code')->nullable();
            $table->string('room_number')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
            $table->index('school_id');
        });

        // ── Add office_id + school_id FKs to employees (offices now exists) ───
        // NOTE: MySQL 8.4 error 3823 makes it impossible to add a CHECK constraint
        // on any column that has an ON DELETE SET NULL FK (and vice versa). Since
        // both school_id and office_id use nullOnDelete(), the chk_employee_location
        // CHECK constraint is enforced at the application layer only (not DB level).
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('office_id')->nullable()->after('school_id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
            $table->foreign('office_id')->references('id')->on('offices')->onDelete('set null');
        });
    }

    public function down(): void
    {
        try {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropForeign(['office_id']);
                $table->dropForeign(['school_id']);
                $table->dropColumn('office_id');
            });
        } catch (\Exception $e) {}

        Schema::dropIfExists('offices');
    }
};
