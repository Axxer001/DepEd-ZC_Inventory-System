<?php
use Illuminate\Database\Migrations\Migration;
return new class extends Migration {
    // No-op: sub_items table never exists in a fresh install.
    // This table is dropped entirely in the April 30 restructure.
    public function up(): void {}
    public function down(): void {}
};
