<?php
use Illuminate\Database\Migrations\Migration;
return new class extends Migration {
    // No-op: sub_items/ownerships tables never exist in a fresh install.
    // Both are dropped in the April 30 restructure.
    public function up(): void {}
    public function down(): void {}
};
