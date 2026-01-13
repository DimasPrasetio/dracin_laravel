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
        // Validate free_parts format
        if ($key === 'free_parts' && !empty($value)) {
            $parts = explode(',', $value);
            foreach ($parts as $part) {
                if (!ctype_digit(trim($part))) {
                    throw new \InvalidArgumentException(
                        'free_parts must be comma-separated integers (e.g., "1,2,3")'
                    );
                }
            }
        }

        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );

        Cache::forget("setting.{$key}");
    }

    /**
     * Get free parts as array
     * Returns [1] as default if value is invalid
     */
    public static function getFreeParts(): array
    {
        $value = self::get('free_parts', '1');

        if (empty($value)) {
            return [1];
        }

        $parts = array_filter(array_map('trim', explode(',', $value)));
        $validParts = array_filter($parts, fn($part) => ctype_digit($part));

        if (empty($validParts)) {
            return [1];
        }

        return array_map('intval', $validParts);
    }
}
