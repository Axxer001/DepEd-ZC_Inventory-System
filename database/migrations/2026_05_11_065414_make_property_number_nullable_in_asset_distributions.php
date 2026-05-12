<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Makes property_number nullable so assets can be registered without one.
     */
    public function up(): void
    {
        Schema::table('asset_distributions', function (Blueprint $table) {
            $table->string('property_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_distributions', function (Blueprint $table) {
            $table->string('property_number')->nullable(false)->change();
        });
    }
};
