<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\TelegramUser;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates default web admin and moderator users
     * and automatically links them to their telegram accounts if available.
     */
    public function run(): void
    {
        // Default web users
        $defaultUsers = [
            [
                'username' => 'admin',
                'name' => 'Dimas Prasetio',
                'phone' => '081234567890',
                'email' => 'admin@yopmail.com',
                'password' => 'admin123',
                'role' => 'admin',
                'telegram_user_id' => '1597383375', // Link to Dimas's telegram
            ],
            [
                'username' => 'moderator',
                'name' => 'Mahardika',
                'phone' => '081234567891',
                'email' => 'moderator@yopmail.com',
                'password' => 'moderator123',
                'role' => 'moderator',
                'telegram_user_id' => '8190923723', // Link to Mahardika's telegram
            ],
        ];

        foreach ($defaultUsers as $userData) {
            $telegramUserId = $userData['telegram_user_id'];
            unset($userData['telegram_user_id']);

            // Check if user already exists
            $user = User::where('email', $userData['email'])->first();

            if (!$user) {
                // Create new user
                $userData['password'] = Hash::make($userData['password']);
                $user = User::create($userData);

                $this->command->info("Created {$userData['role']}: {$userData['name']} ({$userData['email']})");
                Log::info('UserSeeder: Created new web user', [
                    'user_id' => $user->id,
                    'username' => $userData['username'],
                    'role' => $userData['role'],
                ]);
            } else {
                // Update existing user if role changed
                if ($user->role !== $userData['role']) {
                    $oldRole = $user->role;
                    $user->update(['role' => $userData['role']]);

                    $this->command->info("Updated {$userData['name']}'s role from {$oldRole} to {$userData['role']}");
                    Log::info('UserSeeder: Updated existing user role', [
                        'user_id' => $user->id,
                        'old_role' => $oldRole,
                        'new_role' => $userData['role'],
                    ]);
                } else {
                    $this->command->info("{$userData['name']} ({$userData['email']}) already exists as {$userData['role']}");
                }
            }

            // Link to telegram user if exists
            $telegramUser = TelegramUser::where('telegram_user_id', $telegramUserId)->first();

            if ($telegramUser) {
                // Check if already linked
                if ($telegramUser->linked_user_id !== $user->id) {
                    $telegramUser->linkToUser($user);

                    $this->command->info("  → Linked to Telegram user: {$telegramUser->full_name} (ID: {$telegramUserId})");
                    Log::info('UserSeeder: Linked web user to telegram user', [
                        'user_id' => $user->id,
                        'telegram_user_id' => $telegramUserId,
                    ]);

                    // The UserObserver will automatically sync the role to telegram user
                } else {
                    $this->command->info("  → Already linked to Telegram user: {$telegramUser->full_name}");
                }
            } else {
                $this->command->warn("  ⚠ Telegram user {$telegramUserId} not found. Run TelegramAdminSeeder first!");
            }
        }

        $this->command->info('User seeder completed successfully!');
    }
}