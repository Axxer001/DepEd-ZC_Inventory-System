<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Retired: this migration handled upgrading a legacy `custodians` table
        // with string school_id data on existing production databases.
        // Fresh installs no longer need this — the correct schema is now defined
        // directly in the original employees create-table migration.
        // Left in place only to preserve migration history where it already ran.
    }

    public function down(): void
    {
        //
    }
};
