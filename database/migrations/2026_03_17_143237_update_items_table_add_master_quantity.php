<?php
use Illuminate\Database\Migrations\Migration;
return new class extends Migration {
    // No-op: items/master_quantity column never exists in a fresh install.
    // The master_quantity column is dropped in the April 30 restructure anyway.
    public function up(): void {}
    public function down(): void {}
};
