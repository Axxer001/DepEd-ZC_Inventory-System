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
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('region')->default('REGION IX');
            $table->string('division')->default('Division of Zamboanga City');
            $table->string('office_type')->nullable();
            $table->string('school_identifier')->nullable();
            $table->string('office_name')->nullable();
            $table->string('address')->nullable();
            $table->integer('storeys')->nullable();
            $table->integer('classrooms')->nullable();
            $table->string('article')->nullable();
            $table->string('description')->nullable();
            $table->string('classification')->nullable();
            $table->string('occupancy_nature')->nullable();
            $table->string('location')->nullable();
            $table->date('date_constructed')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->string('property_number')->nullable();
            $table->decimal('acquisition_cost', 15, 2)->nullable();
            $table->decimal('appraised_value', 15, 2)->nullable();
            $table->date('appraisal_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
