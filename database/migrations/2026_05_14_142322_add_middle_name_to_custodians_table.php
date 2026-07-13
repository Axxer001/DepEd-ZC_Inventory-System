<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Retired: middle_name is now defined directly in the create_employees migration.
        // Left in place to preserve migration history where it already ran.
    }

    public function down(): void
    {
        //
    }
};
