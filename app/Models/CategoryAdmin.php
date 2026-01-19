<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryAdmin extends Model
{
    use HasFactory;

    /**
     * Role constants
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MODERATOR = 'moderator';

    protected $fillable = [
        'category_id',
        'user_id',
        'role',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Category relationship
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Web user relationship
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Only admins
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    /**
     * Scope: Only moderators
     */
    public function scopeModerators($query)
    {
        return $query->where('role', self::ROLE_MODERATOR);
    }

    /**
     * Scope: By category
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: By web user
     */
    public function scopeForWebUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }


    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if this is an admin role
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if this is a moderator role
     */
    public function isModerator(): bool
    {
        return $this->role === self::ROLE_MODERATOR;
    }

    /**
     * Check if can add movies (admin or moderator)
     */
    public function canAddMovies(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_MODERATOR]);
    }

    /**
     * Check if can edit movies (admin only)
     */
    public function canEditMovies(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if can delete movies (admin only)
     */
    public function canDeleteMovies(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Get display name
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->display_name;
        }

        return 'Unknown';
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayNameAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MODERATOR => 'Moderator',
            default => ucfirst($this->role),
        };
    }
}
