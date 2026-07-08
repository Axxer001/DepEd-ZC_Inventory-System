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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('see_category_code')->nullable()->after('classification_id');
            $table->string('ppe_category_code')->nullable()->after('see_category_code');
            $table->string('see_short_category_code')->nullable()->after('ppe_category_code');
            $table->string('ppe_short_category_code')->nullable()->after('see_short_category_code');
            $table->dropColumn(['category_code', 'short_category_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('category_code')->nullable()->after('classification_id');
            $table->string('short_category_code')->nullable()->after('category_code');
            $table->dropColumn([
                'see_category_code',
                'ppe_category_code',
                'see_short_category_code',
                'ppe_short_category_code'
            ]);
        });
    }
};
