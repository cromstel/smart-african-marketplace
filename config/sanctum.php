<?php

use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\VerifyCsrfToken;

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Sanctum can use session cookies to authenticate SPA front-ends. These
    | domains should match the "stateful" domains used by your SPA.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,127.0.0.1,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    /*
    |--------------------------------------------------------------------------
    | Authentication Guard
    |--------------------------------------------------------------------------
    |
    | Sanctum uses this guard to authenticate API tokens. The default is the
    | web guard which is appropriate for most applications.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration (minutes)
    |--------------------------------------------------------------------------
    |
    | Tokens issued to the native mobile app expire after this many minutes.
    | Kept long-lived (1 year) to avoid forcing frequent re-logins on mobile,
    | while remaining configurable. Set null for non-expiring tokens.
    |
    */

    'expiration' => env('SANCTUM_EXPIRATION', 525600),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Sanctum uses these middleware when stateful (cookie) requests are made.
    |
    */

    'middleware' => [
        'verify_csrf_token' => VerifyCsrfToken::class,
        'encrypt_cookies' => EncryptCookies::class,
    ],

];

