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
        DB::unprepared("
            CREATE TRIGGER trg_schools_insert AFTER INSERT ON schools
            FOR EACH ROW
            BEGIN
                INSERT INTO system_logs (user, activity, module, action_type, created_at, updated_at)
                VALUES (
                    IFNULL(@app_user, CURRENT_USER()),
                    CONCAT('Added new school: ', NEW.name),
                    'Schools',
                    'Create',
                    NOW(),
                    NOW()
                );
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_schools_delete AFTER DELETE ON schools
            FOR EACH ROW
            BEGIN
                INSERT INTO system_logs (user, activity, module, action_type, created_at, updated_at)
                VALUES (
                    IFNULL(@app_user, CURRENT_USER()),
                    CONCAT('Deleted school: ', OLD.name),
                    'Schools',
                    'Delete',
                    NOW(),
                    NOW()
                );
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_schools_insert;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_schools_delete;");
    }
};
