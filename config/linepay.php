<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | LINE Pay Channel ID
    |--------------------------------------------------------------------------
    |
    | Your LINE Pay Channel ID from the LINE Pay Merchant Center.
    |
    */
    'channel_id' => env('LINE_PAY_CHANNEL_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | LINE Pay Channel Secret
    |--------------------------------------------------------------------------
    |
    | Your LINE Pay Channel Secret from the LINE Pay Merchant Center.
    |
    */
    'channel_secret' => env('LINE_PAY_CHANNEL_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | The LINE Pay API environment. Use 'sandbox' for testing and
    | 'production' for live transactions.
    |
    | Supported: "sandbox", "production"
    |
    */
    'env' => env('LINE_PAY_ENV', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests.
    |
    */
    'timeout' => env('LINE_PAY_TIMEOUT', 20),
];
