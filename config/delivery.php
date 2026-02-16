<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Delivery Configuration
    |--------------------------------------------------------------------------
    |
    | Built-in delivery management system configuration
    |
    */

    'distance_unit' => env('DISTANCE_UNIT', 'km'),
    'default_radius' => env('DEFAULT_DELIVERY_RADIUS', 50),

    'pricing' => [
        'base_fee' => env('BASE_DELIVERY_FEE', 5.00),
        'per_km_rate' => env('PER_KM_RATE', 0.50),
    ],

    'tracking' => [
        'enabled' => true,
        'update_interval' => 300, // 5 minutes
    ],

    'proof_of_delivery' => [
        'photo_required' => env('POD_PHOTO_REQUIRED', true),
        'signature_required' => env('POD_SIGNATURE_REQUIRED', false),
    ],
];
