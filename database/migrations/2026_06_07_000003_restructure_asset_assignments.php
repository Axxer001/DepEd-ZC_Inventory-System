<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            // Drop removed columns
            $table->dropColumn(['condition', 'office_school_type', 'location']);

            // Rename custodian_id to employee_id
            // (Standard approach for renaming a column with FK)
            $table->renameColumn('custodian_id', 'employee_id');
        });

        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->renameColumn('employee_id', 'custodian_id');
            $table->enum('condition', ['Good Condition', 'Needs Repair', 'Unserviceable'])->nullable();
            $table->string('office_school_type')->nullable();
            $table->string('location')->nullable();
        });
    }
};
