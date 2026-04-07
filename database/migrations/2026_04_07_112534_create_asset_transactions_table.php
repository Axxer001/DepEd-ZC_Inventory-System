<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the asset_transactions table — a dedicated movement ledger.
     * Every time an asset changes state (returned, condemned, transferred,
     * reassigned, or replaced), a row is created here instead of
     * mutating the ownership record directly. This preserves full audit history.
     *
     * Transaction Types:
     *   - RETURN     : Item physically returned to warehouse stock
     *   - CONDEMN    : Item declared broken / beyond repair
     *   - TRANSFER   : Item moved from one school to another
     *   - REASSIGN   : Item kept at same school but reassigned to new recipient
     *   - REPLACE    : New item sent to replace a condemned one
     */
    public function up(): void
    {
        Schema::create('asset_transactions', function (Blueprint $table) {
            $table->id();

            // The type of movement event
            $table->enum('type', ['RETURN', 'CONDEMN', 'TRANSFER', 'REASSIGN', 'REPLACE']);

            // The ownership record this transaction is acting on
            $table->unsignedBigInteger('ownership_id')->nullable();
            $table->foreign('ownership_id')->references('id')->on('ownerships')->onDelete('set null');

            // The sub-item (specific asset/batch) involved
            $table->unsignedBigInteger('sub_item_id')->nullable();
            $table->foreign('sub_item_id')->references('id')->on('sub_items')->onDelete('set null');

            // How many units are involved in this transaction
            $table->integer('quantity_affected');

            // Condition before and after this transaction
            $table->string('condition_before')->default('Serviceable');
            $table->string('condition_after')->nullable();

            // Who is the source (from) in this transaction
            $table->unsignedBigInteger('from_school_id')->nullable();
            $table->foreign('from_school_id')->references('id')->on('schools')->onDelete('set null');

            $table->unsignedBigInteger('from_recipient_id')->nullable();
            $table->foreign('from_recipient_id')->references('id')->on('stakeholders')->onDelete('set null');

            // Who is the destination (to) in this transaction
            // NULL = returned to central warehouse
            $table->unsignedBigInteger('to_school_id')->nullable();
            $table->foreign('to_school_id')->references('id')->on('schools')->onDelete('set null');

            $table->unsignedBigInteger('to_recipient_id')->nullable();
            $table->foreign('to_recipient_id')->references('id')->on('stakeholders')->onDelete('set null');

            // For REPLACE type: the new sub-item sent as replacement
            $table->unsignedBigInteger('replacement_sub_item_id')->nullable();
            $table->foreign('replacement_sub_item_id')->references('id')->on('sub_items')->onDelete('set null');

            // The admin/user who processed this transaction
            $table->string('processed_by')->nullable();

            // Free-text reason / remarks
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_transactions');
    }
};
