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
    }

    /**
     * Configure mail settings from database
     */
    protected function configureMail(): void
    {
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
