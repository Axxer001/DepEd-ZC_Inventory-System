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
        Schema::table('asset_sources', function (Blueprint $table) {
            $table->string('contact_person')->nullable()->after('procurement_mode_id');
            $table->string('contact_position')->nullable()->after('contact_person');
        });

        // Copy existing contact info from acquisition_sources into asset_sources for existing records
        $assetSources = DB::table('asset_sources')->get();
        foreach ($assetSources as $asrc) {
            $acqSource = DB::table('acquisition_sources')->where('id', $asrc->acquisition_source_id)->first();
            if ($acqSource) {
                DB::table('asset_sources')->where('id', $asrc->id)->update([
                    'contact_person' => $acqSource->contact_person,
                    'contact_position' => $acqSource->contact_position,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_sources', function (Blueprint $table) {
            $table->dropColumn(['contact_person', 'contact_position']);
        });
    }
};
