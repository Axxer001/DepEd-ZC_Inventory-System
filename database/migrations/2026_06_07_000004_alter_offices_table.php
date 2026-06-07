<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            // Drop school FK — offices are now standalone
            try { $table->dropForeign(['school_id']); } catch (\Exception $e) {}
            $table->dropColumn(['school_id', 'room_number']);

            // Rename and add new columns
            $table->renameColumn('office_code', 'office_id');
            $table->string('type')->nullable()->after('name');
            $table->string('location')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn(['type', 'location']);
            $table->renameColumn('office_id', 'office_code');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('room_number')->nullable();
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
        });
    }
};
