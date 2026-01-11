<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'thumbnail_file_id',
        'channel_message_id',
        'total_parts',
        'created_by',
    ];

    protected $casts = [
        'total_parts' => 'integer',
        'channel_message_id' => 'integer',
    ];

    /**
     * Video parts relationship
     */
    public function videoParts()
    {
        return $this->hasMany(VideoPart::class)->orderBy('part_number');
    }

    /**
     * Get free parts
     */
    public function getFreeParts()
    {
        return $this->videoParts()->where('is_vip', false)->get();
    }

    /**
     * Get VIP parts
     */
    public function getVipParts()
    {
        return $this->videoParts()->where('is_vip', true)->get();
    }

    /**
     * Check if movie is complete (all parts uploaded)
     */
    public function isComplete(): bool
    {
        return $this->videoParts()->count() === $this->total_parts;
    }
}
