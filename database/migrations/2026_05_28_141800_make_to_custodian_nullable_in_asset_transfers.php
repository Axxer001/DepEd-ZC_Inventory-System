<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old FK by its original name (from when table was asset_transfer_history)
        DB::statement('ALTER TABLE asset_transfers DROP FOREIGN KEY asset_transfer_history_to_custodian_id_foreign');

        // Make to_custodian_id nullable (for Return transfers where there is no recipient)
        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->unsignedBigInteger('to_custodian_id')->nullable()->change();
        });

        // Re-add the FK with set null on delete
        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->foreign('to_custodian_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->dropForeign(['to_custodian_id']);
            $table->unsignedBigInteger('to_custodian_id')->nullable(false)->change();
            $table->foreign('to_custodian_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }
};
