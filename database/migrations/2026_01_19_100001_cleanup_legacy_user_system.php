<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Migration: Cleanup Legacy User System
 *
 * This migration runs AFTER the consolidation migration to:
 * 1. Remove any remaining legacy columns
 * 2. Add proper indexes for the unified system
 * 3. Ensure data integrity
 *
 * This is a separate migration to ensure the consolidation
 * completes successfully before cleanup.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->info('Starting legacy cleanup migration...');

        // Verify telegram_users table is dropped
        if (Schema::hasTable('telegram_users')) {
            throw new \RuntimeException(
                'telegram_users table still exists. Run consolidation migration first.'
            );
        }

        // Ensure all tables have proper user_id column
        $this->verifyUserIdColumns();

        // Add missing indexes for performance
        $this->addPerformanceIndexes();

        // Ensure unique constraints
        $this->ensureUniqueConstraints();

        $this->info('Legacy cleanup completed successfully!');
    }

    public function down(): void
    {
        // This is a cleanup migration, nothing to reverse
        $this->info('Cleanup migration rollback - no action needed.');
    }

    private function verifyUserIdColumns(): void
    {
        $this->info('Verifying user_id columns...');

        $tables = ['payments', 'view_logs', 'user_category_vip'];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (!Schema::hasColumn($table, 'user_id')) {
                throw new \RuntimeException(
                    "Table {$table} is missing user_id column. Migration may have failed."
                );
            }

            // Verify no orphaned records
            $orphaned = DB::table($table)
                ->whereNotIn('user_id', function ($query) {
                    $query->select('id')->from('users');
                })
                ->count();

            if ($orphaned > 0) {
                $this->info("  - Warning: {$orphaned} orphaned records in {$table}");
            }
        }

        $this->info('User ID columns verified.');
    }

    private function addPerformanceIndexes(): void
    {
        $this->info('Adding performance indexes...');

        // Users table indexes
        $this->addIndexIfNotExists('users', 'role', 'users_role_index');
        $this->addIndexIfNotExists('users', 'created_at', 'users_created_at_index');

        // Payments table indexes
        if (Schema::hasTable('payments')) {
            $this->addIndexIfNotExists('payments', 'user_id', 'payments_user_id_index');
            $this->addIndexIfNotExists('payments', 'category_id', 'payments_category_id_index');
        }

        // View logs table indexes
        if (Schema::hasTable('view_logs')) {
            $this->addIndexIfNotExists('view_logs', 'user_id', 'view_logs_user_id_index');
            $this->addIndexIfNotExists('view_logs', 'category_id', 'view_logs_category_id_index');
        }

        // User category VIP table indexes
        if (Schema::hasTable('user_category_vip')) {
            $this->addIndexIfNotExists('user_category_vip', 'user_id', 'user_category_vip_user_id_index');
        }

        // Category admins table indexes
        if (Schema::hasTable('category_admins')) {
            $this->addIndexIfNotExists('category_admins', 'user_id', 'category_admins_user_id_index');
        }

        $this->info('Performance indexes added.');
    }

    private function ensureUniqueConstraints(): void
    {
        $this->info('Ensuring unique constraints...');

        // Ensure user_category_vip has unique constraint on [user_id, category_id]
        if (Schema::hasTable('user_category_vip')) {
            $this->addUniqueIfNotExists(
                'user_category_vip',
                ['user_id', 'category_id'],
                'user_category_vip_user_category_unique'
            );
        }

        // Ensure category_admins has unique constraint on [category_id, user_id]
        if (Schema::hasTable('category_admins')) {
            $this->addUniqueIfNotExists(
                'category_admins',
                ['category_id', 'user_id'],
                'category_admins_category_user_unique'
            );
        }

        $this->info('Unique constraints ensured.');
    }

    private function addIndexIfNotExists(string $table, string $column, string $indexName): void
    {
        if (!Schema::hasColumn($table, $column)) {
            return;
        }

        $exists = DB::selectOne("
            SHOW INDEX FROM `{$table}` WHERE Key_name = ?
        ", [$indexName]);

        if ($exists) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $indexName) {
                $blueprint->index($column, $indexName);
            });
            $this->info("  - Added index {$indexName}");
        } catch (\Exception $e) {
            $this->info("  - Warning: Could not add index {$indexName}: " . $e->getMessage());
        }
    }

    private function addUniqueIfNotExists(string $table, array $columns, string $indexName): void
    {
        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return;
            }
        }

        $exists = DB::selectOne("
            SHOW INDEX FROM `{$table}` WHERE Key_name = ?
        ", [$indexName]);

        if ($exists) {
            return;
        }

        // Check for duplicates before adding unique constraint
        $columnList = implode(', ', $columns);
        $duplicates = DB::select("
            SELECT {$columnList}, COUNT(*) as cnt
            FROM `{$table}`
            GROUP BY {$columnList}
            HAVING cnt > 1
            LIMIT 5
        ");

        if (count($duplicates) > 0) {
            $this->info("  - Warning: Duplicates found in {$table}, skipping unique constraint");
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
                $blueprint->unique($columns, $indexName);
            });
            $this->info("  - Added unique constraint {$indexName}");
        } catch (\Exception $e) {
            $this->info("  - Warning: Could not add unique constraint {$indexName}: " . $e->getMessage());
        }
    }

    private function info(string $message): void
    {
        if (app()->runningInConsole()) {
            echo "[INFO] {$message}\n";
        }
        Log::info("[Migration] {$message}");
    }
};
