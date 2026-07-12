<?php

$appUrl = config('app.url') ?? 'http://localhost';
$appHost = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';
$appScheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';

return [
    'scheme' => env('VENDOR_STORE_SCHEME', $appScheme),
    'base_domain' => env('VENDOR_STORE_BASE_DOMAIN', $appHost),
    'domain_target' => env('VENDOR_STORE_DOMAIN_TARGET', $appHost),
    'reserved_subdomains' => [
        'www',
        'admin',
        'api',
        'app',
        'mail',
        'support',
    ],
];
