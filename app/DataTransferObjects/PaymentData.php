<?php

namespace App\DataTransferObjects;

use App\Models\TelegramUser;
use Illuminate\Support\Str;

class PaymentData
{
    public function __construct(
        public readonly TelegramUser $telegramUser,
        public readonly string $package,
        public readonly string $paymentMethod,
        public readonly int $amount,
        public readonly string $merchantRef,
    ) {}

    public static function fromRequest(array $data, TelegramUser $telegramUser, int $amount): self
    {
        return new self(
            telegramUser: $telegramUser,
            package: $data['package'],
            paymentMethod: $data['payment_method'],
            amount: $amount,
            merchantRef: self::generateMerchantRef($telegramUser->telegram_user_id),
        );
    }

    private static function generateMerchantRef(string $telegramUserId): string
    {
        // Add random suffix to prevent collision if multiple payments in same second
        $randomSuffix = Str::upper(Str::random(4));
        return 'DRC-' . time() . '-' . $telegramUserId . '-' . $randomSuffix;
    }

    public function toArray(): array
    {
        return [
            'telegram_user_id' => $this->telegramUser->id,
            'package' => $this->package,
            'payment_method' => $this->paymentMethod,
            'amount' => $this->amount,
            'merchant_ref' => $this->merchantRef,
        ];
    }
}
