<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates default web admin and moderator users.
     */
    public function run(): void
    {
        $defaultUsers = [
            [
                'username' => 'superadmin',
                'name' => 'Super Admin',
                'phone' => null,
                'email' => 'superadmin@yopmail.com',
                'password' => 'superadmin123',
                'role' => 'super_admin',
                'telegram_id' => null,
            ],
            [
                'username' => 'admin',
                'name' => 'Dimas Prasetio',
                'phone' => '081234567890',
                'email' => 'admin@yopmail.com',
                'password' => 'admin123',
                'role' => 'admin',
                'telegram_id' => 1597383375,
            ],
            [
                'username' => 'moderator',
                'name' => 'Mahardika',
                'phone' => '081234567891',
                'email' => 'moderator@yopmail.com',
                'password' => 'moderator123',
                'role' => 'moderator',
                'telegram_id' => 8190923723,
            ],
        ];

        foreach ($defaultUsers as $userData) {
            $user = User::query()
                ->when(!empty($userData['telegram_id']), function ($query) use ($userData) {
                    $query->where('telegram_id', $userData['telegram_id']);
                })
                ->when(!empty($userData['email']), function ($query) use ($userData) {
                    $query->orWhere('email', $userData['email']);
                })
                ->first();

            if (!$user) {
                $userData['password'] = Hash::make($userData['password']);
                $user = User::create($userData);

                $this->command->info("Created {$userData['role']}: {$userData['name']} ({$userData['email']})");
                Log::info('UserSeeder: Created new web user', [
                    'user_id' => $user->id,
                    'username' => $userData['username'],
                    'role' => $userData['role'],
                ]);
            } else {
                $updates = [];

                if ($user->role !== $userData['role']) {
                    $updates['role'] = $userData['role'];
                }

                if (empty($user->email) && !empty($userData['email'])) {
                    $updates['email'] = $userData['email'];
                }

                if (empty($user->username) && !empty($userData['username'])) {
                    $updates['username'] = $userData['username'];
                }

                if (empty($user->name) && !empty($userData['name'])) {
                    $updates['name'] = $userData['name'];
                }

                if (empty($user->phone) && !empty($userData['phone'])) {
                    $updates['phone'] = $userData['phone'];
                }

                if (empty($user->telegram_id) && !empty($userData['telegram_id'])) {
                    $updates['telegram_id'] = $userData['telegram_id'];
                }

                if (empty($user->password) && !empty($userData['password'])) {
                    $updates['password'] = Hash::make($userData['password']);
                }

                if (!empty($updates)) {
                    $oldRole = $user->role;
                    $user->update($updates);

                    if (isset($updates['role'])) {
                        $this->command->info("Updated {$userData['name']}'s role from {$oldRole} to {$userData['role']}");
                        Log::info('UserSeeder: Updated existing user role', [
                            'user_id' => $user->id,
                            'old_role' => $oldRole,
                            'new_role' => $userData['role'],
                        ]);
                    } else {
                        $this->command->info("Updated {$userData['name']} ({$userData['email']})");
                    }
                } else {
                    $this->command->info("{$userData['name']} ({$userData['email']}) already exists");
                }
            }
        }

        $this->command->info('User seeder completed successfully!');
    }
}
