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
     * This seeder will set the initial admin from TELE_ADMIN_ID environment variable
     */
    public function run(): void
    {
        $adminTelegramId = env('TELE_ADMIN_ID');

        if (empty($adminTelegramId)) {
            $this->command->warn('TELE_ADMIN_ID is not set in .env file. Skipping admin seeder.');
            Log::warning('TelegramAdminSeeder skipped: TELE_ADMIN_ID not configured');
            return;
        }

        // Check if telegram user exists
        $telegramUser = TelegramUser::where('telegram_user_id', $adminTelegramId)->first();

        if (!$telegramUser) {
            // Create new telegram user as admin
            $telegramUser = TelegramUser::create([
                'telegram_user_id' => $adminTelegramId,
                'username' => null,
                'first_name' => 'Admin',
                'last_name' => null,
                'role' => TelegramUser::ROLE_ADMIN,
            ]);

            $this->command->info("Created new Telegram admin user with ID: {$adminTelegramId}");
            Log::info('TelegramAdminSeeder: Created new admin', [
                'telegram_user_id' => $adminTelegramId,
            ]);

        } else {
            // Update existing user to admin
            if ($telegramUser->role !== TelegramUser::ROLE_ADMIN) {
                $oldRole = $telegramUser->role;
                $telegramUser->update(['role' => TelegramUser::ROLE_ADMIN]);

                $this->command->info("Updated Telegram user {$adminTelegramId} from {$oldRole} to admin");
                Log::info('TelegramAdminSeeder: Updated existing user to admin', [
                    'telegram_user_id' => $adminTelegramId,
                    'old_role' => $oldRole,
                    'new_role' => TelegramUser::ROLE_ADMIN,
                ]);
            } else {
                $this->command->info("Telegram user {$adminTelegramId} is already an admin");
            }
        }

        $this->command->info('Telegram admin seeder completed successfully!');
    }
}
