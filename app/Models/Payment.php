<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\CategoryVipPackage;
use Illuminate\Support\Facades\Schema;

class Payment extends Model
{
    use HasFactory;
    private static ?bool $hasCategoryVipPackages = null;

    private static function hasCategoryVipPackages(): bool
    {
        if (self::$hasCategoryVipPackages === null) {
            self::$hasCategoryVipPackages = Schema::hasTable('category_vip_packages');
        }

        return self::$hasCategoryVipPackages;
    }

    protected $fillable = [
        'user_id',
        'video_part_id',
        'category_id',
        'package',
        'package_name',
        'package_duration_days',
        'package_price',
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
        'package_duration_days' => 'integer',
        'package_price' => 'integer',
        'expired_at' => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * User who made the payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Category this payment is for
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Related video part (for analytics - which video triggered payment)
     */
    public function videoPart(): BelongsTo
    {
        return $this->belongsTo(VideoPart::class);
    }

    // ==========================================
    // STATIC HELPERS
    // ==========================================

    /**
     * Get package duration in days from config
     */
    public static function getPackageDuration(string $package, ?int $categoryId = null): int
    {
        if ($categoryId && self::hasCategoryVipPackages()) {
            $record = CategoryVipPackage::where('category_id', $categoryId)
                ->where('code', $package)
                ->first(['duration_days']);

            if ($record) {
                return (int) $record->duration_days;
            }
        }

        $packageData = config("vip.packages.{$package}");
        return (int) ($packageData['duration'] ?? 0);
    }

    /**
     * Get package price from config
     */
    public static function getPackagePrice(string $package, ?int $categoryId = null): int
    {
        if ($categoryId && self::hasCategoryVipPackages()) {
            $record = CategoryVipPackage::where('category_id', $categoryId)
                ->where('code', $package)
                ->first(['price']);

            if ($record) {
                return (int) $record->price;
            }
        }

        $packageData = config("vip.packages.{$package}");
        return (int) ($packageData['price'] ?? 0);
    }

    /**
     * Get package name from config
     */
    public static function getPackageName(string $package, ?int $categoryId = null): string
    {
        if ($categoryId && self::hasCategoryVipPackages()) {
            $record = CategoryVipPackage::where('category_id', $categoryId)
                ->where('code', $package)
                ->first(['name']);

            if ($record) {
                return (string) $record->name;
            }
        }

        $packageData = config("vip.packages.{$package}");
        return (string) ($packageData['name'] ?? ucfirst($package));
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Paid payments
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope: By category
     */
    public function scopeForCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
