<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Retired: school_id is now defined as a proper integer FK directly in the
        // create_employees migration. There is no string school_id column to drop.
        // Left in place to preserve migration history where it already ran.
    }

    public function down(): void
    {
        //
    }
};
