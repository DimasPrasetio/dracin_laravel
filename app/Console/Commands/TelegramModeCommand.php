<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;

class TelegramModeCommand extends Command
{
    protected $signature = 'telegram:mode {mode : The mode to use (polling or webhook)}';
    protected $description = 'Switch Telegram bot mode between polling and webhook';

    public function handle()
    {
        $mode = strtolower($this->argument('mode'));

        if (!in_array($mode, ['polling', 'webhook'])) {
            $this->error('❌ Invalid mode! Use "polling" or "webhook"');
            return Command::FAILURE;
        }

        $this->info("🔄 Switching to {$mode} mode...");
        $this->newLine();

        try {
            if ($mode === 'polling') {
                // Remove webhook for polling mode
                $response = Telegram::removeWebhook();

                if ($response) {
                    $this->info('✅ Switched to POLLING mode successfully!');
                    $this->newLine();
                    $this->info('💡 Next steps:');
                    $this->line('   1. Update .env: TELE_BOT_MODE=polling');
                    $this->line('   2. Start polling: php artisan telegram:polling');
                    $this->newLine();
                    $this->warn('⚠️  Remember to use a process manager (supervisor/pm2) in production!');

                    Log::info('Switched to polling mode');

                    return Command::SUCCESS;
                }
            } else {
                // Set webhook mode
                $webhookUrl = config('telegram.webhook_url', env('TELEGRAM_WEBHOOK_URL'));

                if (!$webhookUrl) {
                    $this->error('❌ Webhook URL not configured!');
                    $this->newLine();
                    $this->info('💡 Please set TELEGRAM_WEBHOOK_URL in .env:');
                    $this->line('   TELEGRAM_WEBHOOK_URL=https://yourdomain.com/api/telegram/webhook');
                    $this->newLine();
                    $this->info('Or use:');
                    $this->line('   php artisan telegram:webhook:set https://yourdomain.com/api/telegram/webhook');

                    return Command::FAILURE;
                }

                // Validate HTTPS
                if (!str_starts_with($webhookUrl, 'https://')) {
                    $this->error('❌ Webhook URL must use HTTPS!');
                    return Command::FAILURE;
                }

                $this->info('Setting webhook: ' . $webhookUrl);

                $params = [
                    'url' => $webhookUrl,
                    'allowed_updates' => ['message', 'callback_query'],
                ];

                $secretToken = config('telegram.webhook_secret_token');
                if (!empty($secretToken)) {
                    $params['secret_token'] = $secretToken;
                }

                $response = Telegram::setWebhook($params);

                if ($response) {
                    $this->info('✅ Switched to WEBHOOK mode successfully!');
                    $this->newLine();
                    $this->info('💡 Next steps:');
                    $this->line('   1. Update .env: TELE_BOT_MODE=webhook');
                    $this->line('   2. Stop any running polling processes');
                    $this->line('   3. Test: Send a message to your bot');
                    $this->line('   4. Check status: php artisan telegram:webhook:info');
                    $this->newLine();
                    $this->info('📊 Webhook Info:');
                    $this->call('telegram:webhook:info');

                    Log::info('Switched to webhook mode', ['url' => $webhookUrl]);

                    return Command::SUCCESS;
                }
            }

            $this->error('❌ Failed to switch mode!');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('Failed to switch mode', [
                'mode' => $mode,
                'error' => $e->getMessage()
            ]);
            return Command::FAILURE;
        }
    }
}
