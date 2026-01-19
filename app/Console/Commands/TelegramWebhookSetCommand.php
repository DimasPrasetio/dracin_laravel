<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;

class TelegramWebhookSetCommand extends Command
{
    protected $signature = 'telegram:webhook:set {url?} {--certificate=}';
    protected $description = 'Set Telegram webhook URL';

    public function handle()
    {
        $url = $this->argument('url') ?? config('telegram.webhook_url', env('TELEGRAM_WEBHOOK_URL'));
        $certificate = $this->option('certificate');

        if (!$url) {
            $this->error(' Webhook URL not provided!');
            $this->info('Please provide URL as argument or set TELEGRAM_WEBHOOK_URL in .env');
            $this->newLine();
            $this->info('Example: php artisan telegram:webhook:set https://yourdomain.com/api/telegram/webhook');
            return Command::FAILURE;
        }

        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error(' Invalid URL format!');
            return Command::FAILURE;
        }

        // Check if URL is HTTPS
        if (!str_starts_with($url, 'https://')) {
            $this->error(' Webhook URL must use HTTPS!');
            $this->info('Telegram webhooks require HTTPS connection.');
            return Command::FAILURE;
        }

        $this->info(' Setting webhook...');
        $this->info('URL: ' . $url);

        try {
            $params = [
                'url' => $url,
                'allowed_updates' => ['message', 'callback_query'],
            ];

            $secretToken = config('telegram.webhook_secret_token');
            if (!empty($secretToken)) {
                $params['secret_token'] = $secretToken;
            }

            if ($certificate) {
                if (!file_exists($certificate)) {
                    $this->error(' Certificate file not found: ' . $certificate);
                    return Command::FAILURE;
                }
                $params['certificate'] = $certificate;
                $this->info('Certificate: ' . $certificate);
            }

            $response = Telegram::setWebhook($params);

            if ($response) {
                $this->newLine();
                $this->info(' Webhook set successfully!');
                $this->newLine();

                // Get webhook info
                $this->call('telegram:webhook:info');

                $this->newLine();
                $this->info(' Tips:');
                $this->line('  - Test your webhook: Send a message to your bot');
                $this->line('  - Check webhook status: php artisan telegram:webhook:info');
                $this->line('  - View logs: tail -f storage/logs/laravel.log');

                Log::info('Webhook set successfully', ['url' => $url]);

                return Command::SUCCESS;
            } else {
                $this->error(' Failed to set webhook!');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error(' Error: ' . $e->getMessage());
            Log::error('Failed to set webhook', [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            return Command::FAILURE;
        }
    }
}

