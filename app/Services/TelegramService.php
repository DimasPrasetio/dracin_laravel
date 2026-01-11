<?php

namespace App\Services;

use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;
use App\Models\Movie;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class TelegramService
{
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

            $chatId = $chatId ?? config('telegram.bots.default.admin_id', env('TELE_ADMIN_ID'));

            if (empty($chatId)) {
                Log::error('Telegram admin ID not configured');
                return null;
            }

            Log::info('Uploading video to Telegram (silent mode)', [
                'file_path' => $filePath,
                'chat_id' => $chatId,
                'file_size' => filesize($filePath)
            ]);

            // Upload video to get file_id
            $response = Telegram::sendVideo([
                'chat_id' => $chatId,
                'video' => InputFile::create($filePath),
            ]);

            $fileId = $response->video->file_id ?? null;

            Log::info('Video uploaded successfully', ['file_id' => $fileId]);

            // Auto-delete message to keep admin chat clean
            if ($fileId && isset($response->message_id)) {
                try {
                    Telegram::deleteMessage([
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
                    // Don't fail the whole operation if delete fails
                }
            }

            return $fileId;
        } catch (\Exception $e) {
            Log::error('Failed to upload video to Telegram', [
                'file_path' => $filePath,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'exception_class' => get_class($e)
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

            $chatId = $chatId ?? config('telegram.bots.default.admin_id', env('TELE_ADMIN_ID'));

            if (empty($chatId)) {
                Log::error('Telegram admin ID not configured');
                return null;
            }

            Log::info('Uploading photo to Telegram (silent mode)', [
                'file_path' => $filePath,
                'chat_id' => $chatId,
                'file_size' => filesize($filePath),
                'mime_type' => mime_content_type($filePath)
            ]);

            // Upload photo to get file_id
            $response = Telegram::sendPhoto([
                'chat_id' => $chatId,
                'photo' => InputFile::create($filePath),
            ]);

            $fileId = isset($response->photo) && count($response->photo) > 0
                ? $response->photo[count($response->photo) - 1]->file_id
                : null;

            Log::info('Photo uploaded successfully', ['file_id' => $fileId]);

            // Auto-delete message to keep admin chat clean
            if ($fileId && isset($response->message_id)) {
                try {
                    Telegram::deleteMessage([
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
                    // Don't fail the whole operation if delete fails
                }
            }

            return $fileId;
        } catch (\Exception $e) {
            Log::error('Failed to upload photo to Telegram', [
                'file_path' => $filePath,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'exception_class' => get_class($e)
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
            Telegram::sendVideo([
                'chat_id' => $chatId,
                'video' => $fileId,
                'caption' => $caption,
                'parse_mode' => 'HTML',
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
            $channelId = config('telegram.bots.default.channel_id', env('TELE_CHANNEL_ID'));
            $botUsername = ltrim(config('telegram.bots.default.username', env('TELE_BOT_USERNAME')), '@');

            $caption = $this->generateChannelCaption($movie, $botUsername);

            try {
                $response = Telegram::sendPhoto([
                    'chat_id' => $channelId,
                    'photo' => $movie->thumbnail_file_id,
                    'caption' => $caption,
                    'parse_mode' => 'HTML',
                ]);

                return $response->message_id;
            } catch (\Exception $photoException) {
                Log::warning('Failed to post thumbnail as photo, trying document', [
                    'error' => $photoException->getMessage(),
                    'thumbnail_file_id' => $movie->thumbnail_file_id,
                ]);

                $response = Telegram::sendDocument([
                    'chat_id' => $channelId,
                    'document' => $movie->thumbnail_file_id,
                    'caption' => $caption,
                    'parse_mode' => 'HTML',
                ]);

                return $response->message_id;
            }
        } catch (\Exception $e) {
            Log::error('Failed to post movie to channel: ' . $e->getMessage());
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

            $channelId = config('telegram.bots.default.channel_id', env('TELE_CHANNEL_ID'));
            $botUsername = ltrim(config('telegram.bots.default.username', env('TELE_BOT_USERNAME')), '@');

            $caption = $this->generateChannelCaption($movie, $botUsername);

            Telegram::editMessageCaption([
                'chat_id' => $channelId,
                'message_id' => $movie->channel_message_id,
                'caption' => $caption,
                'parse_mode' => 'HTML',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update channel message: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete message from channel
     */
    public function deleteChannelMessage(int $messageId): bool
    {
        try {
            $channelId = config('telegram.bots.default.channel_id', env('TELE_CHANNEL_ID'));

            Telegram::deleteMessage([
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
            $response = Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => $replyMarkup,
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
            Telegram::deleteMessage([
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
}
