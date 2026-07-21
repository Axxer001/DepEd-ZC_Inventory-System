<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add is_system flag to offices table
        if (!Schema::hasColumn('offices', 'is_system')) {
            Schema::table('offices', function (Blueprint $table) {
                $table->boolean('is_system')->default(false);
            });
        }

        // Seed the canonical Property and Supply Unit (PSU) office if not already present
        $existing = DB::table('offices')
            ->where('office_id', 'PSU')
            ->where('is_system', true)
            ->first();

        if (!$existing) {
            DB::table('offices')->insert([
                'name'       => 'Property and Supply Unit',
                'office_id'  => 'PSU',
                'is_system'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Remove the seeded PSU office
        DB::table('offices')
            ->where('office_id', 'PSU')
            ->where('is_system', true)
            ->delete();

        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
