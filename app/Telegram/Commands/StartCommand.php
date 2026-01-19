<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use App\Models\Category;
use App\Models\User;
use App\Models\VideoPart;
use App\Models\ViewLog;
use App\Services\TelegramService;
use App\Telegram\Commands\Traits\CategoryAware;

class StartCommand extends Command
{
    use CategoryAware;

    protected string $name = 'start';
    protected string $description = 'Start command';

    public function handle()
    {
        $update = $this->getUpdate();
        $telegramUser = $update->getMessage()->from;
        $chatId = $update->getMessage()->chat->id;
        $text = $update->getMessage()->text;

        // Find or create user
        $user = User::findOrCreateFromTelegram($telegramUser);

        // Check if deep link parameter exists
        if (preg_match('/^\/start\s+(.+)$/', $text, $matches)) {
            $videoId = $matches[1];
            $this->handleVideoRequest($user, $chatId, $videoId);
        } else {
            $this->sendWelcomeMessage($chatId, $user);
        }
    }

    private function handleVideoRequest(User $user, int $chatId, string $videoId)
    {
        $videoPart = VideoPart::with('movie.category')->where('video_id', $videoId)->first();

        if (!$videoPart) {
            $this->replyWithMessage([
                'text' => 'Video tidak ditemukan.',
            ]);
            return;
        }

        // Determine category from video's movie
        $movieCategory = $videoPart->movie->category;

        // Check VIP access - per category if movie has category, otherwise global
        $hasVipAccess = false;
        $vipUntil = 'Tidak aktif';

        if ($videoPart->is_vip) {
            $vipCategory = $movieCategory ?? Category::getDefault();

            if ($vipCategory) {
                $hasVipAccess = $user->isVipForCategory($vipCategory->id);
                if ($hasVipAccess) {
                    $vipData = $user->vipSubscriptions()
                        ->where('category_id', $vipCategory->id)
                        ->where('vip_until', '>', now())
                        ->first();
                    $vipUntil = $vipData?->vip_until?->format('d M Y H:i') ?? 'N/A';
                }
            } else {
                $vipUntil = 'N/A';
                $hasVipAccess = false;
            }

            if (!$hasVipAccess) {
                $categoryInfo = $movieCategory ? " untuk kategori {$movieCategory->name}" : "";
                $message = "<b>Video ini memerlukan akses VIP{$categoryInfo}</b>\n\n";
                $message .= "Film: <b>{$videoPart->movie->title}</b>\n";
                $message .= "Part: {$videoPart->part_number}\n";
                if ($movieCategory) {
                    $message .= "Kategori: {$movieCategory->name}\n";
                }
                $message .= "\nStatus VIP Anda{$categoryInfo}: {$vipUntil}\n\n";
                $message .= "Untuk mengakses video VIP, ketik /vip untuk info upgrade.";

                $this->replyWithMessage([
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]);
                return;
            }
        }

        // Send video using telegram service with category context
        $telegramService = $this->getTelegramService();
        $caption = "<b>{$videoPart->movie->title}</b>\nPart {$videoPart->part_number}";

        $telegramService->sendVideo($chatId, $videoPart->file_id, $caption);

        // Log video view with category
        ViewLog::create([
            'user_id' => $user->id,
            'video_part_id' => $videoPart->id,
            'category_id' => $movieCategory?->id,
            'is_vip' => $videoPart->is_vip,
            'source' => 'bot',
            'device' => 'telegram',
            'ip_address' => null,
        ]);
    }

    private function sendWelcomeMessage(int $chatId, User $user)
    {
        $category = $this->getCategory() ?? Category::getDefault();

        if ($category) {
            $isVip = $user->isVipForCategory($category->id);
            if ($isVip) {
                $vipExpiry = $user->getVipExpiryForCategory($category->id);
                $expiryText = $vipExpiry?->format('d M Y H:i') ?? 'N/A';
                $vipStatus = "VIP aktif ({$category->name}) hingga {$expiryText}";
            } else {
                $vipStatus = "Belum VIP untuk {$category->name}";
            }

            $botName = $category->name;
            $channelInfo = $category->channel_id
                ? "Join channel {$category->channel_id} untuk update film terbaru!"
                : "Nikmati koleksi film {$category->name}!";
        } else {
            $vipStatus = "Belum VIP";
            $botName = "Dracin Bot";
            $channelInfo = "Join channel @dracin_hd untuk update film terbaru!";
        }

        $message = "<b>Selamat datang di {$botName}!</b>\n\n";
        $message .= "Status: {$vipStatus}\n\n";
        $message .= "<b>Commands:</b>\n";
        $message .= "/vip - Info paket VIP\n";
        $message .= "/help - Bantuan\n\n";
        $message .= $channelInfo;

        $this->replyWithMessage([
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);
    }
}

