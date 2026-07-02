<?php
use Illuminate\Database\Migrations\Migration;
return new class extends Migration {
    // No-op: items table didn't exist at this point in a fresh install.
    // school_id/user_id/quantity are dropped in a later migration anyway.
    public function up(): void {}
    public function down(): void {}
};
