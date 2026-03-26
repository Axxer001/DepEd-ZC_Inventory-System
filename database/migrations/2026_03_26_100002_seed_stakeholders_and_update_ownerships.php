<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Seed a default "System Warehouse" stakeholder (the implicit distributor)
        $systemId = DB::table('stakeholders')->insertGetId([
            'parent_id' => null,
            'name' => 'System Warehouse',
            'type' => 'System',
            'school_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Migrate every existing school into stakeholders
        $schools = DB::table('schools')->get();
        $schoolToStakeholder = [];

        foreach ($schools as $school) {
            $stakeholderId = DB::table('stakeholders')->insertGetId([
                'parent_id' => null,
                'name' => $school->name,
                'type' => 'School',
                'school_id' => $school->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $schoolToStakeholder[$school->id] = $stakeholderId;
        }

        // 3. Add new columns to ownerships
        Schema::table('ownerships', function (Blueprint $table) {
            $table->unsignedBigInteger('distributor_id')->nullable()->after('id');
            $table->unsignedBigInteger('recipient_id')->nullable()->after('distributor_id');

            $table->foreign('distributor_id')
                  ->references('id')
                  ->on('stakeholders')
                  ->onDelete('set null');

            $table->foreign('recipient_id')
                  ->references('id')
                  ->on('stakeholders')
                  ->onDelete('set null');
        });

        // 4. Backfill existing ownerships with the mapped stakeholder IDs
        $ownerships = DB::table('ownerships')->get();
        foreach ($ownerships as $ownership) {
            $recipientId = $schoolToStakeholder[$ownership->school_id] ?? null;
            DB::table('ownerships')->where('id', $ownership->id)->update([
                'distributor_id' => $systemId,
                'recipient_id' => $recipientId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('ownerships', function (Blueprint $table) {
            $table->dropForeign(['distributor_id']);
            $table->dropForeign(['recipient_id']);
            $table->dropColumn(['distributor_id', 'recipient_id']);
        });

        // Remove seeded stakeholders
        DB::table('stakeholders')->where('type', 'School')->delete();
        DB::table('stakeholders')->where('type', 'System')->delete();
    }
};
