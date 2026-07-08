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
        // Nullify foreign keys in asset_sources to prevent FK constraint failures
        Illuminate\Support\Facades\DB::table('asset_sources')->update(['procurement_mode_id' => null]);

        // Delete all old procurement modes
        Illuminate\Support\Facades\DB::table('procurement_modes')->delete();

        // Seed specific procurement modes
        $modes = ['PROCUREMENT', 'TRANSFER', 'DONATION'];
        foreach ($modes as $mode) {
            Illuminate\Support\Facades\DB::table('procurement_modes')->insert([
                'name'       => $mode,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Illuminate\Support\Facades\DB::table('asset_sources')->update(['procurement_mode_id' => null]);
        Illuminate\Support\Facades\DB::table('procurement_modes')->delete();
    }
};
