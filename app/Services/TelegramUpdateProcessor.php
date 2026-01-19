<?php

namespace App\Services;

use App\DataTransferObjects\PaymentData;
use App\Jobs\ClearChatHistory;
use App\Exceptions\TripayException;
use App\Models\BotState;
use App\Models\Category;
use App\Models\User;
use App\Repositories\PaymentRepository;
use App\Services\TripayService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class TelegramUpdateProcessor
{
    protected TelegramService $telegramService;
    protected VideoService $videoService;
    protected TripayService $tripayService;
    protected PaymentRepository $paymentRepository;
    protected ?Category $category = null;
    protected ?Api $bot = null;

    public function __construct(
        TelegramService $telegramService,
        VideoService $videoService,
        TripayService $tripayService,
        PaymentRepository $paymentRepository,
    )
    {
        $this->telegramService = $telegramService;
        $this->videoService = $videoService;
        $this->tripayService = $tripayService;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Set category context for multi-bot support
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        $this->bot = null;

        // Also set category on telegram service
        if ($category) {
            $this->telegramService->setCategory($category);
            app()->instance('telegram.category', $category);
        } else {
            app()->forgetInstance('telegram.category');
        }

        return $this;
    }

    /**
     * Get current category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Get the bot API instance for current category
     */
    protected function getBot(): Api
    {
        if ($this->category && $this->category->bot_token) {
            if (!$this->bot) {
                $this->bot = new Api($this->category->bot_token);

                // Register commands to this bot instance
                $commands = config('telegram.bots.default.commands', []);
                foreach ($commands as $commandClass) {
                    $this->bot->addCommand($commandClass);
                }
            }
            return $this->bot;
        }

        return Telegram::bot();
    }

    /**
     * Process a single update from Telegram
     *
     * @param Update $update The Telegram update object
     * @param Category|null $category Optional category context for multi-bot support
     */
    public function processUpdate(Update $update, ?Category $category = null): void
    {
        try {
            // Set category context if provided
            if ($category) {
                $this->setCategory($category);
            }

            // Handle callback query
            if ($update->getCallbackQuery()) {
                $this->handleCallbackQuery($update);
                return;
            }

            // Check if this is a command
            $commandHandled = false;
            if ($update->getMessage() && isset($update->getMessage()->text)) {
                $text = $update->getMessage()->text;
                if (str_starts_with($text, '/')) {
                    // For category-specific bots, process command with their API
                    if ($this->category) {
                        $this->getBot()->processCommand($update);
                    } else {
                        Telegram::processCommand($update);
                    }
                    $commandHandled = true;
                    Log::info('Command processed', [
                        'command' => $text,
                        'category' => $this->category?->name ?? 'default'
                    ]);
                }
            }

            // If not a command, process as regular message
            if (!$commandHandled && $update->getMessage()) {
                $this->handleMessage($update);
            }
        } catch (\Exception $e) {
            Log::error('Update processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'category' => $this->category?->name ?? 'default'
            ]);
        }
    }

    /**
     * Handle callback query from inline buttons
     */
    private function handleCallbackQuery(Update $update): void
    {
        try {
            $callbackQuery = $update->getCallbackQuery();
            $telegramUserId = $callbackQuery->from->id;
            $data = $callbackQuery->data;
            $chatId = $callbackQuery->message->chat->id;

            Log::info('Callback query received', [
                'user_id' => $telegramUserId,
                'user_name' => $callbackQuery->from->first_name,
                'data' => $data,
                'category' => $this->category?->name ?? 'default',
            ]);

            // Answer callback query to remove loading state using appropriate bot
            $this->getBot()->answerCallbackQuery([
                'callback_query_id' => $callbackQuery->id,
            ]);

            if (str_starts_with($data, 'vip_package:')) {
                $package = substr($data, strlen('vip_package:'));
                $this->handleVipPackageSelection($callbackQuery, $package, $chatId);
            }
        } catch (\Exception $e) {
            Log::error('Callback query handler error', [
                'error' => $e->getMessage(),
                'category' => $this->category?->name ?? 'default',
            ]);
        }
    }

    /**
     * Handle VIP package selection from inline keyboard
     */
    private function handleVipPackageSelection($callbackQuery, string $package, int $chatId): void
    {
        $telegramUser = User::findOrCreateFromTelegram($callbackQuery->from);
        $category = $this->category ?? Category::getDefault();

        if (!$category) {
            $this->telegramService->sendMessage(
                $chatId,
                'Kategori tidak ditemukan. Silakan hubungi admin.'
            );
            return;
        }

        if ($category && $telegramUser->isVipForCategory($category->id)) {
            $vipExpiry = $telegramUser->getVipExpiryForCategory($category->id);
            $expiryText = $vipExpiry?->format('d M Y H:i') ?? 'N/A';
            $categoryName = $category->name ?? 'default';

            $this->telegramService->sendMessage(
                $chatId,
                "Anda sudah VIP untuk kategori {$categoryName}.\nBerlaku hingga: {$expiryText}"
            );
            return;
        }

        $paymentStatus = $this->tripayService->isAvailable();
        if (!$paymentStatus['available']) {
            $this->telegramService->sendMessage(
                $chatId,
                $paymentStatus['description'] ?: 'Tripay belum dikonfigurasi. Silakan hubungi admin.'
            );
            return;
        }

        $packageData = $this->tripayService->getPackageDetails($package, $category->id);
        if (!$packageData) {
            $this->telegramService->sendMessage($chatId, 'Paket VIP tidak valid.');
            return;
        }

        try {
            $existingPayment = $this->paymentRepository->findReusablePayment(
                $telegramUser->id,
                $package,
                'QRIS',
                $category->id,
                (int) ($packageData['price'] ?? 0)
            );

            if ($existingPayment && $existingPayment->tripay_qr_string) {
                $qrPath = $this->generateQrImage(
                    $existingPayment->tripay_qr_string,
                    $existingPayment->tripay_reference
                );
                if (!$qrPath) {
                    $this->telegramService->sendMessage(
                        $chatId,
                        'Gagal menyiapkan QRIS. Silakan coba lagi.'
                    );
                    return;
                }
                $expiredAt = $existingPayment->expired_at?->format('d M Y H:i');

                $basePrice = $packageData['price'];
                $feeAmount = $this->getQrisFeeAmount($basePrice);
                $totalAmount = $basePrice + $feeAmount;

                $caption = "<b>QRIS VIP {$packageData['name']}</b>\n\n";
                $caption .= "<b>Detail Pembayaran:</b>\n";
                $caption .= "Harga Paket: Rp " . number_format($basePrice, 0, ',', '.') . "\n";
                if ($feeAmount > 0) {
                    $caption .= "Biaya Layanan: Rp " . number_format($feeAmount, 0, ',', '.') . "\n";
                }
                $caption .= "----------------\n";
                $caption .= "<b>Total Bayar: Rp " . number_format($totalAmount, 0, ',', '.') . "</b>\n\n";
                if ($expiredAt) {
                    $caption .= "Berlaku sampai: {$expiredAt}\n\n";
                }
                $caption .= "QRIS ini masih aktif. Silakan scan untuk pembayaran.";

                $this->sendQrImage($chatId, $qrPath, $caption);

                return;
            }

            $paymentData = PaymentData::fromRequest([
                'package' => $package,
                'payment_method' => 'QRIS',
                'category_id' => $category->id,
            ], $telegramUser, (int) ($packageData['price'] ?? 0), $packageData);

            $result = $this->tripayService->createPayment($paymentData);

            $qrString = $result['qr_string'] ?? null;
            $payment = $result['payment'] ?? null;

            if (!$qrString) {
                $this->telegramService->sendMessage(
                    $chatId,
                    'Gagal membuat QRIS. Silakan coba lagi.'
                );
                return;
            }

            $qrPath = $this->generateQrImage($qrString, $payment?->tripay_reference ?? null);
            if (!$qrPath) {
                $this->telegramService->sendMessage(
                    $chatId,
                    'Gagal menyiapkan QRIS. Silakan coba lagi.'
                );
                return;
            }
            $expiredAt = $payment?->expired_at?->format('d M Y H:i');

            $basePrice = $packageData['price'];
            $feeAmount = $this->getQrisFeeAmount($basePrice);
            $totalAmount = $basePrice + $feeAmount;

            $caption = "<b>QRIS VIP {$packageData['name']}</b>\n\n";
            $caption .= "<b>Detail Pembayaran:</b>\n";
            $caption .= "Harga Paket: Rp " . number_format($basePrice, 0, ',', '.') . "\n";
            if ($feeAmount > 0) {
                $caption .= "Biaya Layanan: Rp " . number_format($feeAmount, 0, ',', '.') . "\n";
            }
            $caption .= "----------------\n";
            $caption .= "<b>Total Bayar: Rp " . number_format($totalAmount, 0, ',', '.') . "</b>\n\n";
            if ($expiredAt) {
                $caption .= "Berlaku sampai: {$expiredAt}\n\n";
            }
            $caption .= "Silakan scan QRIS ini untuk pembayaran.";

            $this->sendQrImage($chatId, $qrPath, $caption);

        } catch (TripayException $e) {
            Log::error('Failed to create Tripay payment', [
                'error' => $e->getMessage(),
                'package' => $package,
                'telegram_id' => $telegramUser->telegram_id,
            ]);
            $this->telegramService->sendMessage(
                $chatId,
                'Gagal membuat pembayaran. Silakan coba lagi.'
            );
        } catch (\Exception $e) {
            Log::error('Unexpected error when creating VIP payment', [
                'error' => $e->getMessage(),
            ]);
            $this->telegramService->sendMessage(
                $chatId,
                'Terjadi kesalahan sistem. Silakan coba lagi.'
            );
        }
    }

    /**
     * Generate QR image and return file path
     */
    private function generateQrImage(string $qrString, ?string $reference): ?string
    {
        $dir = storage_path('app/qris');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fileName = ($reference ? $reference : ('qris-' . time())) . '.png';
        $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;

        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'scale' => 6,
        ]);

        try {
            (new QRCode($options))->render($qrString, $filePath);
        } catch (\Throwable $e) {
            Log::error('Failed to generate QR code image', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            return null;
        }

        return $filePath;
    }

    /**
     * Send QR image
     * Note: File cleanup is handled by scheduled task, not immediately
     */
    private function sendQrImage(int $chatId, string $filePath, string $caption): void
    {
        if (empty($filePath) || !is_file($filePath)) {
            Log::warning('QR image path missing or invalid', [
                'chat_id' => $chatId,
                'path' => $filePath,
            ]);
            $this->telegramService->sendMessage(
                $chatId,
                'Gagal memuat QRIS. Silakan coba lagi.'
            );
            return;
        }

        // Use appropriate bot for sending photo
        // Note: QR payment images should NOT be protected so users can save/screenshot for payment
        $this->getBot()->sendPhoto([
            'chat_id' => $chatId,
            'photo' => InputFile::create($filePath),
            'caption' => $caption,
            'parse_mode' => 'HTML',
        ]);

        // File will be cleaned up by scheduled task (storage:cleanup-temp)
        // This ensures Telegram has time to download the image
    }

    private function getQrisFeeAmount(int $baseAmount): int
    {
        try {
            $channels = $this->tripayService->getPaymentChannels();
            $qrisChannel = collect($channels)->firstWhere('code', 'QRIS');

            if (!$qrisChannel || !isset($qrisChannel['fee_customer'])) {
                return 0;
            }

            $feeFlat = $qrisChannel['fee_customer']['flat'] ?? 0;
            $feePercent = $qrisChannel['fee_customer']['percent'] ?? 0;

            return (int) ($feeFlat + ($baseAmount * $feePercent / 100));
        } catch (TripayException $e) {
            Log::warning('Failed to fetch QRIS fee from Tripay', [
                'error' => $e->getMessage(),
            ]);
            return 0;
        } catch (\Exception $e) {
            Log::warning('Unexpected error while calculating QRIS fee', [
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Handle regular message (non-command)
     */
    private function handleMessage(Update $update): void
    {
        $message = $update->getMessage();
        $telegramUserId = $message->from->id;
        $chatId = $message->chat->id;

        // Get message type
        $messageType = 'text';
        if (isset($message->photo)) {
            $messageType = 'photo';
        } elseif (isset($message->video)) {
            $messageType = 'video';
        }

        Log::info('Message received', [
            'user_id' => $telegramUserId,
            'user_name' => $message->from->first_name,
            'type' => $messageType
        ]);

        // Check if user in conversation state
        $botState = BotState::getState($telegramUserId);

        if ($botState) {
            $this->handleConversation($botState, $message, $chatId);
        }
    }

    /**
     * Handle conversation flow based on bot state
     */
    private function handleConversation(BotState $botState, $message, int $chatId): void
    {
        try {
            // Security: Only admin and moderator can use conversation (add movie flow)
            $authService = app(\App\Services\TelegramAuthService::class);
            if (!$authService->canAddMovies($botState->telegram_id)) {
                Log::warning('Unauthorized user attempting conversation', [
                    'user_id' => $botState->telegram_id
                ]);
                BotState::clearState($botState->telegram_id);
                return;
            }

            $state = $botState->state;

            switch ($state) {
                case 'AWAITING_THUMBNAIL':
                    $this->handleThumbnailUpload($botState, $message, $chatId);
                    break;

                case 'AWAITING_TITLE':
                    $this->handleTitleInput($botState, $message, $chatId);
                    break;

                case 'AWAITING_PART_COUNT':
                    $this->handlePartCountInput($botState, $message, $chatId);
                    break;

                case 'AWAITING_VIDEO_PART':
                    $this->handleVideoPartUpload($botState, $message, $chatId);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Conversation handler error', [
                'state' => $botState->state ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->telegramService->sendMessage(
                $chatId,
                "<b>Terjadi kesalahan.</b>

Gunakan /addmovie untuk mulai ulang."
            );

            BotState::clearState($botState->telegram_id);
        }
    }

    /**
     * Handle thumbnail upload step
     */
    private function handleThumbnailUpload(BotState $botState, $message, int $chatId): void
    {
        $fileId = null;

        if (isset($message->photo) && is_iterable($message->photo)) {
            $lastPhoto = null;
            foreach ($message->photo as $photo) {
                $lastPhoto = $photo;
            }
            $fileId = $lastPhoto->file_id ?? null;
        } elseif (isset($message->document) && isset($message->document->mime_type)) {
            $mimeType = $message->document->mime_type;
            if (str_starts_with($mimeType, 'image/')) {
                $fileId = $message->document->file_id ?? null;
            }
        } elseif (isset($message->document) && isset($message->document->file_id)) {
            $fileName = strtolower($message->document->file_name ?? '');
            $hasImageExtension = (bool) preg_match('/\.(jpg|jpeg|png|webp|gif)$/', $fileName);
            $hasThumb = isset($message->document->thumb);
            if ($hasImageExtension || $hasThumb) {
                $fileId = $message->document->file_id ?? null;
            }
        }

        if (!$fileId) {
            $this->telegramService->sendMessage($chatId, 'Mohon kirim foto untuk thumbnail.');
            return;
        }

        // Save to state
        $data = $botState->data ?? [];
        $data['thumbnail_file_id'] = $fileId;
        $data = $this->addMessageId($data, $message->message_id);

        $messageId = $this->telegramService->sendMessage(
            $chatId,
            'Thumbnail diterima. Silakan masukkan judul film.'
        );

        $data = $this->addMessageId($data, $messageId);
        BotState::setState($botState->telegram_id, 'AWAITING_TITLE', $data);

        Log::info('Thumbnail uploaded', ['user_id' => $botState->telegram_id]);
    }

    /**
     * Handle title input step
     */
    private function handleTitleInput(BotState $botState, $message, int $chatId): void
    {
        if (!isset($message->text) || empty(trim($message->text))) {
            $this->telegramService->sendMessage($chatId, 'Mohon kirim teks untuk judul.');
            return;
        }

        $title = trim($message->text);

        // Save to state
        $data = $botState->data ?? [];
        $data['title'] = $title;
        $data = $this->addMessageId($data, $message->message_id);

        $messageId = $this->telegramService->sendMessage(
            $chatId,
            "Judul diterima.
Judul: {$title}
Silakan masukkan jumlah part (1-50)."
        );

        $data = $this->addMessageId($data, $messageId);
        BotState::setState($botState->telegram_id, 'AWAITING_PART_COUNT', $data);

        Log::info('Title set', ['title' => $title]);
    }

    /**
     * Handle part count input step
     */
    private function handlePartCountInput(BotState $botState, $message, int $chatId): void
    {
        if (!isset($message->text) || !is_numeric(trim($message->text))) {
            $this->telegramService->sendMessage($chatId, 'Mohon kirim angka untuk jumlah part.');
            return;
        }

        $partCount = (int) trim($message->text);

        if ($partCount < 1 || $partCount > 50) {
            $this->telegramService->sendMessage($chatId, 'Jumlah part harus antara 1-50.');
            return;
        }

        // Save to state
        $data = $botState->data ?? [];
        $data['total_parts'] = $partCount;
        $data['current_part'] = 1;
        $data['video_parts'] = [];
        $data = $this->addMessageId($data, $message->message_id);

        $messageId = $this->telegramService->sendMessage(
            $chatId,
            "Jumlah part diterima. Total part: {$partCount}.
Silakan masukkan Part 1/{$partCount}."
        );

        $data = $this->addMessageId($data, $messageId);
        BotState::setState($botState->telegram_id, 'AWAITING_VIDEO_PART', $data);

        Log::info('Part count set', ['count' => $partCount]);
    }

    /**
     * Handle video part upload step
     */
    private function handleVideoPartUpload(BotState $botState, $message, int $chatId): void
    {
        $data = $botState->data ?? [];
        $currentPart = $data['current_part'] ?? 1;
        $totalParts = $data['total_parts'] ?? 1;

        if (!isset($message->video) || !$message->video) {
            $this->telegramService->sendMessage(
                $chatId,
                "Mohon kirim video untuk Part {$currentPart}/{$totalParts}."
            );
            return;
        }

        $video = $message->video;

        if (!isset($video->file_id) || !$video->file_id) {
            $this->telegramService->sendMessage(
                $chatId,
                "Gagal mendapatkan file_id video. Silakan kirim ulang Part {$currentPart}/{$totalParts}."
            );
            return;
        }

        $fileId = $video->file_id;
        $duration = $video->duration ?? null;
        $fileSize = $video->file_size ?? null;

        // Validate file size (Telegram limit is 2GB, warn if > 1.5GB)
        if ($fileSize && $fileSize > 1610612736) { // 1.5GB in bytes
            $this->telegramService->sendMessage(
                $chatId,
                "Video Part {$currentPart}/{$totalParts} berukuran besar (" . round($fileSize / 1048576, 2) . " MB). Pastikan ukuran tidak melebihi 2GB."
            );
        }

        $data = $this->addMessageId($data, $message->message_id);

        // Save video part
        $data['video_parts'][] = [
            'file_id' => $fileId,
            'duration' => $duration,
            'file_size' => $fileSize,
        ];

        $data['current_part'] = $currentPart + 1;

        // Check if all parts uploaded
        if ($currentPart >= $totalParts) {
            $messageId = $this->telegramService->sendMessage(
                $chatId,
                "Video Part {$currentPart}/{$totalParts} (END) diterima. Memproses dan posting ke channel..."
            );

            $data = $this->addMessageId($data, $messageId);

            $this->createMovieFromState($data, $chatId, $botState->telegram_id);
            BotState::clearState($botState->telegram_id);

            Log::info('Movie created', ['title' => $data['title']]);
        } else {
            $nextPart = $currentPart + 1;
            $isNextPartEnd = $nextPart === $totalParts;
            $nextPartLabel = $isNextPartEnd ? ' (END)' : '';

            $progress = str_repeat('#', $currentPart) . str_repeat('-', max($totalParts - $currentPart, 0));

            $messageId = $this->telegramService->sendMessage(
                $chatId,
                "Video Part {$currentPart}/{$totalParts} diterima. [{$progress}] Silakan masukkan Part {$nextPart}/{$totalParts}{$nextPartLabel}."
            );

            $data = $this->addMessageId($data, $messageId);
            BotState::setState($botState->telegram_id, 'AWAITING_VIDEO_PART', $data);

            Log::info('Video part uploaded', ['part' => $currentPart, 'total' => $totalParts]);
        }
    }

    /**
     * Create movie from collected state data
     */
    private function createMovieFromState(array $data, int $chatId, int $telegramUserId): void
    {
        try {
            // Get category_id from state data (set by AddMovieCommand) or from processor context
            $categoryId = $data['category_id'] ?? $this->category?->id;

            // Set category on video service for channel posting
            if ($categoryId && !$this->category) {
                // Load category from ID if not set in context
                $category = Category::find($categoryId);
                if ($category) {
                    $this->videoService->setCategory($category);
                }
            } elseif ($this->category) {
                $this->videoService->setCategory($this->category);
            }

            $creatorId = User::where('telegram_id', $telegramUserId)->value('id');

            $movie = $this->videoService->createMovie([
                'title' => $data['title'],
                'thumbnail_file_id' => $data['thumbnail_file_id'],
                'total_parts' => $data['total_parts'],
                'video_parts' => $data['video_parts'],
                'created_by' => $creatorId,
                'category_id' => $categoryId,
            ]);

            // Send success message
            $messageId = $this->telegramService->sendMessage(
                $chatId,
                "Film baru telah ditambahkan.
Judul: {$movie->title}
Total: {$movie->total_parts} part
Riwayat ini akan dihapus dalam 3 detik..."
            );

            $data = $this->addMessageId($data, $messageId);

            ClearChatHistory::dispatch(
                $chatId,
                $telegramUserId,
                $data['message_ids'] ?? [],
                $movie->title,
                $categoryId
            )->delay(now()->addSeconds(3));
        } catch (\Exception $e) {
            Log::error('Failed to create movie', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->telegramService->sendMessage(
                $chatId,
                "<b>Gagal menyimpan film.</b>

" .
                'Error: ' . $e->getMessage() . "

" .
                'Silakan coba lagi dengan /addmovie'
            );
        }
    }

    /**
     * Add message ID to tracking array
     */
    private function addMessageId(array $data, ?int $messageId): array
    {
        if (!$messageId) {
            return $data;
        }

        if (!isset($data['message_ids'])) {
            $data['message_ids'] = [];
        }

        $data['message_ids'][] = $messageId;

        // Limit to last 100 messages to prevent memory leak
        if (count($data['message_ids']) > 100) {
            $data['message_ids'] = array_slice($data['message_ids'], -100);
        }

        return $data;
    }
}
