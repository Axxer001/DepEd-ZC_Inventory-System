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
            CREATE TRIGGER trg_users_insert AFTER INSERT ON users
            FOR EACH ROW
            BEGIN
                INSERT INTO system_logs (user, activity, module, action_type, created_at, updated_at)
                VALUES (
                    IFNULL(@app_user, CURRENT_USER()),
                    CONCAT('Created new account: ', NEW.email),
                    'Accounts',
                    'Create',
                    NOW(),
                    NOW()
                );
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_users_delete AFTER DELETE ON users
            FOR EACH ROW
            BEGIN
                INSERT INTO system_logs (user, activity, module, action_type, created_at, updated_at)
                VALUES (
                    IFNULL(@app_user, CURRENT_USER()),
                    CONCAT('Deleted account: ', OLD.email),
                    'Accounts',
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
        DB::unprepared("DROP TRIGGER IF EXISTS trg_users_insert;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_users_delete;");
    }
};
