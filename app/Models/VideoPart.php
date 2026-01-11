<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VideoPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'video_id',
        'file_id',
        'part_number',
        'is_vip',
        'duration',
        'file_size',
    ];

    protected $casts = [
        'part_number' => 'integer',
        'is_vip' => 'boolean',
        'duration' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * Movie relationship
     */
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    /**
     * Generate unique video ID
     */
    public static function generateVideoId(): string
    {
        do {
            $videoId = 'MKV' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 14)) . '_' . rand(10000, 99999);
        } while (self::where('video_id', $videoId)->exists());

        return $videoId;
    }

    /**
     * Get deep link for this video
     */
    public function getDeepLinkAttribute(): string
    {
        $botUsername = config('telegram.bots.default.username');
        return "https://t.me/{$botUsername}?start={$this->video_id}";
    }
}
