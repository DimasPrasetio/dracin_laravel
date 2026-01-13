<?php

use App\Models\BotState;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Schedule::command('storage:cleanup-temp')
    ->hourly()
    ->withoutOverlapping();

Schedule::call(function () {
    $deletedCount = BotState::cleanupExpired();

    if ($deletedCount > 0) {
        Log::info('Cleaned up expired bot states', [
            'deleted_count' => $deletedCount,
        ]);
    }
})->everyFifteenMinutes()
    ->name('cleanup-expired-bot-states')
    ->withoutOverlapping();
