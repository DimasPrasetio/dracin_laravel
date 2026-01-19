<?php

namespace App\Jobs;

use App\Models\Category;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ClearChatHistory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $chatId,
        private readonly int $telegramUserId,
        private readonly array $messageIds,
        private readonly string $title,
        private readonly ?int $categoryId = null,
    ) {}

    public function handle(TelegramService $telegramService): void
    {
        try {
            if ($this->categoryId) {
                $category = Category::find($this->categoryId);
                if ($category) {
                    $telegramService->setCategory($category);
                }
            }

            $deletedCount = 0;

            foreach ($this->messageIds as $messageId) {
                if ($telegramService->deleteMessage($this->chatId, $messageId)) {
                    $deletedCount++;
                }
                usleep(50000);
            }

            Log::info('Chat history cleared', [
                'telegram_id' => $this->telegramUserId,
                'total_messages' => count($this->messageIds),
                'deleted_count' => $deletedCount,
            ]);

            $telegramService->sendMessage(
                $this->chatId,
                "Riwayat chat telah dibersihkan. Film {$this->title} berhasil diposting ke channel. Gunakan /addmovie untuk menambah film baru."
            );
        } catch (\Exception $e) {
            Log::error('Failed to clear chat history', [
                'error' => $e->getMessage(),
                'telegram_id' => $this->telegramUserId,
            ]);
        }
    }
}
