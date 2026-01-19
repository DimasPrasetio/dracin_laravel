<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

/**
 * User Model - Single Source of Truth for all users
 *
 * This model handles both web admin users and telegram bot users.
 * Telegram users are identified by telegram_id field.
 *
 * Role Hierarchy:
 * - super_admin: Full access to all categories and system settings
 * - admin: Can be assigned to manage specific categories
 * - moderator: Can be assigned to add content to specific categories
 * - user: Regular user (telegram bot users default to this)
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // ==========================================
    // ROLE CONSTANTS
    // ==========================================

    public const ROLE_USER = 'user';
    public const ROLE_MODERATOR = 'moderator';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPER_ADMIN = 'super_admin';

    // ==========================================
    // MODEL CONFIGURATION
    // ==========================================

    protected $fillable = [
        'telegram_id',
        'username',
        'name',
        'first_name',
        'last_name',
        'phone',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'telegram_id' => 'integer',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Category assignments for this user (admin/moderator roles)
     */
    public function categoryAssignments(): HasMany
    {
        return $this->hasMany(CategoryAdmin::class);
    }

    /**
     * Categories where user has any role
     */
    public function assignedCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_admins')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * VIP subscriptions per category
     */
    public function vipSubscriptions(): HasMany
    {
        return $this->hasMany(UserCategoryVip::class);
    }

    /**
     * Payments made by this user
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * View logs for this user
     */
    public function viewLogs(): HasMany
    {
        return $this->hasMany(ViewLog::class);
    }

    // ==========================================
    // ROLE CHECK METHODS
    // ==========================================

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if user is admin or super admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    /**
     * Check if user is moderator
     */
    public function isModerator(): bool
    {
        return $this->role === self::ROLE_MODERATOR;
    }

    /**
     * Check if user is staff (admin, moderator, or super_admin)
     */
    public function isStaff(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            self::ROLE_MODERATOR,
            self::ROLE_SUPER_ADMIN,
        ]);
    }

    /**
     * Check if user is regular user
     */
    public function isRegularUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Check if user registered via Telegram
     */
    public function isTelegramUser(): bool
    {
        return $this->telegram_id !== null;
    }

    /**
     * Check if user can login via web (has email and password)
     */
    public function canWebLogin(): bool
    {
        return $this->email !== null && $this->password !== null;
    }

    // ==========================================
    // CATEGORY-SPECIFIC PERMISSION METHODS
    // ==========================================

    /**
     * Check if user has any access to a category
     */
    public function hasAccessToCategory(int $categoryId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->categoryAssignments()
            ->where('category_id', $categoryId)
            ->exists();
    }

    /**
     * Check if user is admin for a specific category
     */
    public function isAdminForCategory(int $categoryId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->categoryAssignments()
            ->where('category_id', $categoryId)
            ->where('role', CategoryAdmin::ROLE_ADMIN)
            ->exists();
    }

    /**
     * Check if user can add movies to a specific category
     */
    public function canAddMoviesForCategory(int $categoryId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->categoryAssignments()
            ->where('category_id', $categoryId)
            ->whereIn('role', [CategoryAdmin::ROLE_ADMIN, CategoryAdmin::ROLE_MODERATOR])
            ->exists();
    }

    /**
     * Check if user can edit movies in a specific category
     */
    public function canEditMoviesForCategory(int $categoryId): bool
    {
        return $this->isAdminForCategory($categoryId);
    }

    /**
     * Check if user can delete movies in a specific category
     */
    public function canDeleteMoviesForCategory(int $categoryId): bool
    {
        return $this->isAdminForCategory($categoryId);
    }

    /**
     * Get user's role in a specific category
     */
    public function getRoleForCategory(int $categoryId): ?string
    {
        if ($this->isSuperAdmin()) {
            return 'super_admin';
        }

        $assignment = $this->categoryAssignments()
            ->where('category_id', $categoryId)
            ->first();

        return $assignment?->role;
    }

    /**
     * Get all categories user has access to
     */
    public function getAccessibleCategories()
    {
        if ($this->isSuperAdmin()) {
            return Category::active()->get();
        }

        return $this->assignedCategories()
            ->where('is_active', true)
            ->get();
    }

    // ==========================================
    // VIP METHODS
    // ==========================================

    /**
     * Check if user is VIP for a specific category
     */
    public function isVipForCategory(int $categoryId): bool
    {
        return $this->vipSubscriptions()
            ->where('category_id', $categoryId)
            ->where('vip_until', '>', now())
            ->exists();
    }

    /**
     * Get VIP expiry for a specific category
     */
    public function getVipExpiryForCategory(int $categoryId): ?Carbon
    {
        $subscription = $this->vipSubscriptions()
            ->where('category_id', $categoryId)
            ->first();

        return $subscription?->vip_until;
    }

    /**
     * Activate VIP for a specific category
     */
    public function activateVipForCategory(int $categoryId, int $days): UserCategoryVip
    {
        if ($this->isVipForCategory($categoryId)) {
            throw new \LogicException('Cannot activate VIP while current VIP is still active.');
        }

        return UserCategoryVip::updateOrCreate(
            [
                'user_id' => $this->id,
                'category_id' => $categoryId,
            ],
            [
                'vip_until' => now()->addDays($days),
            ]
        );
    }

    /**
     * Get all active VIP subscriptions
     */
    public function getActiveVipSubscriptions()
    {
        return $this->vipSubscriptions()
            ->where('vip_until', '>', now())
            ->with('category')
            ->get();
    }

    // ==========================================
    // GLOBAL PERMISSION METHODS (for web panel)
    // ==========================================

    /**
     * Check if user can manage categories (super admin only)
     */
    public function canManageCategories(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user can manage users (admin or super admin)
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage payments (admin or super admin)
     */
    public function canManagePayments(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage settings (super admin only)
     */
    public function canManageSettings(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user can view analytics (staff and above)
     */
    public function canViewAnalytics(): bool
    {
        return $this->isStaff();
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get full name (prioritize first_name + last_name, fallback to name)
     */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name || $this->last_name) {
            return trim($this->first_name . ' ' . $this->last_name);
        }

        return $this->name ?? $this->username ?? 'User ' . $this->id;
    }

    /**
     * Get display name for UI
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->full_name;
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayNameAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MODERATOR => 'Moderator',
            self::ROLE_USER => 'User',
            default => ucfirst($this->role ?? 'User'),
        };
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Only telegram users
     */
    public function scopeTelegramUsers($query)
    {
        return $query->whereNotNull('telegram_id');
    }

    /**
     * Scope: Only web users (can login via web)
     */
    public function scopeWebUsers($query)
    {
        return $query->whereNotNull('email')->whereNotNull('password');
    }

    /**
     * Scope: Only super admins
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('role', self::ROLE_SUPER_ADMIN);
    }

    /**
     * Scope: Only admins (includes super_admin)
     */
    public function scopeAdmins($query)
    {
        return $query->whereIn('role', [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    /**
     * Scope: Only moderators
     */
    public function scopeModerators($query)
    {
        return $query->where('role', self::ROLE_MODERATOR);
    }

    /**
     * Scope: All staff (admin, moderator, super_admin)
     */
    public function scopeStaff($query)
    {
        return $query->whereIn('role', [
            self::ROLE_ADMIN,
            self::ROLE_MODERATOR,
            self::ROLE_SUPER_ADMIN,
        ]);
    }

    /**
     * Scope: Regular users only
     */
    public function scopeRegularUsers($query)
    {
        return $query->where('role', self::ROLE_USER);
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Find or create user from Telegram data
     */
    public static function findOrCreateFromTelegram(object $telegramUser): self
    {
        $user = self::where('telegram_id', $telegramUser->id)->first();

        if (!$user) {
            return self::create([
                'telegram_id' => $telegramUser->id,
                'username' => $telegramUser->username ?? null,
                'first_name' => $telegramUser->first_name ?? null,
                'last_name' => $telegramUser->last_name ?? null,
                'name' => trim(($telegramUser->first_name ?? '') . ' ' . ($telegramUser->last_name ?? ''))
                    ?: ($telegramUser->username ?? 'User ' . $telegramUser->id),
                'role' => self::ROLE_USER,
            ]);
        }

        $user->update([
            'username' => $telegramUser->username ?? $user->username,
            'first_name' => $telegramUser->first_name ?? $user->first_name,
            'last_name' => $telegramUser->last_name ?? $user->last_name,
            'name' => trim(($telegramUser->first_name ?? '') . ' ' . ($telegramUser->last_name ?? ''))
                ?: ($telegramUser->username ?? $user->name),
        ]);

        return $user;
    }

    /**
     * Find user by Telegram ID
     */
    public static function findByTelegramId(int $telegramId): ?self
    {
        return self::where('telegram_id', $telegramId)->first();
    }

    // ==========================================
    // ROLE MANAGEMENT
    // ==========================================

    /**
     * Promote user to admin
     */
    public function promoteToAdmin(): bool
    {
        return $this->update(['role' => self::ROLE_ADMIN]);
    }

    /**
     * Promote user to moderator
     */
    public function promoteToModerator(): bool
    {
        return $this->update(['role' => self::ROLE_MODERATOR]);
    }

    /**
     * Demote user to regular user
     */
    public function demoteToUser(): bool
    {
        return $this->update(['role' => self::ROLE_USER]);
    }

    /**
     * Set user role
     */
    public function setRole(string $role): bool
    {
        if (!in_array($role, [
            self::ROLE_USER,
            self::ROLE_MODERATOR,
            self::ROLE_ADMIN,
            self::ROLE_SUPER_ADMIN,
        ])) {
            return false;
        }

        return $this->update(['role' => $role]);
    }
}
