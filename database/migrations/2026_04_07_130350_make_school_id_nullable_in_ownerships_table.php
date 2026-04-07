<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration A: Make school_id nullable in ownerships.
     *
     * This is the critical fix that allows the system to record distributions
     * to any recipient type — not just school-linked stakeholders.
     * The recipient_id column is now the authoritative "who has this item" field.
     * school_id is retained as optional geographic context only.
     */
    public function up(): void
    {
        Schema::table('ownerships', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ownerships', function (Blueprint $table) {
            // Note: reverting to NOT NULL may fail if NULL records now exist
            $table->unsignedBigInteger('school_id')->nullable(false)->change();
        });
    }
};
