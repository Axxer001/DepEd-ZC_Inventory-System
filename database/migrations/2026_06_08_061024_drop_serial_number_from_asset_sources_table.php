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
            if (Schema::hasColumn('asset_sources', 'serial_number')) {
                $table->dropColumn('serial_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_sources', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_sources', 'serial_number')) {
                $table->string('serial_number')->nullable();
            }
        });
    }
};
