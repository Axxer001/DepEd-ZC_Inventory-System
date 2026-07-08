<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->foreignId('from_school_id')->nullable()->after('to_office_id')->constrained('schools')->onDelete('set null');
            $table->foreignId('to_school_id')->nullable()->after('from_school_id')->constrained('schools')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->dropForeign(['asset_transfers_to_school_id_foreign']);
            $table->dropForeign(['asset_transfers_from_school_id_foreign']);
            $table->dropColumn(['from_school_id', 'to_school_id']);
        });
    }
};
