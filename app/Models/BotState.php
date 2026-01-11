<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BotState extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'telegram_user_id';
    public $incrementing = false;

    protected $fillable = [
        'telegram_user_id',
        'state',
        'data',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * Get or create state for user
     */
    public static function getState(int $telegramUserId): ?self
    {
        // Auto-cleanup expired states before fetching
        self::cleanupExpired();

        $state = self::find($telegramUserId);

        // Check if state is expired
        if ($state && $state->expires_at && $state->expires_at->isPast()) {
            $state->delete();
            return null;
        }

        return $state;
    }

    /**
     * Set state for user with 1 hour expiration
     */
    public static function setState(int $telegramUserId, string $state, array $data = []): self
    {
        return self::updateOrCreate(
            ['telegram_user_id' => $telegramUserId],
            [
                'state' => $state,
                'data' => $data,
                'expires_at' => Carbon::now()->addHour(), // 1 hour timeout
            ]
        );
    }

    /**
     * Clear state for user
     */
    public static function clearState(int $telegramUserId): void
    {
        self::where('telegram_user_id', $telegramUserId)->delete();
    }

    /**
     * Clean up expired states
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', Carbon::now())->delete();
    }
}
