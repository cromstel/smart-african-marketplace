<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode
    |--------------------------------------------------------------------------
    |
    | This option controls whether the application is in maintenance mode.
    | When enabled, a maintenance page will be shown to all visitors
    | except those with admin privileges or whitelisted IPs/emails.
    |
    */

    'enabled' => env('MAINTENANCE_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Maintenance Message
    |--------------------------------------------------------------------------
    |
    | The message to display when the site is in maintenance mode.
    |
    */

    'message' => env('MAINTENANCE_MESSAGE', 'Site is currently under maintenance. Please check back soon.'),

    /*
    |--------------------------------------------------------------------------
    | Retry After
    |--------------------------------------------------------------------------
    |
    | The number of seconds to wait before retrying the request.
    | This is sent as the Retry-After HTTP header.
    |
    */

    'retry' => env('MAINTENANCE_RETRY_AFTER', 3600),

    /*
    |--------------------------------------------------------------------------
    | Expected Duration
    |--------------------------------------------------------------------------
    |
    | The expected duration of the maintenance mode.
    | This is displayed to users on the maintenance page.
    |
    */

    'expected' => env('MAINTENANCE_EXPECTED', null),

    /*
    |--------------------------------------------------------------------------
    | Allowed IPs
    |--------------------------------------------------------------------------
    |
    | IP addresses that can bypass maintenance mode.
    | Useful for allowing developer access during maintenance.
    |
    */

    'allowed_ips' => array_filter(array_map('trim', explode(',', env('MAINTENANCE_ALLOWED_IPS', '127.0.0.1,::1')))),

    /*
    |--------------------------------------------------------------------------
    | Allowed Emails
    |--------------------------------------------------------------------------
    |
    | Email addresses that can bypass maintenance mode.
    | Useful for allowing specific users access during maintenance.
    |
    */

    'allowed_emails' => [
        // Add admin emails here
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Routes
    |--------------------------------------------------------------------------
    |
    | Routes that should be accessible even in maintenance mode.
    |
    */

    'allowed_routes' => [
        'admin/*',
        'vendor/*',
        'api/health',
    ],
];
