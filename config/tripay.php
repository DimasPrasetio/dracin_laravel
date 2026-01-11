<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tripay API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Tripay Payment Gateway integration.
    | Get your credentials from https://tripay.co.id/
    |
    */

    'api_key' => env('TRIPAY_API_KEY', ''),
    'private_key' => env('TRIPAY_PRIVATE_KEY', ''),
    'merchant_code' => env('TRIPAY_MERCHANT_CODE', ''),

    /*
    |--------------------------------------------------------------------------
    | API Mode
    |--------------------------------------------------------------------------
    |
    | Set mode to 'sandbox' for testing or 'production' for live transactions
    |
    */
    'mode' => env('TRIPAY_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | API URLs
    |--------------------------------------------------------------------------
    */
    'api_url' => [
        'sandbox' => 'https://tripay.co.id/api-sandbox',
        'production' => 'https://tripay.co.id/api',
    ],

    /*
    |--------------------------------------------------------------------------
    | Callback Configuration
    |--------------------------------------------------------------------------
    */
    'callback_url' => env('APP_URL') . '/payment/callback',

    /*
    |--------------------------------------------------------------------------
    | Return URL after payment
    |--------------------------------------------------------------------------
    */
    'return_url' => env('APP_URL'),

    /*
    |--------------------------------------------------------------------------
    | Payment Channels
    |--------------------------------------------------------------------------
    |
    | Available payment channels from Tripay
    |
    */
    'channels' => [
        'qris' => [
            'code' => 'QRIS',
            'name' => 'QRIS (Quick Response Code Indonesian Standard)',
            'type' => 'qris',
        ],
        'bca' => [
            'code' => 'BCAVA',
            'name' => 'BCA Virtual Account',
            'type' => 'virtual_account',
        ],
        'bni' => [
            'code' => 'BNIVA',
            'name' => 'BNI Virtual Account',
            'type' => 'virtual_account',
        ],
        'bri' => [
            'code' => 'BRIVA',
            'name' => 'BRI Virtual Account',
            'type' => 'virtual_account',
        ],
        'mandiri' => [
            'code' => 'MANDIRIVA',
            'name' => 'Mandiri Virtual Account',
            'type' => 'virtual_account',
        ],
        'permata' => [
            'code' => 'PERMATAVA',
            'name' => 'Permata Virtual Account',
            'type' => 'virtual_account',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | VIP Packages Configuration
    |--------------------------------------------------------------------------
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
];
