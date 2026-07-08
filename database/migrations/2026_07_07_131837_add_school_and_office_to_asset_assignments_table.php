<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('employee_id')->constrained('schools')->onDelete('set null');
            $table->foreignId('office_id')->nullable()->after('school_id')->constrained('offices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropForeign(['asset_assignments_office_id_foreign']);
            $table->dropForeign(['asset_assignments_school_id_foreign']);
            $table->dropColumn(['school_id', 'office_id']);
        });
    }
};
