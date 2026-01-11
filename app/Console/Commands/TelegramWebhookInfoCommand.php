<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramWebhookInfoCommand extends Command
{
    protected $signature = 'telegram:webhook:info';
    protected $description = 'Get Telegram webhook information';

    public function handle()
    {
        $this->info('🔍 Fetching webhook info...');
        $this->newLine();

        try {
            $webhookInfo = Telegram::getWebhookInfo();

            if (empty($webhookInfo->url)) {
                $this->warn('⚠️  No webhook is set (Polling mode)');
                $this->newLine();
                $this->info('To set webhook:');
                $this->line('   php artisan telegram:webhook:set https://yourdomain.com/api/telegram/webhook');
            } else {
                $this->info('✅ Webhook is active');
                $this->newLine();

                $this->line('<fg=cyan>URL:</>           ' . $webhookInfo->url);
                $this->line('<fg=cyan>Has Custom Cert:</> ' . ($webhookInfo->has_custom_certificate ? 'Yes' : 'No'));
                $this->line('<fg=cyan>Pending Updates:</> ' . ($webhookInfo->pending_update_count ?? 0));

                if (isset($webhookInfo->ip_address)) {
                    $this->line('<fg=cyan>IP Address:</>     ' . $webhookInfo->ip_address);
                }

                if (isset($webhookInfo->last_error_date) && $webhookInfo->last_error_date > 0) {
                    $this->newLine();
                    $this->warn('⚠️  Last Error:');
                    $this->line('   Date: ' . date('Y-m-d H:i:s', $webhookInfo->last_error_date));
                    if (isset($webhookInfo->last_error_message)) {
                        $this->line('   Message: ' . $webhookInfo->last_error_message);
                    }
                } else {
                    $this->newLine();
                    $this->info('✅ No errors reported');
                }

                if (isset($webhookInfo->last_synchronization_error_date) && $webhookInfo->last_synchronization_error_date > 0) {
                    $this->newLine();
                    $this->warn('⚠️  Last Sync Error:');
                    $this->line('   Date: ' . date('Y-m-d H:i:s', $webhookInfo->last_synchronization_error_date));
                }

                if (isset($webhookInfo->max_connections)) {
                    $this->newLine();
                    $this->line('<fg=cyan>Max Connections:</> ' . $webhookInfo->max_connections);
                }

                if (isset($webhookInfo->allowed_updates) && !empty($webhookInfo->allowed_updates)) {
                    $this->newLine();
                    $this->line('<fg=cyan>Allowed Updates:</> ' . implode(', ', $webhookInfo->allowed_updates));
                }
            }

            $this->newLine();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
