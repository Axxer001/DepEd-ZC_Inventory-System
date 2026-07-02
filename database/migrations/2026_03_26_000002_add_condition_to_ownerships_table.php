<?php
use Illuminate\Database\Migrations\Migration;
return new class extends Migration {
    // No-op: ownerships table never exists in a fresh install.
    // Dropped in the April 30 restructure.
    public function up(): void {}
    public function down(): void {}
};
