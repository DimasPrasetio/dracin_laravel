<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Category
    |--------------------------------------------------------------------------
    |
    | Used when no explicit category is provided (single-bot fallback).
    |
    */
    'default_category_slug' => env('VIP_DEFAULT_CATEGORY_SLUG', 'dracin'),
    'default_category_name' => env('VIP_DEFAULT_CATEGORY_NAME', 'Dracin'),

    /*
    |--------------------------------------------------------------------------
    | VIP Package Definitions
    |--------------------------------------------------------------------------
    |
    | Single source of truth for VIP package metadata used by both web and bot.
    |
    */
    'packages' => [
        '1day' => [
            'name' => 'VIP 1 Hari',
            'duration' => 1,
            'price' => 2500,
            'description' => 'Akses VIP selama 1 hari untuk menonton semua film Dracin HD',
        ],
        '3days' => [
            'name' => 'VIP 3 Hari',
            'duration' => 3,
            'price' => 6000,
            'description' => 'Akses VIP selama 3 hari untuk menonton semua film Dracin HD',
            'badge' => 'Hemat 14%',
        ],
        '7days' => [
            'name' => 'VIP 7 Hari',
            'duration' => 7,
            'price' => 10000,
            'description' => 'Akses VIP selama 7 hari untuk menonton semua film Dracin HD',
            'badge' => 'Hemat 43%',
            'popular' => true,
        ],
        '30days' => [
            'name' => 'VIP 30 Hari',
            'duration' => 30,
            'price' => 25000,
            'description' => 'Akses VIP selama 30 hari untuk menonton semua film Dracin HD',
            'badge' => 'Hemat 73%',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Behaviour
    |--------------------------------------------------------------------------
    */
    'payment' => [
        'expiry_minutes' => env('PAYMENT_EXPIRY_MINUTES', 60),
        'reuse_window_minutes' => env('PAYMENT_REUSE_WINDOW_MINUTES', 60),
        'status_cache_seconds' => env('PAYMENT_STATUS_CACHE_SECONDS', 20),
        'status_cache_prefix' => env('PAYMENT_STATUS_CACHE_PREFIX', 'payment-status'),
        'allowed_methods' => [
            'QRIS',
            'BCAVA',
            'BNIVA',
            'BRIVA',
            'MANDIRIVA',
            'PERMATAVA',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Rate Limits
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'payment_create' => [
            'max_attempts' => env('RATE_LIMIT_PAYMENT_CREATE', 5),
            'decay_minutes' => env('RATE_LIMIT_PAYMENT_CREATE_DECAY', 1),
        ],
        'payment_status' => [
            'max_attempts' => env('RATE_LIMIT_PAYMENT_STATUS', 30),
            'decay_minutes' => env('RATE_LIMIT_PAYMENT_STATUS_DECAY', 1),
        ],
        'bot_webhook' => [
            'max_attempts' => env('RATE_LIMIT_BOT_WEBHOOK', 90),
            'decay_minutes' => env('RATE_LIMIT_BOT_WEBHOOK_DECAY', 1),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Temporary File Cleanup
    |--------------------------------------------------------------------------
    | QR code images are cleaned up hourly to ensure Telegram has time to
    | download them. Files older than 5 minutes will be removed.
    */
    'cleanup' => [
        'directories' => [
            storage_path('app/qris'),
            storage_path('app/temp'),
        ],
        'max_age_minutes' => env('TEMP_FILE_MAX_AGE_MINUTES', 5),
    ],
];
