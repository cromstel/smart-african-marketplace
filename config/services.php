<?php

return [

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    'swish' => [
        'production_url' => env('SWISH_PRODUCTION_URL', 'https://swish.paymentiq.io'),
        'sandbox_url' => env('SWISH_SANDBOX_URL', 'https://sandbox.swishpayment.com'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    /*
    | Read through config() rather than env() directly in views — env()
    | returns null outside config files once `php artisan config:cache`
    | has run, which silently dropped these tags in production.
    */
    'google_analytics' => [
        'id' => env('GOOGLE_ANALYTICS_ID'),
    ],

    'facebook_pixel' => [
        'id' => env('FACEBOOK_PIXEL_ID'),
    ],

];
