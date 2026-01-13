<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Role constants
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MODERATOR = 'moderator';

    protected $fillable = [
        'username',
        'name',
        'phone',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is moderator
     */
    public function isModerator(): bool
    {
        return $this->role === self::ROLE_MODERATOR;
    }

    /**
     * Check if user is admin or moderator (staff)
     */
    public function isStaff(): bool
    {
        return $this->isAdmin() || $this->isModerator();
    }

    /**
     * Check if user can add movies (admin or moderator)
     */
    public function canAddMovies(): bool
    {
        return $this->isStaff();
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
     * Check if user can manage telegram users (admin only)
     */
    public function canManageTelegramUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage settings (admin only)
     */
    public function canManageSettings(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Relationship: Linked telegram user (reverse relationship)
     */
    public function linkedTelegramUser()
    {
        return $this->hasOne(TelegramUser::class, 'linked_user_id');
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
}
