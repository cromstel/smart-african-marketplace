<?php

use Illuminate\Support\Facades\Facade;

return [
    'name' => env('APP_NAME', 'Smart African Marketplace'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'asset_url' => env('ASSET_URL'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'supported_locales' => explode(',', env('APP_SUPPORTED_LOCALES', 'en,fr,sv,da')),
    'supported_locale_labels' => [
        'en' => 'English',
        'fr' => 'Francais',
        'sv' => 'Svenska',
        'da' => 'Dansk',
    ],
    'faker_locale' => 'en_US',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    'version' => env('APP_VERSION', '1.0.0'),

    'maintenance' => [
        'driver' => 'file',
    ],

    // Roadmap 3.3: no 'providers' key here anymore. Illuminate\Foundation\
    // Bootstrap\RegisterProviders falls back to
    // Illuminate\Support\ServiceProvider::defaultProviders() (confirmed
    // identical to the list this app used to spell out here, plus the
    // framework's own Illuminate\Concurrency\ConcurrencyServiceProvider)
    // whenever config('app.providers') is null -- an empty array would NOT
    // trigger that fallback, so the key is absent entirely, not emptied.
    // The app's own providers now live in bootstrap/providers.php.

    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
    ])->toArray(),
];
