<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('movies') || !Schema::hasColumn('movies', 'created_by')) {
            return;
        }

        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'telegram_id')) {
            return;
        }

        DB::statement("
            UPDATE movies m
            INNER JOIN users u ON u.telegram_id = m.created_by
            SET m.created_by = u.id
            WHERE m.created_by IS NOT NULL
        ");

        DB::statement("
            UPDATE movies
            SET created_by = NULL
            WHERE created_by IS NOT NULL
              AND created_by NOT IN (SELECT id FROM users)
        ");
    }

    public function down(): void
    {
        // No rollback: mapping is lossy and should remain consistent.
    }
};
