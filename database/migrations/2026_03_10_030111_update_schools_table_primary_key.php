<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * No-op: This migration was a one-time fix for an old pre-existing schools table.
     * The schools table created by 2026_03_17_092831_create_schools_table already
     * has the correct structure (auto-increment id + school_id index).
     */
    public function up(): void
    {
        // No-op
    }

    public function down(): void
    {
        // No-op
    }
};
