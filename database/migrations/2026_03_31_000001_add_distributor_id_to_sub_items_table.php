<?php
use Illuminate\Database\Migrations\Migration;
return new class extends Migration {
    // No-op: sub_items never exists in a fresh install. Dropped in April 30 restructure.
    public function up(): void {}
    public function down(): void {}
};
