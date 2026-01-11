<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use App\Models\TelegramUser;
use App\Models\VideoPart;
use App\Services\TelegramService;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Start command';

    public function handle()
    {
        $update = $this->getUpdate();
        $telegramUser = $update->getMessage()->from;
        $chatId = $update->getMessage()->chat->id;
        $text = $update->getMessage()->text;

        // Find or create user
        $user = TelegramUser::findOrCreateFromTelegram($telegramUser);

        // Check if deep link parameter exists
        if (preg_match('/^\/start\s+(.+)$/', $text, $matches)) {
            $videoId = $matches[1];
            $this->handleVideoRequest($user, $chatId, $videoId);
        } else {
            $this->sendWelcomeMessage($chatId, $user);
        }
    }

    private function handleVideoRequest(TelegramUser $user, int $chatId, string $videoId)
    {
        $videoPart = VideoPart::with('movie')->where('video_id', $videoId)->first();

        if (!$videoPart) {
            $this->replyWithMessage([
                'text' => '❌ Video tidak ditemukan.',
            ]);
            return;
        }

        // Check VIP access
        if ($videoPart->is_vip && !$user->isVip()) {
            $vipUntil = $user->vip_until ? $user->vip_until->format('d M Y H:i') : 'Tidak aktif';

            $this->replyWithMessage([
                'text' => "⚠️ <b>Video ini memerlukan akses VIP</b>\n\n" .
                    "📽️ Film: <b>{$videoPart->movie->title}</b>\n" .
                    "📀 Part: {$videoPart->part_number}\n\n" .
                    "Status VIP Anda: {$vipUntil}\n\n" .
                    "Untuk mengakses video VIP, ketik /vip untuk info upgrade.",
                'parse_mode' => 'HTML',
            ]);
            return;
        }

        // Send video
        $telegramService = app(TelegramService::class);
        $caption = "📽️ <b>{$videoPart->movie->title}</b>\n📀 Part {$videoPart->part_number}";

        $telegramService->sendVideo($chatId, $videoPart->file_id, $caption);
    }

    private function sendWelcomeMessage(int $chatId, TelegramUser $user)
    {
        $vipStatus = $user->isVip()
            ? "✅ VIP Aktif hingga " . $user->vip_until->format('d M Y H:i')
            : "❌ Belum VIP";

        $message = "🎬 <b>Selamat datang di Dracin Bot!</b>\n\n";
        $message .= "Status: {$vipStatus}\n\n";
        $message .= "📋 <b>Commands:</b>\n";
        $message .= "/vip - Info paket VIP\n";
        $message .= "/help - Bantuan\n\n";
        $message .= "Join channel @dracin_hd untuk update film terbaru!";

        $this->replyWithMessage([
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);
    }
}
