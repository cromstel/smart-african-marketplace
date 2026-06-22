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
use App\Rules\SecureEmail;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
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
        // Secure email validation to mitigate CRLF injection (CVE-2024-xxxxx)
        Validator::extend('email_secure', SecureEmail::class);

        Blade::if('canModule', function (string $moduleKey) {
            return auth()->check() && auth()->user()->hasModuleAccess($moduleKey);
        });

        Product::observe(new KnowledgeBaseObserver('product'));
        SalonService::observe(new KnowledgeBaseObserver('salon_service'));
        BlogPost::observe(new KnowledgeBaseObserver('blog_post'));
        SitePage::observe(new KnowledgeBaseObserver('site_page'));

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
// Skip if the settings table does not exist (e.g., fresh DB)
if (!Schema::hasTable('settings')) {
    return;
}
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
