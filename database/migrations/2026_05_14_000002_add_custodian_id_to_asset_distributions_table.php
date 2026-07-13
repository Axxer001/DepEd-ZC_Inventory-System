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
        Schema::table('asset_distributions', function (Blueprint $table) {
            $table->unsignedBigInteger('custodian_id')->nullable()->after('asset_source_id');
            $table->foreign('custodian_id')->references('id')->on('employees')->onDelete('set null');
            
            // Add index for performance on large datasets (10k+ assets)
            $table->index('custodian_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_distributions', function (Blueprint $table) {
            $table->dropForeign(['custodian_id']);
            $table->dropColumn('custodian_id');
        });
    }
};
