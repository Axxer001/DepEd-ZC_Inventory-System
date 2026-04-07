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
        Schema::table('sub_items', function (Blueprint $table) {
            $table->string('property_number')->nullable()->unique();
            $table->string('serial_number')->nullable()->unique();
            $table->string('qr_hash')->nullable()->unique();
            $table->boolean('is_serialized')->default(false);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->date('date_acquired')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_items', function (Blueprint $table) {
            $table->dropColumn([
                'property_number',
                'serial_number',
                'qr_hash',
                'is_serialized',
                'unit_price',
                'date_acquired',
            ]);
        });
    }
};
