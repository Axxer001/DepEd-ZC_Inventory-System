<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add to asset_sources
        Schema::table('asset_sources', function (Blueprint $table) {
            $table->string('equipment')->nullable()->after('condition');
        });

        // Add to acquisition_sources
        Schema::table('acquisition_sources', function (Blueprint $table) {
            $table->string('equipment')->nullable()->after('contact_position');
        });

        // Populate existing asset_sources
        DB::table('asset_sources')->orderBy('id')->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                $eqValue = $row->asset_cost <= 49999 ? 'SEE' : 'PPE';
                DB::table('asset_sources')->where('id', $row->id)->update([
                    'equipment' => $eqValue
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_sources', function (Blueprint $table) {
            $table->dropColumn('equipment');
        });

        Schema::table('acquisition_sources', function (Blueprint $table) {
            $table->dropColumn('equipment');
        });
    }
};
