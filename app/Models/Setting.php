<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Get setting value
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = self::find($key);
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set setting value
     */
    public static function set(string $key, $value, string $description = null): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );

        Cache::forget("setting.{$key}");
    }

    /**
     * Get free parts as array
     */
    public static function getFreeParts(): array
    {
        $value = self::get('free_parts', '1');
        return array_map('intval', explode(',', $value));
    }
}
