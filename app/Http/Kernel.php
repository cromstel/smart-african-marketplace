<?php

namespace App\Http;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CaptureCampaignContext;
use App\Http\Middleware\CheckSubscriptionLimits;
use App\Http\Middleware\CompressResponse;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\EnsureModuleAccess;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsSalonOwner;
use App\Http\Middleware\EnsureUserIsVendor;
use App\Http\Middleware\MaintenanceMode;
use App\Http\Middleware\PreventCrlfInjection;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\PreventSpamSubmissions;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackUserBehavior;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\ValidateSignature;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel
{
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        PreventCrlfInjection::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            SetLocale::class,
            CaptureCampaignContext::class,
            TrackUserBehavior::class,
            CompressResponse::class,
            MaintenanceMode::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            ThrottleRequests::class.':api',
            SubstituteBindings::class,
            CompressResponse::class,
        ],
    ];

    protected $middlewareAliases = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'auth.session' => AuthenticateSession::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'password.confirm' => RequirePassword::class,
        'signed' => ValidateSignature::class,
        'throttle' => ThrottleRequests::class,
        'verified' => EnsureEmailIsVerified::class,
        'admin' => EnsureUserIsAdmin::class,
        'vendor' => EnsureUserIsVendor::class,
        'salon_owner' => EnsureUserIsSalonOwner::class,
        'subscription.limits' => CheckSubscriptionLimits::class,
        'module.access' => EnsureModuleAccess::class,
        'compress' => CompressResponse::class,
        'maintenance' => MaintenanceMode::class,
        'antispam' => PreventSpamSubmissions::class,
    ];
}
