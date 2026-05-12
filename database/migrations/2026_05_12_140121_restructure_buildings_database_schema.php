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
        // Drop the old flat table
        Schema::dropIfExists('buildings');

        // 1. Taxonomy: Classifications
        Schema::create('building_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 2. Taxonomy: Types (Articles)
        Schema::create('building_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('building_classification_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('building_classification_id')->references('id')->on('building_classifications')->onDelete('cascade');
        });

        // 3. Taxonomy: Specs (Descriptions)
        Schema::create('building_specs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('building_type_id');
            $table->string('description')->nullable();
            $table->integer('storeys')->nullable();
            $table->integer('classrooms')->nullable();
            $table->timestamps();

            $table->foreign('building_type_id')->references('id')->on('building_types')->onDelete('cascade');
        });

        // 4. Transactional: Records
        Schema::create('building_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('building_spec_id')->nullable();
            
            $table->string('occupancy_nature')->nullable();
            $table->date('date_constructed')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->string('property_number')->nullable();
            
            $table->decimal('acquisition_cost', 15, 2)->nullable();
            $table->integer('estimated_useful_life')->nullable();
            $table->decimal('appraised_value', 15, 2)->nullable();
            $table->date('appraisal_date')->nullable();
            
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
            $table->foreign('building_spec_id')->references('id')->on('building_specs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_records');
        Schema::dropIfExists('building_specs');
        Schema::dropIfExists('building_types');
        Schema::dropIfExists('building_classifications');

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
            $table->integer('estimated_useful_life')->nullable();
            $table->decimal('appraised_value', 15, 2)->nullable();
            $table->date('appraisal_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
        });
    }
};
