<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class PaymentStatusCache
{
    /**
     * Retrieve cached payload by Tripay reference.
     */
    public static function getByReference(string $reference): ?array
    {
        return Cache::get(self::key('ref', $reference));
    }

    /**
     * Store payload for both reference and merchant reference.
     */
    public static function put(array $payload, ?string $reference, ?string $merchantRef, int $ttlSeconds): void
    {
        if ($ttlSeconds <= 0) {
            return;
        }

        $expiration = now()->addSeconds($ttlSeconds);

        if (!empty($reference)) {
            Cache::put(self::key('ref', $reference), $payload, $expiration);
        }

        if (!empty($merchantRef)) {
            Cache::put(self::key('merchant', $merchantRef), $payload, $expiration);
        }
    }

    /**
     * Clear cached payloads.
     */
    public static function forget(?string $reference, ?string $merchantRef = null): void
    {
        if (!empty($reference)) {
            Cache::forget(self::key('ref', $reference));
        }

        if (!empty($merchantRef)) {
            Cache::forget(self::key('merchant', $merchantRef));
        }
    }

    /**
     * Build cache key.
     */
    private static function key(string $type, string $value): string
    {
        $prefix = config('vip.payment.status_cache_prefix', 'payment-status');

        return "{$prefix}:{$type}:{$value}";
    }
}
