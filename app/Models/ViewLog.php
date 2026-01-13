<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewLog extends Model
{
    use HasFactory;

    protected $table = 'view_logs';

    protected $fillable = [
        'telegram_user_id',
        'video_part_id',
        'is_vip',
        'source',
        'device',
        'ip_address',
    ];

    protected $casts = [
        'is_vip' => 'boolean',
    ];

    public function videoPart()
    {
        return $this->belongsTo(VideoPart::class);
    }

    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class);
    }
}
