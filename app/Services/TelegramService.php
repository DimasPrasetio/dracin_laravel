<?php

namespace App\Services;

use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Api;
use Telegram\Bot\FileUpload\InputFile;
use App\Models\Movie;
use App\Models\Setting;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Current category context
     */
    protected ?Category $category = null;

    /**
     * Custom Telegram API instance for specific bot
     */
    protected ?Api $customBot = null;

    /**
     * Set category context for all operations
     */
    public function setCategory(Category $category): self
    {
        $this->category = $category;
        $this->customBot = null; // Reset custom bot
        return $this;
    }

    /**
     * Get category context
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Get Telegram API instance for current context
     * If category is set, uses category's bot token
     * Otherwise uses default bot from config
     */
    protected function getBot(): Api
    {
        if ($this->category && $this->category->bot_token) {
            if (!$this->customBot) {
                $this->customBot = new Api($this->category->bot_token);
            }
            return $this->customBot;
        }

        return Telegram::bot();
    }

    /**
     * Get admin chat ID for uploads
     */
    protected function getAdminChatId(): ?int
    {
        // Use environment variable for admin ID (same for all bots)
        return (int) config('telegram.bots.default.admin_id', env('TELE_ADMIN_ID'));
    }

    /**
     * Get channel ID for posting
     */
    protected function getChannelId(): ?string
    {
        if ($this->category && $this->category->channel_id) {
            return $this->category->channel_id;
        }

        return config('telegram.bots.default.channel_id', env('TELE_CHANNEL_ID'));
    }

    /**
     * Get bot username
     */
    protected function getBotUsername(): string
    {
        if ($this->category && $this->category->bot_username) {
            return ltrim($this->category->bot_username, '@');
        }

        return ltrim(config('telegram.bots.default.username', env('TELE_BOT_USERNAME')), '@');
    }

    /**
     * Upload video to Telegram and get file_id (silent mode with auto-delete)
     */
    public function uploadVideo($filePath, int $chatId = null): ?string
    {
        try {
            if (!file_exists($filePath)) {
                Log::error('Video file not found', [
                    'file_path' => $filePath,
                    'directory_exists' => is_dir(dirname($filePath)),
                    'directory_path' => dirname($filePath)
                ]);
                return null;
            }

            $chatId = $chatId ?? $this->getAdminChatId();

            if (empty($chatId)) {
                Log::error('Telegram admin ID not configured');
                return null;
            }

            Log::info('Uploading video to Telegram (silent mode)', [
                'file_path' => $filePath,
                'chat_id' => $chatId,
                'file_size' => filesize($filePath),
                'category' => $this->category?->name ?? 'default'
            ]);

            $bot = $this->getBot();

            // Upload video to get file_id
            $response = $bot->sendVideo([
                'chat_id' => $chatId,
                'video' => InputFile::create($filePath),
            ]);

            $fileId = $response->video->file_id ?? null;

            Log::info('Video uploaded successfully', [
                'file_id' => $fileId,
                'category' => $this->category?->name ?? 'default'
            ]);

            // Auto-delete message to keep admin chat clean
            if ($fileId && isset($response->message_id)) {
                try {
                    $bot->deleteMessage([
                        'chat_id' => $chatId,
                        'message_id' => $response->message_id,
                    ]);
                    Log::info('Video message auto-deleted from admin chat', [
                        'message_id' => $response->message_id
                    ]);
                } catch (\Exception $deleteException) {
                    Log::warning('Failed to auto-delete video message', [
                        'message_id' => $response->message_id,
                        'error' => $deleteException->getMessage()
                    ]);
                }
            }

            return $fileId;
        } catch (\Exception $e) {
            Log::error('Failed to upload video to Telegram', [
                'file_path' => $filePath,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'exception_class' => get_class($e),
                'category' => $this->category?->name ?? 'default'
            ]);
            return null;
        }
    }

    /**
     * Upload photo to Telegram and get file_id (silent mode with auto-delete)
     */
    public function uploadPhoto($filePath, int $chatId = null): ?string
    {
        try {
            if (!file_exists($filePath)) {
                Log::error('Photo file not found', [
                    'file_path' => $filePath,
                    'directory_exists' => is_dir(dirname($filePath)),
                    'directory_path' => dirname($filePath)
                ]);
                return null;
            }

            $chatId = $chatId ?? $this->getAdminChatId();

            if (empty($chatId)) {
                Log::error('Telegram admin ID not configured');
                return null;
            }

            Log::info('Uploading photo to Telegram (silent mode)', [
                'file_path' => $filePath,
                'chat_id' => $chatId,
                'file_size' => filesize($filePath),
                'mime_type' => mime_content_type($filePath),
                'category' => $this->category?->name ?? 'default'
            ]);

            $bot = $this->getBot();

            // Upload photo to get file_id
            $response = $bot->sendPhoto([
                'chat_id' => $chatId,
                'photo' => InputFile::create($filePath),
            ]);

            $fileId = isset($response->photo) && count($response->photo) > 0
                ? $response->photo[count($response->photo) - 1]->file_id
                : null;

            Log::info('Photo uploaded successfully', [
                'file_id' => $fileId,
                'category' => $this->category?->name ?? 'default'
            ]);

            // Auto-delete message to keep admin chat clean
            if ($fileId && isset($response->message_id)) {
                try {
                    $bot->deleteMessage([
                        'chat_id' => $chatId,
                        'message_id' => $response->message_id,
                    ]);
                    Log::info('Photo message auto-deleted from admin chat', [
                        'message_id' => $response->message_id
                    ]);
                } catch (\Exception $deleteException) {
                    Log::warning('Failed to auto-delete photo message', [
                        'message_id' => $response->message_id,
                        'error' => $deleteException->getMessage()
                    ]);
                }
            }

            return $fileId;
        } catch (\Exception $e) {
            Log::error('Failed to upload photo to Telegram', [
                'file_path' => $filePath,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'exception_class' => get_class($e),
                'category' => $this->category?->name ?? 'default'
            ]);
            return null;
        }
    }

    /**
     * Send video using file_id
     */
    public function sendVideo(int $chatId, string $fileId, string $caption = null): bool
    {
        try {
            $this->getBot()->sendVideo([
                'chat_id' => $chatId,
                'video' => $fileId,
                'caption' => $caption,
                'parse_mode' => 'HTML',
                'protect_content' => true,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send video: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Post movie to channel
     */
    public function postMovieToChannel(Movie $movie): ?int
    {
        try {
            $movie->loadMissing('category');

            // If movie has category, use that category's settings
            if ($movie->category_id && $movie->category) {
                $this->setCategory($movie->category);
            }

            $this->assertCategoryChannelConfig($movie, 'post movie to channel');
            $channelId = $this->getChannelId();
            $botUsername = $this->getBotUsername();

            if (empty($channelId)) {
                Log::warning('No channel configured for posting', [
                    'movie_id' => $movie->id,
                    'category' => $this->category?->name ?? 'default'
                ]);
                return null;
            }

            $caption = $this->generateChannelCaption($movie, $botUsername);
            $bot = $this->getBot();

            try {
                $response = $bot->sendPhoto([
                    'chat_id' => $channelId,
                    'photo' => $movie->thumbnail_file_id,
                    'caption' => $caption,
                    'parse_mode' => 'HTML',
                    'protect_content' => true,
                ]);

                return $response->message_id;
            } catch (\Exception $photoException) {
                Log::warning('Failed to post thumbnail as photo, trying document', [
                    'error' => $photoException->getMessage(),
                    'thumbnail_file_id' => $movie->thumbnail_file_id,
                ]);

                $response = $bot->sendDocument([
                    'chat_id' => $channelId,
                    'document' => $movie->thumbnail_file_id,
                    'caption' => $caption,
                    'parse_mode' => 'HTML',
                    'protect_content' => true,
                ]);

                return $response->message_id;
            }
        } catch (\Exception $e) {
            Log::error('Failed to post movie to channel: ' . $e->getMessage(), [
                'movie_id' => $movie->id,
                'category' => $this->category?->name ?? 'default'
            ]);
            return null;
        }
    }

    /**
     * Update movie message in channel
     */
    public function updateChannelMessage(Movie $movie): bool
    {
        try {
            if (!$movie->channel_message_id) {
                return false;
            }

            $movie->loadMissing('category');

            // If movie has category, use that category's settings
            if ($movie->category_id && $movie->category) {
                $this->setCategory($movie->category);
            }

            $this->assertCategoryChannelConfig($movie, 'update channel message');
            $channelId = $this->getChannelId();
            $botUsername = $this->getBotUsername();

            $caption = $this->generateChannelCaption($movie, $botUsername);

            $this->getBot()->editMessageCaption([
                'chat_id' => $channelId,
                'message_id' => $movie->channel_message_id,
                'caption' => $caption,
                'parse_mode' => 'HTML',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update channel message: ' . $e->getMessage(), [
                'movie_id' => $movie->id,
                'category' => $this->category?->name ?? 'default'
            ]);
            return false;
        }
    }

    /**
     * Delete message from channel
     */
    public function deleteChannelMessage(int $messageId, ?Category $category = null): bool
    {
        try {
            if ($category) {
                $this->setCategory($category);
            }

            $this->assertCategoryChannelConfig(null, 'delete channel message');
            $channelId = $this->getChannelId();

            $this->getBot()->deleteMessage([
                'chat_id' => $channelId,
                'message_id' => $messageId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete channel message: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate caption for channel post
     */
    private function generateChannelCaption(Movie $movie, string $botUsername): string
    {
        $movie->load('videoParts');

        $caption = "<b>{$movie->title}</b>\n\n";

        $freeParts = Setting::getFreeParts();

        foreach ($movie->videoParts as $part) {
            $isLastPart = $part->part_number === $movie->total_parts;
            $isFree = in_array($part->part_number, $freeParts) || !$part->is_vip;

            $caption .= "Part {$part->part_number}";

            if (!$isFree) {
                $caption .= " (VIP)";
            }

            if ($isLastPart) {
                $caption .= " END";
            }

            $caption .= "\n";
            $caption .= "https://t.me/{$botUsername}?start={$part->video_id}\n";
        }

        // Add footer
        $footer = Setting::get('channel_post_footer');
        if ($footer) {
            $caption .= "\n" . $footer;
        }

        return $caption;
    }

    /**
     * Send message to user
     */
    public function sendMessage(int $chatId, string $text, $replyMarkup = null)
    {
        try {
            $response = $this->getBot()->sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => $replyMarkup,
                'protect_content' => true,
            ]);

            return $response->message_id ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to send message: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete message
     */
    public function deleteMessage(int $chatId, int $messageId): bool
    {
        try {
            $this->getBot()->deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::warning('Failed to delete message', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Answer callback query
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text = null, bool $showAlert = false): bool
    {
        try {
            $this->getBot()->answerCallbackQuery([
                'callback_query_id' => $callbackQueryId,
                'text' => $text,
                'show_alert' => $showAlert,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to answer callback query: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Edit message text
     */
    public function editMessageText(int $chatId, int $messageId, string $text, $replyMarkup = null): bool
    {
        try {
            $this->getBot()->editMessageText([
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => $replyMarkup,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to edit message: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Set webhook for category's bot
     */
    public function setWebhook(Category $category): bool
    {
        try {
            $this->setCategory($category);

            $webhookUrl = $category->webhook_url;

            $this->getBot()->setWebhook([
                'url' => $webhookUrl,
                'secret_token' => $category->webhook_secret,
            ]);

            Log::info('Webhook set for category', [
                'category' => $category->name,
                'webhook_url' => $webhookUrl
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to set webhook', [
                'category' => $category->name,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete webhook for category's bot
     */
    public function deleteWebhook(Category $category): bool
    {
        try {
            $this->setCategory($category);

            $this->getBot()->deleteWebhook();

            Log::info('Webhook deleted for category', [
                'category' => $category->name
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete webhook', [
                'category' => $category->name,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get webhook info for category's bot
     */
    public function getWebhookInfo(Category $category): array
    {
        try {
            $this->setCategory($category);

            $info = $this->getBot()->getWebhookInfo();

            return [
                'url' => $info->url ?? null,
                'has_custom_certificate' => $info->has_custom_certificate ?? false,
                'pending_update_count' => $info->pending_update_count ?? 0,
                'last_error_date' => $info->last_error_date ?? null,
                'last_error_message' => $info->last_error_message ?? null,
                'max_connections' => $info->max_connections ?? null,
                'ip_address' => $info->ip_address ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get webhook info', [
                'category' => $category->name,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create a new instance for a specific category
     * Useful for when you need multiple category contexts
     */
    public static function forCategory(Category $category): self
    {
        $instance = new self();
        $instance->setCategory($category);
        return $instance;
    }

    private function assertCategoryChannelConfig(?Movie $movie, string $action): void
    {
        if (!$this->category) {
            $movieId = $movie?->id ? " for movie ID {$movie->id}" : '';
            throw new \RuntimeException("Cannot {$action}{$movieId}: category context is missing.");
        }

        if (empty($this->category->bot_token)) {
            throw new \RuntimeException("Cannot {$action}: bot token is missing for category {$this->category->name}.");
        }

        if (empty($this->category->channel_id)) {
            throw new \RuntimeException("Cannot {$action}: channel ID is missing for category {$this->category->name}.");
        }

        if (empty($this->category->bot_username)) {
            throw new \RuntimeException("Cannot {$action}: bot username is missing for category {$this->category->name}.");
        }
    }
}
