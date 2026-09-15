<?php

return [
    'default' => env('MAIL_MAILER', 'smtp'),

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', null),
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL', 'stack'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'mailgun' => [
            'transport' => 'mailgun',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

    'to' => [
        // Cast an empty/unset MAIL_TO to null (not ''). Laravel's own
        // MailManager::setGlobalAddress() runs on every mailer resolution
        // and checks `isset($address['address'])`, which is true for an
        // empty string but false for null -- so leaving this as '' makes
        // the framework itself call Mailer::alwaysTo('', ...) on every
        // mailer, independent of and bypassing AppServiceProvider's own
        // guarded alwaysTo() call, causing every outgoing email (e.g. the
        // registration welcome email) to fail with a Symfony
        // RfcComplianceException. See docs/claude/... session notes.
        'address' => env('MAIL_TO') ?: null,
        'name' => env('MAIL_TO_NAME', ''),
    ],

    'markdown' => [
        'theme' => 'default',

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],
];
