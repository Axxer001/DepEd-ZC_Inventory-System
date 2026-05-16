<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('asset_transfer_history', 'asset_transfers');

        Schema::table('asset_transfers', function (Blueprint $table) {
            // Rename the FK column
            $table->renameColumn('asset_distribution_id', 'asset_assignment_id');
        });

        Schema::table('asset_transfers', function (Blueprint $table) {
            // Add office tracking columns
            $table->unsignedBigInteger('from_office_id')->nullable()->after('asset_assignment_id');
            $table->unsignedBigInteger('to_office_id')->nullable()->after('from_office_id');

            $table->foreign('from_office_id')->references('id')->on('offices')->onDelete('set null');
            $table->foreign('to_office_id')->references('id')->on('offices')->onDelete('set null');
            $table->index('asset_assignment_id');
        });

        // Convert transfer_type to ENUM via raw SQL (MySQL doesn't support ALTER ENUM natively in Blueprint)
        DB::statement("ALTER TABLE asset_transfers MODIFY COLUMN transfer_type ENUM('Permanent', 'Loan', 'Repair', 'Return') DEFAULT 'Permanent'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE asset_transfers MODIFY COLUMN transfer_type VARCHAR(255) DEFAULT NULL");

        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->dropForeign(['from_office_id']);
            $table->dropForeign(['to_office_id']);
            $table->dropIndex(['asset_assignment_id']);
            $table->dropColumn(['from_office_id', 'to_office_id']);
        });

        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->renameColumn('asset_assignment_id', 'asset_distribution_id');
        });

        Schema::rename('asset_transfers', 'asset_transfer_history');
    }
};
