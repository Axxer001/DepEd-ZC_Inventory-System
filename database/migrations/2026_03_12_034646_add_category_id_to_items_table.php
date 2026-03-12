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
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedInteger('category_id')->nullable()->after('name');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');

            // Make school_id and user_id nullable for Inventory Setup items (global items not tied to a school)
            $table->bigInteger('school_id')->unsigned()->nullable()->change();
            $table->bigInteger('user_id')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');

            $table->bigInteger('school_id')->unsigned()->nullable(false)->change();
            $table->bigInteger('user_id')->unsigned()->nullable(false)->change();
        });
    }
};
