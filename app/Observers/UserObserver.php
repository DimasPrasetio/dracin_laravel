<?php

namespace App\Observers;

use App\Models\User;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Skip if not admin or moderator
        if (!in_array($user->role, ['admin', 'moderator'])) {
            return;
        }

        // If user has linked telegram account, sync role
        if ($user->linkedTelegramUser) {
            $this->syncRoleToTelegram($user);
        }

        Log::info('User created, telegram sync attempted', [
            'user_id' => $user->id,
            'role' => $user->role,
            'has_telegram' => (bool) $user->linkedTelegramUser,
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Check if role was changed
        if (!$user->wasChanged('role')) {
            return;
        }

        // If user has linked telegram account, sync role
        if ($user->linkedTelegramUser) {
            $this->syncRoleToTelegram($user);

            Log::info('User role updated, synced to telegram', [
                'user_id' => $user->id,
                'old_role' => $user->getOriginal('role'),
                'new_role' => $user->role,
                'telegram_user_id' => $user->linkedTelegramUser->telegram_user_id,
            ]);
        } else {
            Log::info('User role updated, but no linked telegram account', [
                'user_id' => $user->id,
                'role' => $user->role,
            ]);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // If user has linked telegram account, demote to regular user
        if ($user->linkedTelegramUser) {
            $telegramUser = $user->linkedTelegramUser;

            // Demote to regular user
            $telegramUser->update(['role' => TelegramUser::ROLE_USER]);

            Log::info('User deleted, telegram user demoted to regular user', [
                'user_id' => $user->id,
                'telegram_user_id' => $telegramUser->telegram_user_id,
            ]);
        }
    }

    /**
     * Sync user role to telegram user
     */
    private function syncRoleToTelegram(User $user): void
    {
        $telegramUser = $user->linkedTelegramUser;

        if (!$telegramUser) {
            return;
        }

        // Map web role to telegram role
        $telegramRole = match ($user->role) {
            'admin' => TelegramUser::ROLE_ADMIN,
            'moderator' => TelegramUser::ROLE_MODERATOR,
            default => TelegramUser::ROLE_USER,
        };

        // Update telegram user role
        $telegramUser->update(['role' => $telegramRole]);

        // Clear cache
        app(\App\Services\TelegramAuthService::class)
            ->clearUserCache($telegramUser->telegram_user_id);
    }
}
