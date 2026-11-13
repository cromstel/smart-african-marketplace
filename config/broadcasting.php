<?php

return [
    'default' => env('BROADCAST_DRIVER', 'soketi'),

    'connections' => [
        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

        'soketi' => [
            'driver' => 'pusher',
            'key' => env('SOKETI_APP_KEY'),
            'secret' => env('SOKETI_APP_SECRET'),
            'app_id' => env('SOKETI_APP_ID'),
            'options' => [
                'host' => env('SOKETI_HOST', '127.0.0.1'),
                'port' => env('SOKETI_PORT', 6001),
                'scheme' => env('SOKETI_SCHEME', 'http'),
                'encrypted' => false,
                'useTLS' => false,
            ],
            'client_options' => [],
        ],
    ],
];
