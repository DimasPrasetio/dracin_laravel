<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Log;

class TelegramAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder will set default bot admins and moderators
     */
    public function run(): void
    {
        // Default bot admins and moderators
        $defaultBotAdmins = [
            [
                'telegram_user_id' => '1597383375',
                'first_name' => 'Dimas',
                'last_name' => 'Prasetio',
                'username' => null,
                'role' => TelegramUser::ROLE_ADMIN,
            ],
            [
                'telegram_user_id' => '8190923723',
                'first_name' => 'Mahardika',
                'last_name' => null,
                'username' => null,
                'role' => TelegramUser::ROLE_MODERATOR,
            ],
        ];

        // Process each default admin/moderator
        foreach ($defaultBotAdmins as $adminData) {
            $telegramUser = TelegramUser::where('telegram_user_id', $adminData['telegram_user_id'])->first();

            if (!$telegramUser) {
                // Create new telegram user
                $telegramUser = TelegramUser::create($adminData);

                $this->command->info("Created new {$adminData['role']}: {$adminData['first_name']} (ID: {$adminData['telegram_user_id']})");
                Log::info('TelegramAdminSeeder: Created new bot admin/moderator', [
                    'telegram_user_id' => $adminData['telegram_user_id'],
                    'role' => $adminData['role'],
                    'name' => trim($adminData['first_name'] . ' ' . ($adminData['last_name'] ?? '')),
                ]);
            } else {
                // Update existing user role if different
                if ($telegramUser->role !== $adminData['role']) {
                    $oldRole = $telegramUser->role;
                    $telegramUser->update([
                        'role' => $adminData['role'],
                        'first_name' => $adminData['first_name'],
                        'last_name' => $adminData['last_name'],
                    ]);

                    $this->command->info("Updated {$adminData['first_name']} (ID: {$adminData['telegram_user_id']}) from {$oldRole} to {$adminData['role']}");
                    Log::info('TelegramAdminSeeder: Updated existing user role', [
                        'telegram_user_id' => $adminData['telegram_user_id'],
                        'old_role' => $oldRole,
                        'new_role' => $adminData['role'],
                    ]);
                } else {
                    $this->command->info("{$adminData['first_name']} (ID: {$adminData['telegram_user_id']}) is already a {$adminData['role']}");
                }
            }
        }

        // Also process TELE_ADMIN_ID from environment if set (for backward compatibility)
        $adminTelegramId = env('TELE_ADMIN_ID');
        if (!empty($adminTelegramId) && !in_array($adminTelegramId, ['1597383375', '8190923723'])) {
            $telegramUser = TelegramUser::where('telegram_user_id', $adminTelegramId)->first();

            if (!$telegramUser) {
                TelegramUser::create([
                    'telegram_user_id' => $adminTelegramId,
                    'username' => null,
                    'first_name' => 'Admin',
                    'last_name' => null,
                    'role' => TelegramUser::ROLE_ADMIN,
                ]);

                $this->command->info("Created admin from TELE_ADMIN_ID: {$adminTelegramId}");
            } elseif ($telegramUser->role !== TelegramUser::ROLE_ADMIN) {
                $telegramUser->update(['role' => TelegramUser::ROLE_ADMIN]);
                $this->command->info("Updated user {$adminTelegramId} to admin (from TELE_ADMIN_ID)");
            }
        }

        $this->command->info('Telegram admin seeder completed successfully!');
    }
}
