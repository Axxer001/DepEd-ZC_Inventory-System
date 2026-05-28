<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->date('return_date')->nullable()->after('transfer_date');
        });

        // Revert ENUM to VARCHAR to allow 'Permanent Reassignment' and 'Temporary Borrow'
        DB::statement("ALTER TABLE asset_transfers MODIFY COLUMN transfer_type VARCHAR(255) DEFAULT 'Permanent Reassignment'");
    }

    public function down(): void
    {
        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->dropColumn('return_date');
        });
        
        DB::statement("ALTER TABLE asset_transfers MODIFY COLUMN transfer_type ENUM('Permanent', 'Loan', 'Repair', 'Return') DEFAULT 'Permanent'");
    }
};
