<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Services\TelegramUpdateProcessor;
use Illuminate\Support\Facades\Log;

class TelegramPollingCommand extends Command
{
    protected $signature = 'telegram:polling {--timeout=30 : Polling timeout in seconds}';
    protected $description = 'Start Telegram Bot polling for updates';

    protected $updateProcessor;
    protected $lastUpdateId = 0;

    public function __construct(TelegramUpdateProcessor $updateProcessor)
    {
        parent::__construct();
        $this->updateProcessor = $updateProcessor;
    }

    public function handle()
    {
        if (config('telegram.mode') !== 'polling') {
            $this->warn('Polling mode is disabled. Set TELE_BOT_MODE=polling to run this command.');
            return Command::FAILURE;
        }

        $timeout = (int) $this->option('timeout');

        $this->info("🤖 Starting Telegram Bot Polling...");
        $this->info('Bot: ' . config('telegram.bots.default.username'));
        $this->info('Press Ctrl+C to stop');
        $this->newLine();

        // Disable push updates to ensure polling mode
        try {
            Telegram::removeWebhook();
            $this->info("✅ Polling mode aktif, siap menerima update");
        } catch (\Exception $e) {
            $this->warn("⚠️ Gagal mengaktifkan polling: " . $e->getMessage());
        }

        $this->newLine();

        $lastHeartbeat = time();
        $heartbeatInterval = 60; // 60 seconds

        while (true) {
            try {
                // Memory limit check (restart if > 100MB)
                $memoryUsage = memory_get_usage(true);
                if ($memoryUsage > 104857600) { // 100MB in bytes
                    $memoryMB = round($memoryUsage / 1048576, 2);
                    $this->warn("⚠️ Memory limit reached: {$memoryMB}MB. Restarting...");
                    Log::warning('Polling command restarting due to memory limit', [
                        'memory_usage' => $memoryMB . 'MB'
                    ]);
                    exit(0); // Exit and let supervisor restart
                }

                $updates = Telegram::getUpdates([
                    'offset' => $this->lastUpdateId + 1,
                    'timeout' => $timeout,
                    'allowed_updates' => ['message', 'callback_query'],
                ]);

                foreach ($updates as $update) {
                    $this->processUpdate($update);
                    $this->lastUpdateId = $update->update_id;
                }

                // Show heartbeat or update count
                if (count($updates) > 0) {
                    $this->info("💓 Processed " . count($updates) . ' update(s) - Last ID: ' . $this->lastUpdateId);
                    $lastHeartbeat = time(); // Reset heartbeat timer on activity
                } else {
                    // Show heartbeat if no updates for a while
                    if (time() - $lastHeartbeat >= $heartbeatInterval) {
                        $memoryMB = round(memory_get_usage(true) / 1048576, 2);
                        $this->line("💓 Heartbeat - Waiting for updates... (Memory: {$memoryMB}MB)");
                        $lastHeartbeat = time();
                    }
                }
            } catch (\Exception $e) {
                $this->error("❌ Error: " . $e->getMessage());
                Log::error('Telegram Polling Error: ' . $e->getMessage());

                // Wait before retrying
                sleep(5);
            }
        }

        return Command::SUCCESS;
    }

    protected function processUpdate($update)
    {
        try {
            // Log command if it's a command message
            if ($update->getMessage() && isset($update->getMessage()->text)) {
                $text = $update->getMessage()->text;
                if (str_starts_with($text, '/')) {
                    $this->line("✅ Command received: {$text}");
                }
            }

            // Use shared update processor
            $this->updateProcessor->processUpdate($update);
        } catch (\Exception $e) {
            Log::error('Update processing error: ' . $e->getMessage());
            $this->error("⚠️ Failed to process update: " . $e->getMessage());
        }
    }
}
