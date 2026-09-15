<?php

namespace App\Providers;

use App\Models\BlogPost;
use App\Models\Order;
use App\Models\Product;
use App\Models\SalonService;
use App\Models\Setting;
use App\Models\SitePage;
use App\Models\Vendor;
use App\Observers\KnowledgeBaseObserver;
use App\Observers\OrderObserver;
use App\Observers\VendorObserver;
use App\Services\QrCodeService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Roadmap 3.3: this used to live on App\Providers\RouteServiceProvider,
     * which the slim bootstrap/app.php skeleton has no equivalent slot for
     * (route registration now happens in bootstrap/app.php's withRouting()
     * call). Breeze's own auth controllers (VerifyEmailController,
     * RegisteredUserController, EmailVerificationPromptController,
     * EmailVerificationNotificationController, ConfirmablePasswordController)
     * reference this constant for their post-verification/confirmation
     * redirect, so it moved here rather than disappearing.
     */
    public const HOME = '/dashboard';

    public function register(): void
    {
        // Register QrCodeService as singleton for generating payment QR codes
        $this->app->singleton(QrCodeService::class, function ($app) {
            return new QrCodeService;
        });
    }

    public function boot(): void
    {
        $this->configureRateLimiting();

        Blade::if('canModule', function (string $moduleKey) {
            return auth()->check() && auth()->user()->hasModuleAccess($moduleKey);
        });

        // KnowledgeBaseObserver resolves its source type from the model class,
        // so it is registered by class name (Laravel resolves observers from the
        // container when dispatched, which cannot satisfy a constructor argument).
        Product::observe(KnowledgeBaseObserver::class);
        SalonService::observe(KnowledgeBaseObserver::class);
        BlogPost::observe(KnowledgeBaseObserver::class);
        SitePage::observe(KnowledgeBaseObserver::class);

        Order::observe(OrderObserver::class);
        Vendor::observe(VendorObserver::class);

        // Configure SMTP from database settings
        $this->configureMail();

        // Route all outgoing mail to the dev/test catch-all address
        // when MAIL_TO is set. Leave MAIL_TO empty in production.
        if ($mailTo = config('mail.to.address')) {
            Mail::alwaysTo($mailTo);
        }
    }

    /**
     * Configure mail settings from database
     */
    protected function configureMail(): void
    {
        // During migrations (and on a freshly migrated test DB) the
        // settings/cache tables may not exist yet — skip the DB-backed
        // mail configuration rather than crashing application bootstrap.
        //
        // Schema::hasTable() needs a working connection, so the table guard
        // alone did not cover a database that is unreachable: every artisan
        // command then died at boot with a QueryException, including the ones
        // you need in exactly that situation (key:generate, config:clear,
        // app:verify-readiness). Mail configuration is an optimisation, not a
        // prerequisite for the framework booting.
        try {
            if (! Schema::hasTable('settings') || ! Schema::hasTable('cache')) {
                return;
            }
        } catch (\Throwable $e) {
            Log::debug('Skipping database-backed mail configuration: '.$e->getMessage());

            return;
        }

        // Use cache to avoid configuring mail on every request
        $cacheKey = 'mail.configured_from_db';

        if (Cache::has($cacheKey)) {
            return;
        }

        $host = Setting::getValue('mail_host');

        if (empty($host)) {
            return;
        }

        $config = [
            'driver' => 'smtp',
            'host' => $host,
            'port' => (int) Setting::getValue('mail_port', 587),
            'encryption' => Setting::getValue('mail_encryption', 'tls'),
            'username' => Setting::getValue('mail_username'),
            'password' => Setting::getDecrypted('mail_password'),
            'from' => [
                'address' => Setting::getValue('mail_from_address', config('mail.from.address')),
                'name' => Setting::getValue('mail_from_name', config('mail.from.name')),
            ],
        ];

        config(['mail.mailers.smtp' => array_merge(config('mail.mailers.smtp'), $config)]);
        config(['mail.default' => 'smtp']);

        // Cache for 1 hour - clear this when settings are updated
        Cache::put($cacheKey, true, 3600);
    }

    /**
     * Roadmap 3.3: moved from App\Providers\RouteServiceProvider (deleted),
     * which had no equivalent slot once route registration moved into
     * bootstrap/app.php's withRouting() callback. Unchanged otherwise.
     */
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
