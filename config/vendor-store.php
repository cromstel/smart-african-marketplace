<?php

return [
    'scheme' => env('VENDOR_STORE_SCHEME', 'https'),
    'base_domain' => env('VENDOR_STORE_BASE_DOMAIN', 'localhost'),
    'domain_target' => env('VENDOR_STORE_DOMAIN_TARGET', 'localhost'),
    'reserved_subdomains' => [
        'www',
        'admin',
        'api',
        'app',
        'mail',
        'support',
    ],
];