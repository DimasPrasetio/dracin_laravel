<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserCategoryVip extends Model
{
    use HasFactory;

    protected $table = 'user_category_vip';

    protected $fillable = [
        'user_id',
        'category_id',
        'vip_until',
    ];

    protected $casts = [
        'vip_until' => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * User relationship
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Category relationship
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Only active VIP (not expired)
     */
    public function scopeActive($query)
    {
        return $query->where('vip_until', '>', now());
    }

    /**
     * Scope: Only expired VIP
     */
    public function scopeExpired($query)
    {
        return $query->where('vip_until', '<=', now());
    }

    /**
     * Scope: By category
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: By user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if VIP is active
     */
    public function isActive(): bool
    {
        return $this->vip_until && $this->vip_until->isFuture();
    }

    /**
     * Check if VIP is expired
     */
    public function isExpired(): bool
    {
        return !$this->isActive();
    }

    /**
     * Get remaining days
     */
    public function getRemainingDaysAttribute(): int
    {
        if (!$this->isActive()) {
            return 0;
        }

        return (int) now()->diffInDays($this->vip_until, false);
    }

    /**
     * Get remaining hours
     */
    public function getRemainingHoursAttribute(): int
    {
        if (!$this->isActive()) {
            return 0;
        }

        return (int) now()->diffInHours($this->vip_until, false);
    }

    /**
     * Get human readable remaining time
     */
    public function getRemainingTimeAttribute(): string
    {
        if (!$this->isActive()) {
            return 'Expired';
        }

        $diff = now()->diff($this->vip_until);

        if ($diff->days > 0) {
            return $diff->days . ' hari ' . $diff->h . ' jam';
        }

        if ($diff->h > 0) {
            return $diff->h . ' jam ' . $diff->i . ' menit';
        }

        return $diff->i . ' menit';
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Get or create VIP subscription for a user and category
     */
    public static function getOrCreate(int $userId, int $categoryId): self
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'category_id' => $categoryId,
            ],
            [
                'vip_until' => now()->subDay(), // Default to expired
            ]
        );
    }

    /**
     * Check if a user has active VIP for a category
     */
    public static function hasActiveVip(int $userId, int $categoryId): bool
    {
        return static::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->where('vip_until', '>', now())
            ->exists();
    }

    /**
     * Get VIP expiry for a user in a category
     */
    public static function getVipExpiry(int $userId, int $categoryId): ?Carbon
    {
        $subscription = static::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->first();

        return $subscription?->vip_until;
    }
}
