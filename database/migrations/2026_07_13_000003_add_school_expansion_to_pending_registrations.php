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
        if (Schema::hasTable('pending_registrations')) {
            Schema::table('pending_registrations', function (Blueprint $table) {
                if (!Schema::hasColumn('pending_registrations', 'system_type')) {
                    $table->enum('system_type', ['main', 'school'])->default('main')->after('password');
                }
                if (!Schema::hasColumn('pending_registrations', 'school_id')) {
                    $table->unsignedBigInteger('school_id')->nullable()->after('system_type');
                    
                    $table->foreign('school_id')
                        ->references('id')
                        ->on('schools')
                        ->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pending_registrations')) {
            Schema::table('pending_registrations', function (Blueprint $table) {
                $table->dropForeign(['school_id']);
                $table->dropColumn(['system_type', 'school_id']);
            });
        }
    }
};
