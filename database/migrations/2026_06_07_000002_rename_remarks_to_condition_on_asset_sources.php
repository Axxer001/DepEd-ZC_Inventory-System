<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('asset_sources', 'remarks')) {
            Schema::table('asset_sources', function (Blueprint $table) {
                $table->dropColumn('remarks');
            });
        }

        Schema::table('asset_sources', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_sources', 'condition')) {
                $table->enum('condition', ['Good Condition', 'Needs Repair', 'Unserviceable'])
                      ->nullable()
                      ->after('acceptance_date');
            }
            if (!Schema::hasColumn('asset_sources', 'brand')) {
                $table->string('brand')->nullable()->after('description');
            }
            if (!Schema::hasColumn('asset_sources', 'model')) {
                $table->string('model')->nullable()->after('brand');
            }
            if (!Schema::hasColumn('asset_sources', 'serial_number')) {
                $table->string('serial_number')->nullable()->after('model');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asset_sources', function (Blueprint $table) {
            if (Schema::hasColumn('asset_sources', 'condition')) {
                $table->dropColumn('condition');
            }
            if (Schema::hasColumn('asset_sources', 'brand')) {
                $table->dropColumn('brand');
            }
            if (Schema::hasColumn('asset_sources', 'model')) {
                $table->dropColumn('model');
            }
            if (Schema::hasColumn('asset_sources', 'serial_number')) {
                $table->dropColumn('serial_number');
            }
        });

        if (!Schema::hasColumn('asset_sources', 'remarks')) {
            Schema::table('asset_sources', function (Blueprint $table) {
                $table->text('remarks')->nullable()->after('acceptance_date');
            });
        }
    }
};
