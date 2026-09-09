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
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register QrCodeService as singleton for generating payment QR codes
        $this->app->singleton(QrCodeService::class, function ($app) {
            return new QrCodeService;
        });
    }

    public function boot(): void
    {
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
}
