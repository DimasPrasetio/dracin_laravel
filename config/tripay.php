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

];
