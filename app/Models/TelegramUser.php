<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TelegramUser extends Model
{
    use HasFactory;

    /**
     * Role constants
     */
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MODERATOR = 'moderator';

    protected $fillable = [
        'telegram_user_id',
        'username',
        'first_name',
        'last_name',
        'role',
        'linked_user_id',
        'vip_until',
    ];

    protected $casts = [
        'vip_until' => 'datetime',
    ];

    /**
     * Relationship: Linked web user
     */
    public function linkedUser()
    {
        return $this->belongsTo(User::class, 'linked_user_id');
    }

    /**
     * Relationship: Payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if user is VIP
     */
    public function isVip(): bool
    {
        return $this->vip_until && $this->vip_until->isFuture();
    }

    /**
     * Check if user is admin (bot-specific or linked web admin)
     */
    public function isAdmin(): bool
    {
        // Check bot-specific admin role
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }

        // Check linked web user admin role
        if ($this->linkedUser && $this->linkedUser->role === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Check if user is moderator
     */
    public function isModerator(): bool
    {
        return $this->role === self::ROLE_MODERATOR;
    }

    /**
     * Check if user can add movies (admin or moderator)
     * Moderator can only ADD movies, not edit/delete
     */
    public function canAddMovies(): bool
    {
        return $this->isAdmin() || $this->isModerator();
    }

    /**
     * Check if user can edit movies (admin only)
     */
    public function canEditMovies(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can delete movies (admin only)
     */
    public function canDeleteMovies(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage VIP status (admin only)
     */
    public function canManageVip(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage users (admin only)
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage payments (admin only)
     */
    public function canManagePayments(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can view analytics (admin only)
     */
    public function canViewAnalytics(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Legacy method for backward compatibility
     * @deprecated Use canAddMovies() instead
     */
    public function canManageMovies(): bool
    {
        return $this->canAddMovies();
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return match ($permission) {
            'add_movies' => $this->canAddMovies(),
            'edit_movies' => $this->canEditMovies(),
            'delete_movies' => $this->canDeleteMovies(),
            'manage_vip' => $this->canManageVip(),
            'manage_users' => $this->canManageUsers(),
            'manage_payments' => $this->canManagePayments(),
            'view_analytics' => $this->canViewAnalytics(),
            // Legacy support
            'manage_movies' => $this->canAddMovies(),
            default => false,
        };
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get display role name
     */
    public function getRoleDisplayNameAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MODERATOR => 'Moderator',
            self::ROLE_USER => 'User',
            default => ucfirst($this->role),
        };
    }

    /**
     * Scope: Get only admins
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    /**
     * Scope: Get only moderators
     */
    public function scopeModerators($query)
    {
        return $query->where('role', self::ROLE_MODERATOR);
    }

    /**
     * Scope: Get staff (admins + moderators)
     */
    public function scopeStaff($query)
    {
        return $query->whereIn('role', [self::ROLE_ADMIN, self::ROLE_MODERATOR]);
    }

    /**
     * Find or create user from Telegram data
     */
    public static function findOrCreateFromTelegram($telegramUser): self
    {
        return self::updateOrCreate(
            ['telegram_user_id' => $telegramUser->id],
            [
                'username' => $telegramUser->username ?? null,
                'first_name' => $telegramUser->first_name ?? null,
                'last_name' => $telegramUser->last_name ?? null,
            ]
        );
    }

    /**
     * Promote user to admin
     */
    public function promoteToAdmin(): bool
    {
        return $this->update(['role' => self::ROLE_ADMIN]);
    }

    /**
     * Demote user to regular user
     */
    public function demoteToUser(): bool
    {
        return $this->update(['role' => self::ROLE_USER]);
    }

    /**
     * Link to web user
     */
    public function linkToUser(User $user): bool
    {
        return $this->update(['linked_user_id' => $user->id]);
    }

    /**
     * Unlink from web user
     */
    public function unlinkFromUser(): bool
    {
        return $this->update(['linked_user_id' => null]);
    }
}
