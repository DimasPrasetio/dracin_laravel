<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class TelegramAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder will set default bot admins and moderators.
     */
    public function run(): void
    {
        $defaultBotAdmins = [
            [
                'telegram_id' => 1597383375,
                'first_name' => 'Dimas',
                'last_name' => 'Prasetio',
                'username' => null,
                'role' => User::ROLE_ADMIN,
            ],
            [
                'telegram_id' => 8190923723,
                'first_name' => 'Mahardika',
                'last_name' => null,
                'username' => null,
                'role' => User::ROLE_MODERATOR,
            ],
        ];

        foreach ($defaultBotAdmins as $adminData) {
            $name = trim(($adminData['first_name'] ?? '') . ' ' . ($adminData['last_name'] ?? ''))
                ?: ($adminData['username'] ?? 'User ' . $adminData['telegram_id']);

            $user = User::where('telegram_id', $adminData['telegram_id'])->first();

            if (!$user) {
                $user = User::create([
                    'telegram_id' => $adminData['telegram_id'],
                    'username' => $adminData['username'],
                    'first_name' => $adminData['first_name'],
                    'last_name' => $adminData['last_name'],
                    'name' => $name,
                    'role' => $adminData['role'],
                ]);

                $this->command->info("Created new {$adminData['role']}: {$name} (ID: {$adminData['telegram_id']})");
                Log::info('TelegramAdminSeeder: Created new bot admin/moderator', [
                    'telegram_id' => $adminData['telegram_id'],
                    'role' => $adminData['role'],
                    'name' => $name,
                ]);
            } else {
                if ($user->role !== $adminData['role']) {
                    $oldRole = $user->role;
                    $user->update([
                        'role' => $adminData['role'],
                        'first_name' => $adminData['first_name'],
                        'last_name' => $adminData['last_name'],
                        'name' => $name,
                    ]);

                    $this->command->info("Updated {$name} (ID: {$adminData['telegram_id']}) from {$oldRole} to {$adminData['role']}");
                    Log::info('TelegramAdminSeeder: Updated existing user role', [
                        'telegram_id' => $adminData['telegram_id'],
                        'old_role' => $oldRole,
                        'new_role' => $adminData['role'],
                    ]);
                } else {
                    $this->command->info("{$name} (ID: {$adminData['telegram_id']}) is already a {$adminData['role']}");
                }
            }
        }

        $adminTelegramId = env('TELE_ADMIN_ID');
        if (!empty($adminTelegramId) && !in_array((string) $adminTelegramId, ['1597383375', '8190923723'])) {
            $name = 'Admin';
            $user = User::where('telegram_id', $adminTelegramId)->first();

            if (!$user) {
                User::create([
                    'telegram_id' => $adminTelegramId,
                    'username' => null,
                    'first_name' => $name,
                    'last_name' => null,
                    'name' => $name,
                    'role' => User::ROLE_ADMIN,
                ]);

                $this->command->info("Created admin from TELE_ADMIN_ID: {$adminTelegramId}");
            } elseif ($user->role !== User::ROLE_ADMIN) {
                $user->update(['role' => User::ROLE_ADMIN]);
                $this->command->info("Updated user {$adminTelegramId} to admin (from TELE_ADMIN_ID)");
            }
        }

        $this->command->info('Telegram admin seeder completed successfully!');
    }
}