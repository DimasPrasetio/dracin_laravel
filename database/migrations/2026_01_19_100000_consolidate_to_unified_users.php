<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Migration: Consolidate to Unified Users System
 *
 * This migration consolidates the dual user system into a single users table
 * following the Single Source of Truth principle.
 *
 * BEFORE (Dual System):
 * ├── users table: Web admins (email/password login)
 * └── telegram_users table: Bot users (telegram_id login)
 *     └── linked_user_id → users (problematic linking)
 *
 * AFTER (Single Source of Truth):
 * └── users table: ALL users
 *     ├── telegram_id: For Telegram authentication (nullable)
 *     ├── email/password: For web authentication (nullable)
 *     └── role: Unified role system (super_admin, admin, moderator, user)
 *
 * Migration Steps:
 * 1. Add telegram fields to users table
 * 2. Migrate telegram_users data to users table
 * 3. Update all foreign keys to reference users table
 * 4. Drop telegram_users table
 * 5. Clean up legacy columns
 */
return new class extends Migration
{
    /**
     * Tables that need FK migration from telegram_user_id to user_id
     */
    private const TABLES_WITH_TELEGRAM_USER_FK = [
        'payments',
        'view_logs',
        'user_category_vip',
        'category_admins',
    ];

    public function up(): void
    {
        $this->info('Starting user consolidation migration...');

        DB::beginTransaction();

        try {
            // Step 1: Prepare users table with telegram fields
            $this->prepareUsersTable();

            // Step 2: Create temporary mapping table
            $this->createMappingTable();

            // Step 3: Migrate telegram_users to users
            $this->migrateTelegramUsers();

            // Step 4: Update foreign keys in related tables
            $this->updateForeignKeys();

            // Step 5: Update bot_states table (uses telegram's actual ID)
            $this->updateBotStatesTable();

            // Step 6: Drop telegram_users table
            $this->dropTelegramUsersTable();

            // Step 7: Clean up
            $this->cleanup();

            DB::commit();

            $this->info('User consolidation migration completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User consolidation migration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function down(): void
    {
        // This migration is intentionally not fully reversible
        // as it involves complex data transformation.
        // A fresh migration is recommended if rollback is needed.

        $this->info('Rolling back user consolidation...');

        // Recreate telegram_users table structure
        if (!Schema::hasTable('telegram_users')) {
            Schema::create('telegram_users', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('telegram_user_id')->unique();
                $table->string('username')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('role', 50)->default('user');
                $table->foreignId('linked_user_id')->nullable();
                $table->dateTime('vip_until')->nullable();
                $table->timestamps();

                $table->index('telegram_user_id');
                $table->index('vip_until');
                $table->index('role');
            });
        }

        // Remove telegram fields from users (if they exist)
        Schema::table('users', function (Blueprint $table) {
            $columns = ['telegram_id', 'first_name', 'last_name'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        $this->info('Rollback completed. Manual data restoration may be required.');
    }

    // =========================================================================
    // STEP 1: Prepare Users Table
    // =========================================================================

    private function prepareUsersTable(): void
    {
        $this->info('Step 1: Preparing users table...');

        Schema::table('users', function (Blueprint $table) {
            // Add telegram_id if not exists
            if (!Schema::hasColumn('users', 'telegram_id')) {
                $table->bigInteger('telegram_id')->nullable()->unique()->after('id');
            }

            // Add first_name if not exists
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }

            // Add last_name if not exists
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
        });

        // Make email and password nullable for telegram-only users
        $this->makeColumnNullable('users', 'email');
        $this->makeColumnNullable('users', 'password');
        $this->makeColumnNullable('users', 'username');

        // Add index for telegram_id lookups
        $this->addIndexIfNotExists('users', 'telegram_id', 'users_telegram_id_index');

        $this->info('Step 1: Users table prepared.');
    }

    // =========================================================================
    // STEP 2: Create Mapping Table
    // =========================================================================

    private function createMappingTable(): void
    {
        $this->info('Step 2: Creating mapping table...');

        Schema::dropIfExists('_user_migration_map');

        Schema::create('_user_migration_map', function (Blueprint $table) {
            $table->unsignedBigInteger('old_telegram_user_id')->primary();
            $table->unsignedBigInteger('new_user_id');
            $table->index('new_user_id');
        });

        $this->info('Step 2: Mapping table created.');
    }

    // =========================================================================
    // STEP 3: Migrate Telegram Users
    // =========================================================================

    private function migrateTelegramUsers(): void
    {
        $this->info('Step 3: Migrating telegram users...');

        if (!Schema::hasTable('telegram_users')) {
            $this->info('Step 3: No telegram_users table found. Skipping.');
            return;
        }

        $telegramUsers = DB::table('telegram_users')->get();
        $migratedCount = 0;
        $linkedCount = 0;
        $newCount = 0;

        foreach ($telegramUsers as $tgUser) {
            $newUserId = $this->migrateOneTelegramUser($tgUser);

            if ($tgUser->linked_user_id) {
                $linkedCount++;
            } else {
                $newCount++;
            }

            // Store mapping
            DB::table('_user_migration_map')->insert([
                'old_telegram_user_id' => $tgUser->id,
                'new_user_id' => $newUserId,
            ]);

            $migratedCount++;
        }

        $this->info("Step 3: Migrated {$migratedCount} users ({$linkedCount} linked, {$newCount} new).");
    }

    private function migrateOneTelegramUser(object $tgUser): int
    {
        // Case 1: Telegram user is linked to existing web user
        if ($tgUser->linked_user_id) {
            DB::table('users')
                ->where('id', $tgUser->linked_user_id)
                ->update([
                    'telegram_id' => $tgUser->telegram_user_id,
                    'first_name' => $tgUser->first_name ?? DB::raw('first_name'),
                    'last_name' => $tgUser->last_name ?? DB::raw('last_name'),
                    'updated_at' => now(),
                ]);

            return $tgUser->linked_user_id;
        }

        // Case 2: Check if user with this telegram_id already exists
        $existingUser = DB::table('users')
            ->where('telegram_id', $tgUser->telegram_user_id)
            ->first();

        if ($existingUser) {
            // Update existing user
            DB::table('users')
                ->where('id', $existingUser->id)
                ->update([
                    'first_name' => $tgUser->first_name ?? $existingUser->first_name,
                    'last_name' => $tgUser->last_name ?? $existingUser->last_name,
                    'updated_at' => now(),
                ]);

            return $existingUser->id;
        }

        // Case 3: Create new user from telegram user
        $username = $this->resolveUniqueUsername($tgUser->username, $tgUser->telegram_user_id);
        $displayName = $this->buildDisplayName($tgUser);

        return DB::table('users')->insertGetId([
            'telegram_id' => $tgUser->telegram_user_id,
            'username' => $username,
            'name' => $displayName,
            'first_name' => $tgUser->first_name,
            'last_name' => $tgUser->last_name,
            'email' => null,
            'password' => null,
            'role' => $tgUser->role ?? 'user',
            'created_at' => $tgUser->created_at ?? now(),
            'updated_at' => now(),
        ]);
    }

    private function resolveUniqueUsername(?string $username, int $telegramId): ?string
    {
        if (empty($username)) {
            return null;
        }

        $username = trim($username);

        // Check if username exists
        $exists = DB::table('users')->where('username', $username)->exists();

        if (!$exists) {
            return $username;
        }

        // Append telegram ID to make unique
        $candidate = substr($username . '_' . $telegramId, 0, 191);
        $exists = DB::table('users')->where('username', $candidate)->exists();

        return $exists ? null : $candidate;
    }

    private function buildDisplayName(object $tgUser): string
    {
        $name = trim(($tgUser->first_name ?? '') . ' ' . ($tgUser->last_name ?? ''));

        if (!empty($name)) {
            return $name;
        }

        if (!empty($tgUser->username)) {
            return $tgUser->username;
        }

        return 'User ' . $tgUser->telegram_user_id;
    }

    // =========================================================================
    // STEP 4: Update Foreign Keys
    // =========================================================================

    private function updateForeignKeys(): void
    {
        $this->info('Step 4: Updating foreign keys...');

        foreach (self::TABLES_WITH_TELEGRAM_USER_FK as $table) {
            $this->updateTableForeignKey($table);
        }

        $this->info('Step 4: Foreign keys updated.');
    }

    private function updateTableForeignKey(string $table): void
    {
        if (!Schema::hasTable($table)) {
            $this->info("  - Skipping {$table} (table not found)");
            return;
        }

        if (!Schema::hasColumn($table, 'telegram_user_id')) {
            $this->info("  - Skipping {$table} (column not found)");
            return;
        }

        $this->info("  - Updating {$table}...");

        Schema::disableForeignKeyConstraints();

        try {
            // Drop existing foreign key
            $this->dropForeignKeyIfExists($table, 'telegram_user_id');

            // Update values using mapping table
            DB::statement("
                UPDATE `{$table}` t
                INNER JOIN `_user_migration_map` m ON t.telegram_user_id = m.old_telegram_user_id
                SET t.telegram_user_id = m.new_user_id
            ");

            // Handle category_admins specially (has both user_id and telegram_user_id)
            if ($table === 'category_admins') {
                $this->handleCategoryAdminsTable();
            } else {
                // Rename column to user_id
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->renameColumn('telegram_user_id', 'user_id');
                });

                // Add new foreign key
                $this->addForeignKeyIfNotExists($table, 'user_id', 'users', 'id');
            }

        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private function handleCategoryAdminsTable(): void
    {
        // For category_admins, merge telegram_user_id into user_id
        // where user_id is null
        DB::table('category_admins')
            ->whereNull('user_id')
            ->whereNotNull('telegram_user_id')
            ->update([
                'user_id' => DB::raw('telegram_user_id'),
            ]);

        // Drop unique constraint that includes telegram_user_id if exists
        $this->dropIndexIfExists('category_admins', 'unique_category_telegram_admin');

        // Drop telegram_user_id column
        Schema::table('category_admins', function (Blueprint $table) {
            $table->dropColumn('telegram_user_id');
        });

        // Ensure user_id has foreign key
        $this->addForeignKeyIfNotExists('category_admins', 'user_id', 'users', 'id');
    }

    // =========================================================================
    // STEP 5: Update Bot States Table
    // =========================================================================

    private function updateBotStatesTable(): void
    {
        $this->info('Step 5: Updating bot_states table...');

        if (!Schema::hasTable('bot_states')) {
            $this->info('Step 5: No bot_states table found. Skipping.');
            return;
        }

        // bot_states uses Telegram's actual ID (not our internal ID)
        // Just rename the column for clarity if needed
        if (Schema::hasColumn('bot_states', 'telegram_user_id')) {
            Schema::table('bot_states', function (Blueprint $table) {
                $table->renameColumn('telegram_user_id', 'telegram_id');
            });
            $this->info('Step 5: Renamed telegram_user_id to telegram_id.');
        } else {
            $this->info('Step 5: Already using telegram_id. Skipping.');
        }
    }

    // =========================================================================
    // STEP 6: Drop Telegram Users Table
    // =========================================================================

    private function dropTelegramUsersTable(): void
    {
        $this->info('Step 6: Dropping telegram_users table...');

        Schema::dropIfExists('telegram_users');

        $this->info('Step 6: telegram_users table dropped.');
    }

    // =========================================================================
    // STEP 7: Cleanup
    // =========================================================================

    private function cleanup(): void
    {
        $this->info('Step 7: Cleaning up...');

        // Drop mapping table
        Schema::dropIfExists('_user_migration_map');

        $this->info('Step 7: Cleanup completed.');
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    private function makeColumnNullable(string $table, string $column): void
    {
        if (!Schema::hasColumn($table, $column)) {
            return;
        }

        $columnInfo = DB::selectOne("
            SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ", [$table, $column]);

        if (!$columnInfo) {
            return;
        }

        if (strtoupper($columnInfo->IS_NULLABLE) === 'YES') {
            return; // Already nullable
        }

        $columnType = $columnInfo->COLUMN_TYPE;
        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` {$columnType} NULL");
    }

    private function addIndexIfNotExists(string $table, string $column, string $indexName): void
    {
        $exists = DB::selectOne("
            SHOW INDEX FROM `{$table}` WHERE Key_name = ?
        ", [$indexName]);

        if ($exists) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $indexName) {
            $blueprint->index($column, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        $exists = DB::selectOne("
            SHOW INDEX FROM `{$table}` WHERE Key_name = ?
        ", [$indexName]);

        if (!$exists) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropIndex($indexName);
        });
    }

    private function dropForeignKeyIfExists(string $table, string $column): void
    {
        $constraints = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table, $column]);

        foreach ($constraints as $constraint) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
        }
    }

    private function addForeignKeyIfNotExists(string $table, string $column, string $refTable, string $refColumn): void
    {
        if (!Schema::hasColumn($table, $column)) {
            return;
        }

        $exists = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME = ?
        ", [$table, $column, $refTable]);

        if ($exists) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $refTable, $refColumn) {
            $blueprint->foreign($column)
                ->references($refColumn)
                ->on($refTable)
                ->cascadeOnDelete();
        });
    }

    private function info(string $message): void
    {
        if (app()->runningInConsole()) {
            echo "[INFO] {$message}\n";
        }
        Log::info("[Migration] {$message}");
    }
};
