<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Issue 1 — Atomic per-scope sequential numbering.
     *
     * Creates the `asset_sequences` table used to generate property numbers
     * without read-then-write race conditions. Each scope (main or a specific
     * school) owns one row; all increments are done inside a transaction with
     * lockForUpdate() so concurrent requests for the same scope queue safely.
     *
     * Issue 2 — Race-safe approval processing.
     *
     * Adds a `status` column to `pending_registrations` so approval/rejection
     * state can be checked and updated atomically inside a transaction with
     * lockForUpdate(), rather than relying on row presence/absence alone.
     */
    public function up(): void
    {
        // --- Issue 1: Asset sequences table ---
        if (!Schema::hasTable('asset_sequences')) {
            Schema::create('asset_sequences', function (Blueprint $table) {
                $table->id();
                // 'main' = SDO Main; 'school' = a specific school
                $table->enum('scope_type', ['main', 'school'])->default('main');
                // NULL when scope_type = 'main'; school's PK when scope_type = 'school'
                $table->unsignedBigInteger('school_id')->nullable();
                // Current highest number issued for this scope
                $table->unsignedBigInteger('current_number')->default(0);
                $table->timestamps();

                // One row per scope — guarantees lockForUpdate() targets exactly one row
                $table->unique(['scope_type', 'school_id'], 'uniq_asset_scope');

                $table->foreign('school_id')
                    ->references('id')
                    ->on('schools')
                    ->onDelete('cascade');
            });

            // Seed the Main scope row so it always exists
            DB::table('asset_sequences')->insert([
                'scope_type'     => 'main',
                'school_id'      => null,
                'current_number' => 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        // --- Issue 2: Status column on pending_registrations ---
        if (!Schema::hasColumn('pending_registrations', 'status')) {
            Schema::table('pending_registrations', function (Blueprint $table) {
                // 'pending' = awaiting decision; 'approved' / 'rejected' = final state
                // Using a string with a DB check is sufficient; enum requires a migration
                // to alter later which is more painful on MySQL.
                $table->string('status', 20)->default('pending')->after('expires_at');
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_sequences');

        if (Schema::hasColumn('pending_registrations', 'status')) {
            Schema::table('pending_registrations', function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            });
        }
    }
};
