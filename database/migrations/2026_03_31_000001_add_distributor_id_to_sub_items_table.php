<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_items', function (Blueprint $table) {
            $table->unsignedBigInteger('distributor_id')->nullable()->after('item_id');
            $table->foreign('distributor_id')
                  ->references('id')
                  ->on('stakeholders')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('sub_items', function (Blueprint $table) {
            $table->dropForeign(['distributor_id']);
            $table->dropColumn('distributor_id');
        });
    }
};
