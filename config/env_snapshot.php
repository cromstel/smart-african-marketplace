<?php

/*
|--------------------------------------------------------------------------
| Environment Snapshot
|--------------------------------------------------------------------------
|
| A copy of the environment as it stood when configuration was built.
|
| Why this exists: `php artisan config:cache` -- which the deployment runbook
| mandates -- stops Laravel from loading the .env file, so `env()` returns null
| for anything that lived only in that file. Code doing *dynamic* env lookups
| (a key computed at runtime, so it cannot be listed in a config file) silently
| loses its fallback in production while working perfectly in development.
|
| Config files are evaluated when the cache is built, so this array is baked in
| and survives. Callers read `config('env_snapshot.SOME_KEY')` instead of
| `env('SOME_KEY')` and behave identically cached or not.
|
| This adds no new exposure: the cached config file already contains every
| secret pulled through env() by the other config files. Keep it out of
| anything user-facing all the same.
|
| Prefer a real config entry when the key is known ahead of time. This is for
| the genuinely dynamic case only.
|
*/

$snapshot = [];

foreach ($_ENV as $key => $value) {
    if (is_scalar($value) || $value === null) {
        $snapshot[$key] = $value;
    }
}

return $snapshot;
