<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_user_id',
        'package',
        'amount',
        'payment_method',
        'status',
        'expired_at',
        'tripay_reference',
        'tripay_merchant_ref',
        'tripay_payment_method',
        'tripay_payment_name',
        'tripay_pay_url',
        'tripay_qr_string',
        'tripay_checkout_url',
    ];

    protected $casts = [
        'amount' => 'integer',
        'expired_at' => 'datetime',
    ];

    /**
     * User relationship
     */
    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class);
    }

    /**
     * Get package duration in days
     */
    public static function getPackageDuration(string $package): int
    {
        return match ($package) {
            '1day' => 1,
            '3days' => 3,
            '7days' => 7,
            '30days' => 30,
            default => 0,
        };
    }

    /**
     * Get package price
     */
    public static function getPackagePrice(string $package): int
    {
        return match ($package) {
            '1day' => 2500,
            '3days' => 6000,
            '7days' => 10000,
            '30days' => 25000,
            default => 0,
        };
    }
}
