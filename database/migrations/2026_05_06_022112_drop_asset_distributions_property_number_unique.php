<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('asset_distributions', function (Blueprint $table) {
            $table->dropUnique('asset_distributions_property_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('asset_distributions', function (Blueprint $table) {
            $table->unique('property_number', 'asset_distributions_property_number_unique');
        });
    }
};
