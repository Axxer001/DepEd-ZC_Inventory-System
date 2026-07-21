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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed initial global document numbers
        $now = now();
        DB::table('system_settings')->insert([
            ['key' => 'ics_global_number', 'value' => 'ICS-2026-03-0085', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'par_global_number', 'value' => 'PAR-2026-03-0085', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'ptr_global_number', 'value' => 'PTR-2026-03-0085', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'itr_global_number', 'value' => 'ITR-2026-03-0085', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'rrsp_global_number', 'value' => 'RRSP-2026-03-0085', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
