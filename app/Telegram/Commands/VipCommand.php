<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use App\Services\VipService;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class VipCommand extends Command
{
    protected string $name = 'vip';
    protected string $description = 'Info paket VIP';

    public function handle()
    {
        $update = $this->getUpdate();
        $telegramUser = $update->getMessage()->from;

        // Find or create user
        $user = TelegramUser::findOrCreateFromTelegram($telegramUser);

        if ($user->isVip()) {
            $this->replyWithMessage([
                'text' => 'Anda sudah VIP dan akan expired pada ' . $user->vip_until->format('d M Y H:i'),
                'parse_mode' => 'HTML',
            ]);
            return;
        }

        $vipService = app(VipService::class);
        $packages = $vipService->getPackages();

        $message = "<b>Pilih paket VIP:</b>\n\n";
        foreach ($packages as $index => $package) {
            $no = $index + 1;
            $message .= "{$no}. {$package['label']}\n";
        }
        $expiryMinutes = (int) config('vip.payment.expiry_minutes', 60);
        $message .= "\nQRIS berlaku {$expiryMinutes} menit setelah dibuat.";

        $keyboard = Keyboard::make()->inline();
        foreach ($packages as $package) {
            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => $package['label'],
                    'callback_data' => 'vip_package:' . $package['package'],
                ]),
            ]);
        }

        $this->replyWithMessage([
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => $keyboard,
        ]);
    }
}
