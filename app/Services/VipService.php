<?php

namespace App\Services;

use App\Models\TelegramUser;
use App\Models\Payment;
use Carbon\Carbon;

class VipService
{
    /**
     * Get VIP packages with prices
     */
    public function getPackages(): array
    {
        return [
            ['package' => '1day', 'duration' => 1, 'price' => 2500, 'label' => '1 Hari - Rp 2.500'],
            ['package' => '3days', 'duration' => 3, 'price' => 6000, 'label' => '3 Hari - Rp 6.000'],
            ['package' => '7days', 'duration' => 7, 'price' => 10000, 'label' => '7 Hari - Rp 10.000'],
            ['package' => '30days', 'duration' => 30, 'price' => 25000, 'label' => '30 Hari - Rp 25.000'],
        ];
    }

    /**
     * Activate VIP for user
     */
    public function activateVip(TelegramUser $user, string $package): void
    {
        $duration = (int) Payment::getPackageDuration($package);

        // If user already VIP, extend from current expiry
        if ($user->isVip()) {
            $newVipUntil = Carbon::parse($user->vip_until)->addDays($duration);
        } else {
            $newVipUntil = Carbon::now()->addDays($duration);
        }

        $user->update(['vip_until' => $newVipUntil]);
    }

    /**
     * Create payment record
     */
    public function createPayment(TelegramUser $user, string $package): Payment
    {
        $amount = Payment::getPackagePrice($package);

        return Payment::create([
            'telegram_user_id' => $user->id,
            'package' => $package,
            'amount' => $amount,
            'status' => 'pending',
        ]);
    }

    /**
     * Check if user has VIP access
     */
    public function hasVipAccess(TelegramUser $user): bool
    {
        return $user->isVip();
    }

    /**
     * Get VIP info message
     */
    public function getVipInfoMessage(): string
    {
        $message = "💎 <b>Paket VIP Dracin</b>\n\n";

        foreach ($this->getPackages() as $index => $package) {
            $no = $index + 1;
            $message .= "{$no}️⃣ {$package['label']}\n";
        }

        $message .= "\n💳 <b>Pembayaran</b>\n";
        $message .= "Sistem pembayaran sedang dalam pengembangan.\n";
        $message .= "Untuk info lebih lanjut hubungi admin.\n";

        return $message;
    }
}
