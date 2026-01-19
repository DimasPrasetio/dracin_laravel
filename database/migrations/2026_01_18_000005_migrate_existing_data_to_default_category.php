<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * SAFE DATA MIGRATION:
     * 1. Creates a "Default" category using existing bot configuration from .env
     * 2. Assigns all existing movies to the default category
     * 3. Assigns all existing payments to the default category
     * 4. Assigns all existing view_logs to the default category
     * 5. Migrates existing VIP data from telegram_users.vip_until to user_category_vip
     * 6. Assigns existing admins to the default category
     *
     * NO DATA IS DELETED - only copied/assigned to new structure
     */
    public function up(): void
    {
        // Step 1: Create default category using existing bot configuration
        $defaultSlug = config('vip.default_category_slug', 'dracin');
        $defaultName = config('vip.default_category_name', 'Dracin');

        $defaultCategoryId = DB::table('categories')->insertGetId([
            'name' => $defaultName,
            'slug' => $defaultSlug,
            'description' => 'Kategori utama untuk film drama China',
            'bot_token' => config('telegram.bots.default.token', env('TELE_BOT_TOKEN', 'PLEASE_UPDATE_BOT_TOKEN')),
            'bot_username' => config('telegram.bots.default.username', env('TELE_BOT_USERNAME', '@default_bot')),
            'channel_id' => config('telegram.bots.default.channel_id', env('TELE_CHANNEL_ID')),
            'webhook_secret' => Str::random(32),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Step 2: Assign all existing movies to default category
        DB::table('movies')
            ->whereNull('category_id')
            ->update(['category_id' => $defaultCategoryId]);

        // Step 3: Assign all existing payments to default category
        DB::table('payments')
            ->whereNull('category_id')
            ->update(['category_id' => $defaultCategoryId]);

        // Step 4: Assign all existing view_logs to default category
        if (Schema::hasTable('view_logs')) {
            DB::table('view_logs')
                ->whereNull('category_id')
                ->update(['category_id' => $defaultCategoryId]);
        }

        // Step 5: Migrate VIP data from telegram_users.vip_until to user_category_vip
        // IMPORTANT: We keep the original vip_until column intact for safety
        $usersWithVip = DB::table('telegram_users')
            ->whereNotNull('vip_until')
            ->where('vip_until', '>', now())
            ->get(['id', 'vip_until']);

        foreach ($usersWithVip as $user) {
            // Check if already exists (prevent duplicates on re-run)
            $exists = DB::table('user_category_vip')
                ->where('telegram_user_id', $user->id)
                ->where('category_id', $defaultCategoryId)
                ->exists();

            if (!$exists) {
                DB::table('user_category_vip')->insert([
                    'telegram_user_id' => $user->id,
                    'category_id' => $defaultCategoryId,
                    'vip_until' => $user->vip_until,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Step 6: Assign existing web admins to default category
        $webAdmins = DB::table('users')
            ->where('role', 'admin')
            ->get(['id', 'role']);

        foreach ($webAdmins as $admin) {
            $exists = DB::table('category_admins')
                ->where('user_id', $admin->id)
                ->where('category_id', $defaultCategoryId)
                ->exists();

            if (!$exists) {
                DB::table('category_admins')->insert([
                    'category_id' => $defaultCategoryId,
                    'user_id' => $admin->id,
                    'telegram_user_id' => null,
                    'role' => 'admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Step 7: Assign existing telegram admins to default category
        $telegramAdmins = DB::table('telegram_users')
            ->where('role', 'admin')
            ->get(['id', 'role']);

        foreach ($telegramAdmins as $admin) {
            $exists = DB::table('category_admins')
                ->where('telegram_user_id', $admin->id)
                ->where('category_id', $defaultCategoryId)
                ->exists();

            if (!$exists) {
                DB::table('category_admins')->insert([
                    'category_id' => $defaultCategoryId,
                    'user_id' => null,
                    'telegram_user_id' => $admin->id,
                    'role' => 'admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Step 8: Assign existing telegram moderators to default category
        $telegramModerators = DB::table('telegram_users')
            ->where('role', 'moderator')
            ->get(['id', 'role']);

        foreach ($telegramModerators as $moderator) {
            $exists = DB::table('category_admins')
                ->where('telegram_user_id', $moderator->id)
                ->where('category_id', $defaultCategoryId)
                ->exists();

            if (!$exists) {
                DB::table('category_admins')->insert([
                    'category_id' => $defaultCategoryId,
                    'user_id' => null,
                    'telegram_user_id' => $moderator->id,
                    'role' => 'moderator',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * Note: This only removes the default category and related assignments.
     * Original data in movies, payments, view_logs remains intact (just with NULL category_id).
     * Original vip_until in telegram_users is NEVER touched.
     */
    public function down(): void
    {
        if (app()->environment('production')) {
            return;
        }

        // Get default category ID
        $defaultSlug = config('vip.default_category_slug', 'dracin');
        $defaultCategory = DB::table('categories')
            ->where('slug', $defaultSlug)
            ->first();

        if ($defaultCategory) {
            // Remove VIP subscriptions for default category
            DB::table('user_category_vip')
                ->where('category_id', $defaultCategory->id)
                ->delete();

            // Remove admin assignments for default category
            DB::table('category_admins')
                ->where('category_id', $defaultCategory->id)
                ->delete();

            // Set category_id back to NULL for all affected tables
            DB::table('movies')
                ->where('category_id', $defaultCategory->id)
                ->update(['category_id' => null]);

            DB::table('payments')
                ->where('category_id', $defaultCategory->id)
                ->update(['category_id' => null]);

            if (Schema::hasTable('view_logs')) {
                DB::table('view_logs')
                    ->where('category_id', $defaultCategory->id)
                    ->update(['category_id' => null]);
            }

            // Delete default category
            DB::table('categories')
                ->where('id', $defaultCategory->id)
                ->delete();
        }
    }
};
