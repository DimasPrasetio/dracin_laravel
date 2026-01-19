<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;

class TelegramWebhookRemoveCommand extends Command
{
    protected $signature = 'telegram:webhook:remove';
    protected $description = 'Remove Telegram webhook (switch to polling mode)';

    public function handle()
    {
        $this->info(' Removing webhook...');

        try {
            $response = Telegram::removeWebhook();

            if ($response) {
                $this->newLine();
                $this->info(' Webhook removed successfully!');
                $this->newLine();
                $this->info(' You can now use polling mode:');
                $this->line('   php artisan telegram:polling');

                Log::info('Webhook removed successfully');

                return Command::SUCCESS;
            } else {
                $this->error(' Failed to remove webhook!');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error(' Error: ' . $e->getMessage());
            Log::error('Failed to remove webhook', [
                'error' => $e->getMessage()
            ]);
            return Command::FAILURE;
        }
    }
}

