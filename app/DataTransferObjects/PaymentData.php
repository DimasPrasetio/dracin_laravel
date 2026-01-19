<?php

namespace App\DataTransferObjects;

use App\Models\User;
use Illuminate\Support\Str;

class PaymentData
{
    public function __construct(
        public readonly User $user,
        public readonly string $package,
        public readonly string $paymentMethod,
        public readonly int $amount,
        public readonly string $merchantRef,
        public readonly ?int $categoryId = null,
        public readonly ?string $packageName = null,
        public readonly ?int $packageDurationDays = null,
        public readonly ?int $packagePrice = null,
    ) {}

    public static function fromRequest(array $data, User $user, int $amount, array $packageMeta = []): self
    {
        return new self(
            user: $user,
            package: $data['package'],
            paymentMethod: $data['payment_method'],
            amount: $amount,
            merchantRef: self::generateMerchantRef((string) $user->telegram_id),
            categoryId: $data['category_id'] ?? null,
            packageName: $packageMeta['name'] ?? null,
            packageDurationDays: $packageMeta['duration'] ?? null,
            packagePrice: $packageMeta['price'] ?? null,
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
            'user_id' => $this->user->id,
            'category_id' => $this->categoryId,
            'package' => $this->package,
            'package_name' => $this->packageName,
            'package_duration_days' => $this->packageDurationDays,
            'package_price' => $this->packagePrice,
            'payment_method' => $this->paymentMethod,
            'amount' => $this->amount,
            'merchant_ref' => $this->merchantRef,
        ];
    }
}
