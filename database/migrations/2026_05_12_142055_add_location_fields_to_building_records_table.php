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
        Schema::table('building_records', function (Blueprint $table) {
            $table->string('region')->default('REGION IX')->after('school_id');
            $table->string('division')->default('Division of Zamboanga City')->after('region');
            $table->string('office_type')->nullable()->after('division');
            $table->string('address')->nullable()->after('office_type');
            $table->string('location')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_records', function (Blueprint $table) {
            $table->dropColumn(['region', 'division', 'office_type', 'address', 'location']);
        });
    }
};
