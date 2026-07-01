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
        Schema::table('asset_sources', function (Blueprint $table) {
            $table->integer('warranty')->nullable()->comment('Warranty in months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_sources', function (Blueprint $table) {
            $table->dropColumn('warranty');
        });
    }
};
