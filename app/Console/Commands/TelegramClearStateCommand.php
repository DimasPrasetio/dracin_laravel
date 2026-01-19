<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BotState;

class TelegramClearStateCommand extends Command
{
    protected $signature = 'telegram:clear-state {user_id? : Telegram user ID to clear (optional)}';
    protected $description = 'Clear bot conversation state for user(s)';

    public function handle()
    {
        $userId = $this->argument('user_id');

        if ($userId) {
            // Clear specific user
            BotState::clearState((int) $userId);
            $this->info(" State cleared for user ID: {$userId}");
        } else {
            // Clear all states (with confirmation)
            if (!$this->confirm(' Clear ALL user states?', false)) {
                $this->info('Cancelled.');
                return Command::SUCCESS;
            }

            $count = BotState::count();
            BotState::query()->delete();
            $this->info(" Cleared {$count} state(s)");
        }

        return Command::SUCCESS;
    }
}

