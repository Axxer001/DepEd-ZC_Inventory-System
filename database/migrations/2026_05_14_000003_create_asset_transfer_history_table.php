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
        Schema::create('asset_transfer_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_distribution_id');
            $table->unsignedBigInteger('from_custodian_id')->nullable();
            $table->unsignedBigInteger('to_custodian_id');
            $table->date('transfer_date');
            $table->string('transfer_type')->default('Transfer'); // Assignment, Transfer, Return
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('authorized_by')->nullable(); // FK to users
            $table->timestamps();

            $table->foreign('asset_distribution_id')->references('id')->on('asset_distributions')->onDelete('cascade');
            $table->foreign('from_custodian_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('to_custodian_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('authorized_by')->references('id')->on('users')->onDelete('set null');

            // Indices for high performance tracking
            $table->index('asset_distribution_id');
            $table->index('from_custodian_id');
            $table->index('to_custodian_id');
            $table->index('transfer_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_transfer_history');
    }
};
