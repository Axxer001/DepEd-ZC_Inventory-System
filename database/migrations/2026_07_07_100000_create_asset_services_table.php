<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Asset Service table — tracks assets currently under repair at a supplier's service center.
     * Created when "Return to Supplier" is initiated for an asset with condition "Needs Repair"
     * and the supplier has a non-null service_center.
     */
    public function up(): void
    {
        Schema::create('asset_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_source_id')
                  ->constrained('asset_sources')
                  ->cascadeOnDelete();

            $table->foreignId('asset_assignment_id')
                  ->constrained('asset_assignments')
                  ->cascadeOnDelete();

            $table->foreignId('supplier_id')
                  ->constrained('suppliers')
                  ->cascadeOnDelete();

            // Saved at time of return, so we can restore the asset to its original custodian
            $table->foreignId('previous_custodian_id')
                  ->nullable()
                  ->constrained('employees')
                  ->nullOnDelete();

            $table->date('expected_return_date');

            $table->timestamps(); // created_at = date sent for repair
        });

        // Add repair tracking columns to asset_transfers for historical logging
        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->date('expected_return_date')->nullable()->after('return_date');
            $table->integer('days_difference')
                  ->nullable()
                  ->comment('PH working days: negative = returned early, positive = returned late')
                  ->after('expected_return_date');
            $table->string('repair_status')
                  ->nullable()
                  ->comment('Completed - Returned to Custodian | Completed - Returned to AMU')
                  ->after('days_difference');
        });
    }

    public function down(): void
    {
        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->dropColumn(['expected_return_date', 'days_difference', 'repair_status']);
        });

        Schema::dropIfExists('asset_services');
    }
};
