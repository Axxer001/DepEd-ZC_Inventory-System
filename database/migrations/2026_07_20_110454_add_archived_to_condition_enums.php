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
        DB::statement("ALTER TABLE asset_sources MODIFY COLUMN `condition` ENUM('Good Condition', 'Needs Repair', 'Unserviceable', 'Archived') NOT NULL DEFAULT 'Good Condition'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Change back to original enum
        DB::statement("ALTER TABLE asset_sources MODIFY COLUMN `condition` ENUM('Good Condition', 'Needs Repair', 'Unserviceable') NOT NULL DEFAULT 'Good Condition'");
    }
};
