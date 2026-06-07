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
            if (Schema::hasColumn('asset_sources', 'brand')) {
                $table->dropColumn('brand');
            }
            if (Schema::hasColumn('asset_sources', 'model')) {
                $table->dropColumn('model');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_sources', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_sources', 'brand')) {
                $table->string('brand')->nullable();
            }
            if (!Schema::hasColumn('asset_sources', 'model')) {
                $table->string('model')->nullable();
            }
        });
    }
};
