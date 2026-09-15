<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'african_market'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::ATTR_PERSISTENT => env('DB_PERSISTENT', false),
                PDO::ATTR_TIMEOUT => env('DB_CONNECTION_TIMEOUT', 5),
                // PDO::MYSQL_ATTR_USE_BUFFERED_QUERY is deprecated since PHP 8.5
                // in favor of Pdo\Mysql::ATTR_USE_BUFFERED_QUERY. Use whichever
                // the running PHP version provides so this works on both sides
                // of the 8.4 cutover without a deprecation notice.
                (class_exists(\Pdo\Mysql::class) ? \Pdo\Mysql::ATTR_USE_BUFFERED_QUERY : PDO::MYSQL_ATTR_USE_BUFFERED_QUERY) => true,
            ]) : [],
        ],
    ],

    'migrations' => 'migrations',
];
