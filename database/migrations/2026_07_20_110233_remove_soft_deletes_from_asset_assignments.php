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
        // 1. Restore any soft-deleted records before dropping the column to prevent data loss
        DB::table('asset_assignments')->whereNotNull('deleted_at')->update(['deleted_at' => null]);

        // 2. Drop the column
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
};
