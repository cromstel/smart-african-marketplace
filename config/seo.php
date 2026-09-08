<?php

/*
|--------------------------------------------------------------------------
| SEO Defaults
|--------------------------------------------------------------------------
|
| Fallback metadata used when a page does not set its own. These values are
| read through config() rather than env() on purpose: the deployment runbook
| runs `php artisan config:cache`, after which env() returns null outside
| config files, which previously left the description, keywords and social
| profile links empty in production.
|
| Per-entity overrides live in the SeoMeta model and take precedence.
|
*/

return [
    'title' => env('SEO_TITLE', 'Smart African Marketplace'),

    'description' => env(
        'SEO_DESCRIPTION',
        'Shop authentic African food, clothing, and book salon services.'
    ),

    'keywords' => env(
        'SEO_KEYWORDS',
        'african food, african clothing, african salon, african marketplace, smart african marketplace'
    ),

    /*
    | Social profile URLs, emitted as schema.org "sameAs". Empty entries are
    | filtered out, so an unset variable simply omits that profile.
    */
    'social' => [
        'facebook' => env('FACEBOOK_URL'),
        'twitter' => env('TWITTER_URL'),
        'instagram' => env('INSTAGRAM_URL'),
        'youtube' => env('YOUTUBE_URL'),
    ],
];
