<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViewLog extends Model
{
    use HasFactory;

    protected $table = 'view_logs';

    protected $fillable = [
        'user_id',
        'video_part_id',
        'category_id',
        'is_vip',
        'source',
        'device',
        'ip_address',
    ];

    protected $casts = [
        'is_vip' => 'boolean',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * User who viewed the content
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Video part that was viewed
     */
    public function videoPart(): BelongsTo
    {
        return $this->belongsTo(VideoPart::class);
    }

    /**
     * Category of the viewed content
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: VIP views only
     */
    public function scopeVipViews($query)
    {
        return $query->where('is_vip', true);
    }

    /**
     * Scope: Free views only
     */
    public function scopeFreeViews($query)
    {
        return $query->where('is_vip', false);
    }

    /**
     * Scope: By category
     */
    public function scopeForCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: By user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
