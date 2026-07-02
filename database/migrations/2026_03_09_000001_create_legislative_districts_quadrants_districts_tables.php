<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the legislative_districts, quadrants, and districts tables.
     * These organizational hierarchy tables were previously only available
     * via a cloud DB dump and are now part of the migration chain.
     */
    public function up(): void
    {
        if (!Schema::hasTable('legislative_districts')) {
            Schema::create('legislative_districts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('quadrants')) {
            Schema::create('quadrants', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('legislative_district_id')->nullable();
                $table->timestamps();

                $table->foreign('legislative_district_id')
                      ->references('id')->on('legislative_districts')
                      ->onDelete('set null');
            });
        }

        if (!Schema::hasTable('districts')) {
            Schema::create('districts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('quadrant_id')->nullable();
                $table->timestamps();

                $table->foreign('quadrant_id')
                      ->references('id')->on('quadrants')
                      ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
        Schema::dropIfExists('quadrants');
        Schema::dropIfExists('legislative_districts');
    }
};
