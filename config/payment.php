<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Configuration
    |--------------------------------------------------------------------------
    |
    | Configure payment gateways for Smart African Market
    |
    */

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'payment_url' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
        'merchant_email' => env('MERCHANT_EMAIL'),
    ],

    /*
    | Mobile money (MTN, M-Pesa) callbacks.
    |
    | The callback endpoint confirms payment and marks orders paid, so it must
    | be authenticated like the Stripe and Paystack webhooks are. Set a strong
    | random secret and configure the provider to sign callbacks with it.
    | When this is unset the endpoint refuses every request -- it fails closed
    | rather than accepting unauthenticated payment confirmations.
    */
    'mobile_money' => [
        'webhook_secret' => env('MOMO_WEBHOOK_SECRET'),
        'signature_header' => env('MOMO_SIGNATURE_HEADER', 'X-Momo-Signature'),
    ],

    'cash_on_delivery' => [
        'enabled' => env('COD_ENABLED', true),
        'fee' => env('COD_FEE', 0),
    ],
];
