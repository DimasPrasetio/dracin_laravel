<?php

namespace App\Services;

use App\Models\TelegramUser;
use App\Models\Payment;
use Carbon\Carbon;

class VipService
{
    /**
     * Get VIP packages with prices from config
     */
    public function getPackages(): array
    {
        $packages = config('vip.packages', []);
        $result = [];

        foreach ($packages as $key => $package) {
            $result[] = [
                'package' => $key,
                'duration' => $package['duration'],
                'price' => $package['price'],
                'label' => $package['name'] . ' - Rp ' . number_format($package['price'], 0, ',', '.'),
            ];
        }

        return $result;
    }

    /**
     * Activate VIP for user
     *
     * @throws \InvalidArgumentException if package is invalid
     * @throws \LogicException if user is already VIP
     */
    public function activateVip(TelegramUser $user, string $package): void
    {
        $packageData = config("vip.packages.{$package}");

        if (!$packageData) {
            throw new \InvalidArgumentException("Invalid package: {$package}");
        }

        // Guard clause - should never activate VIP for already-VIP users
        // This check should be done at controller level before calling this method
        if ($user->isVip()) {
            throw new \LogicException(
                'Cannot activate VIP for user who is already VIP. ' .
                'Current VIP expires at: ' . $user->vip_until->format('Y-m-d H:i:s')
            );
        }

        $duration = (int) $packageData['duration'];

        // Always start from now - this is a new purchase, not an extension
        $newVipUntil = Carbon::now()->addDays($duration);

        $user->update(['vip_until' => $newVipUntil]);
    }

    /**
     * Create payment record
     */
    public function createPayment(TelegramUser $user, string $package): Payment
    {
        $packageData = config("vip.packages.{$package}");

        if (!$packageData) {
            throw new \InvalidArgumentException("Invalid package: {$package}");
        }

        $amount = $packageData['price'];

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
