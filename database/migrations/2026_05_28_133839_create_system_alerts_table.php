<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('priority')->default('Medium'); // High / Medium / Low
            $table->string('updated_by')->nullable();      // name of editor
            $table->timestamps();
        });

        // Seed one default row so the table is never empty
        DB::table('system_alerts')->insert([
            'title'      => 'Quarterly Inventory Audit Coming Up',
            'body'       => 'All institution heads are required to verify their current asset counts by the end of the month.',
            'priority'   => 'High',
            'updated_by' => 'System',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_alerts');
    }
};
