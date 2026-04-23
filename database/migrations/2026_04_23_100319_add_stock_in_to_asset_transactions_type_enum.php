<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adding 'STOCK_IN' to the ENUM list for asset_transactions.type
        // Note: For MySQL/MariaDB, we use a raw statement.
        DB::statement("ALTER TABLE asset_transactions MODIFY COLUMN type ENUM('RETURN', 'CONDEMN', 'TRANSFER', 'REASSIGN', 'REPLACE', 'STOCK_IN')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE asset_transactions MODIFY COLUMN type ENUM('RETURN', 'CONDEMN', 'TRANSFER', 'REASSIGN', 'REPLACE')");
    }
};
