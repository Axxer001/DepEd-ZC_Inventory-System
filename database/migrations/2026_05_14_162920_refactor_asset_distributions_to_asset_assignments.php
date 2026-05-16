<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('asset_distributions', 'asset_assignments');

        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('office_id')->nullable()->after('custodian_id');
            $table->enum('condition', ['Serviceable', 'Minor Repair', 'Major Repair', 'Condemned'])->default('Serviceable')->after('office_id');

            $table->foreign('office_id')->references('id')->on('offices')->onDelete('set null');
            $table->index('property_number');
            $table->index('office_id');

            // Drop redundant string columns
            $table->dropColumn(['region', 'division', 'office_school_name']);
        });
    }

    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropIndex(['property_number']);
            $table->dropIndex(['office_id']);
            $table->dropColumn(['office_id', 'condition']);

            $table->string('region')->default('');
            $table->string('division')->default('');
            $table->string('office_school_name')->default('');
        });

        Schema::rename('asset_assignments', 'asset_distributions');
    }
};
