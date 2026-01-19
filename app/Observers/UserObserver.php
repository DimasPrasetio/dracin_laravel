<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Log::info('User created', [
            'user_id' => $user->id,
            'role' => $user->role,
            'telegram_id' => $user->telegram_id,
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
        Log::info('User role updated', [
            'user_id' => $user->id,
            'old_role' => $user->getOriginal('role'),
            'new_role' => $user->role,
            'telegram_id' => $user->telegram_id,
        ]);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Log::info('User deleted', [
            'user_id' => $user->id,
            'telegram_id' => $user->telegram_id,
        ]);
    }
}
