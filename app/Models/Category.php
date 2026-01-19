<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'bot_token',
        'bot_username',
        'channel_id',
        'webhook_secret',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'bot_token',
        'webhook_secret',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            // Auto-generate slug if not provided
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }

            // Auto-generate webhook secret if not provided
            if (empty($category->webhook_secret)) {
                $category->webhook_secret = Str::random(32);
            }
        });
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Movies in this category
     */
    public function movies()
    {
        return $this->hasMany(Movie::class);
    }

    /**
     * Payments for this category
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * View logs for this category
     */
    public function viewLogs()
    {
        return $this->hasMany(ViewLog::class);
    }

    /**
     * Category admins (pivot)
     */
    public function categoryAdmins()
    {
        return $this->hasMany(CategoryAdmin::class);
    }

    /**
     * Web admins for this category
     */
    public function webAdmins()
    {
        return $this->belongsToMany(User::class, 'category_admins')
            ->withPivot('role')
            ->withTimestamps();
    }


    /**
     * VIP subscriptions for this category
     */
    public function vipSubscriptions()
    {
        return $this->hasMany(UserCategoryVip::class);
    }

    /**
     * VIP packages for this category
     */
    public function vipPackages()
    {
        return $this->hasMany(CategoryVipPackage::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if a web user is admin of this category
     */
    public function isWebAdmin(User $user): bool
    {
        // Super admin has access to all categories
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->categoryAdmins()
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists();
    }

    /**
     * Check if a web user has any role in this category
     */
    public function hasWebAccess(User $user): bool
    {
        // Super admin has access to all categories
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->categoryAdmins()
            ->where('user_id', $user->id)
            ->exists();
    }


    /**
     * Get the webhook URL for this category
     */
    public function getWebhookUrlAttribute(): string
    {
        return url("/api/telegram/webhook/{$this->slug}");
    }

    /**
     * Get bot deep link URL
     */
    public function getBotLinkAttribute(): string
    {
        $username = ltrim($this->bot_username, '@');
        return "https://t.me/{$username}";
    }

    /**
     * Find category by bot token
     */
    public static function findByBotToken(string $token): ?self
    {
        return static::where('bot_token', $token)->first();
    }

    /**
     * Find category by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Get default category
     */
    public static function getDefault(): ?self
    {
        $defaultSlug = config('vip.default_category_slug', 'dracin');
        return static::where('slug', $defaultSlug)->first();
    }

    // ==========================================
    // STATISTICS
    // ==========================================

    /**
     * Get total movies count
     */
    public function getMoviesCountAttribute(): int
    {
        return $this->movies()->count();
    }

    /**
     * Get total revenue for this category
     */
    public function getTotalRevenueAttribute(): int
    {
        return $this->payments()
            ->where('status', 'paid')
            ->sum('amount');
    }

    /**
     * Get active VIP users count for this category
     */
    public function getActiveVipCountAttribute(): int
    {
        return $this->vipSubscriptions()
            ->where('vip_until', '>', now())
            ->count();
    }
}
