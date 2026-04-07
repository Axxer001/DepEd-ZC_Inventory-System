<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds status tracking and employment details to stakeholders (recipients).
     * This allows the system to flag when a recipient transfers or resigns,
     * triggering a resolution of their pending ownerships.
     */
    public function up(): void
    {
        Schema::table('stakeholders', function (Blueprint $table) {
            // Employment/operational status of this stakeholder
            // Active = currently in position
            // Transferred = moved to another school (items need reassignment)
            // Resigned = left DepEd entirely (items must be returned/reassigned)
            // Inactive = no longer operational but kept for audit history
            $table->string('status')->default('Active')->after('school_id');

            // Optional: track which school they transferred TO (for Transfers)
            $table->unsignedBigInteger('transferred_to_school_id')->nullable()->after('status');
            $table->foreign('transferred_to_school_id')->references('id')->on('schools')->onDelete('set null');

            // Date the status changed (transfer date, resignation date, etc.)
            $table->date('status_changed_at')->nullable()->after('transferred_to_school_id');

            // Free-text notes for the status change reason
            $table->text('status_notes')->nullable()->after('status_changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stakeholders', function (Blueprint $table) {
            $table->dropForeign(['transferred_to_school_id']);
            $table->dropColumn([
                'status',
                'transferred_to_school_id',
                'status_changed_at',
                'status_notes',
            ]);
        });
    }
};
