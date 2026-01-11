<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TelegramUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_user_id',
        'username',
        'first_name',
        'last_name',
        'vip_until',
    ];

    protected $casts = [
        'vip_until' => 'datetime',
    ];

    /**
     * Check if user is VIP
     */
    public function isVip(): bool
    {
        return $this->vip_until && $this->vip_until->isFuture();
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Payments relationship
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
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
}
