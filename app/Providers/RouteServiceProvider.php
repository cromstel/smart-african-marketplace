<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('public-forms', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(90)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('chatbot', function (Request $request) {
            $limit = (int) Setting::get('chatbot_rate_limit_per_minute', 60);
            $limit = max(10, min(600, $limit));

            return Limit::perMinute($limit)->by($request->user()?->id ?: $request->ip());
        });

        // Payment/SMS provider callbacks previously had no rate limit at all.
        // Signature verification already rejects forged/tampered payloads
        // (see WebhookController), so this isn't guarding authenticity --
        // it's a generous ceiling against unbounded abuse. Kept high because
        // a real provider can legitimately burst events, and throttling away
        // a genuine payment webhook is worse than not throttling one.
        RateLimiter::for('webhook', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });
    }
}
