<?php

return [
    /*
    |--------------------------------------------------------------------------
    | bKash API Credentials
    |--------------------------------------------------------------------------
    */
    'sandbox' => env('BKASH_SANDBOX', true),

    'sandbox_credentials' => [
        'app_key' => env('BKASH_SANDBOX_APP_KEY', ''),
        'app_secret' => env('BKASH_SANDBOX_APP_SECRET', ''),
        'username' => env('BKASH_SANDBOX_USERNAME', ''),
        'password' => env('BKASH_SANDBOX_PASSWORD', ''),
        'base_url' => env('BKASH_SANDBOX_BASE_URL', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized'),
        'app_url' => env('APP_URL', ''),
        'callback_url' => env('BKASH_CALLBACK_URL', ''),
        'bkash_token' => env('BKASH_TOKEN', ''),
        'bkash_token_expire_time' => env('BKASH_TOKEN_EXPIRE_TIME', '')
    ],

    'production_credentials' => [
        'app_key' => env('BKASH_PRODUCTION_APP_KEY', ''),
        'app_secret' => env('BKASH_PRODUCTION_APP_SECRET', ''),
        'username' => env('BKASH_PRODUCTION_USERNAME', ''),
        'password' => env('BKASH_PRODUCTION_PASSWORD', ''),
        'base_url' => env('BKASH_PRODUCTION_BASE_URL', 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized'),
        'app_url' => env('APP_URL', ''),
        'callback_url' => env('BKASH_CALLBACK_URL', ''),
        'bkash_token' => env('BKASH_TOKEN', ''),
        'bkash_token_expire_time' => env('BKASH_TOKEN_EXPIRE_TIME', '')
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */
    'route_prefix' => 'bkash',
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    */
    'button_text' => 'Pay with bKash',
    'button_class' => 'btn btn-success',
];
