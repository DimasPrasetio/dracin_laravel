<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_user_id',
        'video_part_id',
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
     * Related video part (for analytics)
     */
    public function videoPart()
    {
        return $this->belongsTo(VideoPart::class);
    }

    /**
     * Get package duration in days from config
     */
    public static function getPackageDuration(string $package): int
    {
        $packageData = config("vip.packages.{$package}");
        return $packageData['duration'] ?? 0;
    }

    /**
     * Get package price from config
     */
    public static function getPackagePrice(string $package): int
    {
        $packageData = config("vip.packages.{$package}");
        return $packageData['price'] ?? 0;
    }

    /**
     * Get package name from config
     */
    public static function getPackageName(string $package): string
    {
        $packageData = config("vip.packages.{$package}");
        return $packageData['name'] ?? ucfirst($package);
    }
}
