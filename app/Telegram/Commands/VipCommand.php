<?php

namespace App\Telegram\Commands;

use App\Models\Category;
use App\Models\User;
use App\Services\VipService;
use App\Telegram\Commands\Traits\CategoryAware;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class VipCommand extends Command
{
    use CategoryAware;

    protected string $name = 'vip';
    protected string $description = 'Info paket VIP';

    public function handle()
    {
        $update = $this->getUpdate();
        $telegramUser = $update->getMessage()->from;

        // Find or create user
        $user = User::findOrCreateFromTelegram($telegramUser);
        $category = $this->getCategory() ?? Category::getDefault();

        // Check VIP status - per category if category is set
        if ($category) {
            if ($user->isVipForCategory($category->id)) {
                $vipExpiry = $user->getVipExpiryForCategory($category->id);
                $expiryText = $vipExpiry?->format('d M Y H:i') ?? 'N/A';

                $this->replyWithMessage([
                    'text' => "Anda sudah VIP untuk kategori <b>{$category->name}</b>\n\nBerlaku hingga: <b>{$expiryText}</b>",
                    'parse_mode' => 'HTML',
                ]);
                return;
            }
        }

        $vipService = app(VipService::class);
        $packages = $vipService->getPackages($category?->id);

        if (empty($packages)) {
            $this->replyWithMessage([
                'text' => 'Paket VIP belum tersedia. Silakan hubungi admin.',
                'parse_mode' => 'HTML',
            ]);
            return;
        }

        $categoryInfo = $category ? " untuk {$category->name}" : "";
        $message = "<b>Pilih paket VIP{$categoryInfo}:</b>\n\n";
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
