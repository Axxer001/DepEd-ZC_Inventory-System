<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('supplier_personnel')->nullable();
            $table->string('service_center')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('contact_email')->nullable();
            $table->timestamps();
        });

        Schema::table('asset_sources', function (Blueprint $table) {
            $table->foreignId('supplier_id')
                  ->nullable()
                  ->after('acquisition_source_id')
                  ->constrained('suppliers')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asset_sources', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });

        Schema::dropIfExists('suppliers');
    }
};
