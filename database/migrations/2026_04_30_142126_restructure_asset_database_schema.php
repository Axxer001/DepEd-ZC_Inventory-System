<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DATABASE RESTRUCTURING — Stakeholders, Asset Source & Asset Distribution
     *
     * This migration implements the full schema overhaul:
     * 1. Creates `classifications` (new top-level hierarchy)
     * 2. Adds `classification_id` to `categories`
     * 3. Drops `master_quantity` from `items`
     * 4. Creates `acquisition_sources` (replaces `stakeholders`)
     * 5. Creates `asset_sources` (replaces `sub_items`)
     * 6. Creates `asset_distributions` (replaces `ownerships`)
     * 7. Drops redundant tables: stakeholders, sub_items, ownerships, asset_transactions
     */
    public function up(): void
    {
        // =====================================================================
        // 1. CREATE `classifications` — Top-level asset hierarchy
        // =====================================================================
        Schema::create('classifications', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // =====================================================================
        // 2. MODIFY `categories` — Add classification_id FK
        // =====================================================================
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('classification_id')->nullable()->after('name');
            $table->foreign('classification_id')->references('id')->on('classifications')->onDelete('set null');
        });

        // =====================================================================
        // 3. MODIFY `items` — Drop master_quantity (now derived from asset_sources)
        // =====================================================================
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('master_quantity');
        });

        // =====================================================================
        // 4. CREATE `acquisition_sources` — Source organizations (replaces stakeholders)
        // =====================================================================
        Schema::create('acquisition_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('source_type', ['Internal', 'External']);
            $table->timestamps();
        });

        // =====================================================================
        // 5. CREATE `asset_sources` — Core asset acquisition records (replaces sub_items)
        // =====================================================================
        Schema::create('asset_sources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('acquisition_source_id');
            $table->string('mode_of_acquisition');
            $table->string('source_personnel')->nullable();
            $table->string('personnel_position')->nullable();
            $table->decimal('asset_cost', 15, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->integer('estimated_useful_life')->nullable()->comment('Estimated useful life in years (e.g., 2, 3, 5, 10)');
            $table->date('acceptance_date');
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('acquisition_source_id')->references('id')->on('acquisition_sources')->onDelete('cascade');
        });

        // =====================================================================
        // 6. CREATE `asset_distributions` — Distribution records (replaces ownerships)
        // =====================================================================
        Schema::create('asset_distributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_source_id');
            $table->string('region')->default('Region IX');
            $table->string('division')->default('Division of Zamboanga City');
            $table->string('office_school_type');
            $table->string('school_id')->nullable()->comment('School ID of holder, empty if not a school');
            $table->string('office_school_name');
            $table->string('nature_of_occupancy');
            $table->string('location')->nullable();
            $table->string('property_number')->unique();
            $table->decimal('acquisition_cost', 15, 2)->default(0);
            $table->date('acquisition_date');
            $table->timestamps();

            $table->foreign('asset_source_id')->references('id')->on('asset_sources')->onDelete('cascade');
        });

        // =====================================================================
        // 7. DROP REDUNDANT TABLES
        // =====================================================================
        // Drop in dependency order (children first)
        Schema::dropIfExists('asset_transactions');
        Schema::dropIfExists('ownerships');
        Schema::dropIfExists('sub_items');
        Schema::dropIfExists('stakeholders');
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('asset_distributions');
        Schema::dropIfExists('asset_sources');
        Schema::dropIfExists('acquisition_sources');
        Schema::dropIfExists('classifications');

        // Remove classification_id from categories
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['classification_id']);
            $table->dropColumn('classification_id');
        });

        // Restore master_quantity to items
        Schema::table('items', function (Blueprint $table) {
            $table->integer('master_quantity')->default(0)->after('category_id');
        });

        // NOTE: The dropped tables (stakeholders, sub_items, ownerships, asset_transactions)
        // are NOT recreated in down(). Their original migration files handle that.
        // A full rollback would require rolling back to those original migrations.
    }
};
