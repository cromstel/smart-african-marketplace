<?php

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
use App\Http\Middleware\TraceMiddleware;
use App\Http\Middleware\TrackUserBehavior;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\ValidateSignature;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    // Roadmap 3.3: event auto-discovery defaults to on inside
    // Application::configure(), which would let Laravel scan app/Listeners
    // and auto-wire any handle() method it can infer an event type-hint
    // from -- on top of the manual $listen array App\Providers\
    // EventServiceProvider already declares. For BookingStatusChanged's
    // three listeners that would mean every listener firing twice. Explicitly
    // disabling discovery here preserves the app's existing all-manual
    // wiring (App\Providers\EventServiceProvider::$listen), unchanged.
    ->withEvents(discover: false)
    ->withMiddleware(function (Middleware $middleware) {
        // Global stack: identical list and order to the old
        // app/Http/Kernel.php $middleware property (TrustHosts stayed
        // commented out there too -- this app has never enabled it).
        $middleware->use([
            TrustProxies::class,
            HandleCors::class,
            PreventRequestsDuringMaintenance::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
            PreventCrlfInjection::class,
            SecurityHeaders::class,
        ]);

        // 'web'/'api' groups: fully replaced rather than appended to, since
        // this app substitutes its own subclasses for several stock
        // middleware (EncryptCookies, VerifyCsrfToken, ...) and
        // Middleware::group() overwrites a default group entirely when given
        // the same key. Identical list and order to the old Kernel.
        $middleware->group('api', [
            // Trace first so even throttled requests get a requestId.
            TraceMiddleware::class,
            // Sanctum's stateful-frontend middleware was commented out in
            // the old Kernel too -- this app's API is token-only.
            ThrottleRequests::class.':api',
            SubstituteBindings::class,
            CompressResponse::class,
        ]);

        $middleware->group('web', [
            TraceMiddleware::class,
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
        ]);

        // Aliases that differ from Laravel's defaults (custom subclasses) or
        // don't exist as defaults at all. Aliases identical to the framework
        // default (auth.basic, auth.session, cache.headers, can,
        // password.confirm, throttle, verified) are left to inherit it.
        $middleware->alias([
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'signed' => ValidateSignature::class,
            'admin' => EnsureUserIsAdmin::class,
            'vendor' => EnsureUserIsVendor::class,
            'salon_owner' => EnsureUserIsSalonOwner::class,
            'subscription.limits' => CheckSubscriptionLimits::class,
            'module.access' => EnsureModuleAccess::class,
            'compress' => CompressResponse::class,
            'maintenance' => MaintenanceMode::class,
            'antispam' => PreventSpamSubmissions::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
        ]);

        // The old Handler::register() registered an empty reportable
        // callback (`function (Throwable $e) {}`). An empty callback that
        // never returns false doesn't suppress default reporting, so it was
        // a no-op -- intentionally not reproduced here.

        /**
         * Controllers under /api/v1 mostly hand-build a {success, data/errors,
         * message} envelope, but that only covers responses a controller
         * returns itself. An exception thrown before that point -- a failed
         * $request->validate() call, an expired/missing auth token, a 404
         * route model binding, a throttle limit, an uncaught 500 -- skips
         * every controller and is rendered by Laravel's default handler
         * instead, which returns {message, errors?} with no 'success' key.
         * That made "was this request an error" inconsistent depending on
         * *how* the error happened rather than what endpoint it hit. This
         * adds the same 'success' => false flag to every JSON error response
         * coming out of the API, without changing status codes, messages, or
         * the 'errors' shape validation consumers already rely on.
         *
         * It also strips 'exception'/'file'/'line'/'trace' from API
         * responses unconditionally. Laravel's default renderer includes
         * those whenever APP_DEBUG is true, which for this app's .env it is
         * -- so every uncaught API error was leaking the framework's
         * exception class, absolute server file paths, and a full stack
         * trace straight into the JSON response. That's a developer
         * convenience for the web app's HTML debug page, not something an
         * HTTP API client should ever receive, regardless of how APP_DEBUG
         * happens to be set in a given environment.
         *
         * respond() (Handler::respondUsing()) fires for every rendered
         * exception response after Laravel has already built it -- exactly
         * the "post-process the final response" hook the old
         * App\Exceptions\Handler::render() override relied on by calling
         * parent::render() first. Moved here unchanged.
         */
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            $isApiJsonError = $request->is('api/*')
                && $response instanceof JsonResponse
                && $response->getStatusCode() >= 400;

            if ($isApiJsonError) {
                // Fields go in the context, not the message: Laravel's
                // Logger::formatMessage() var_exports array messages into
                // a plain string, which would destroy the structure.
                // Never let log I/O failure break the error response itself.
                try {
                    Log::channel('json')->error($e->getMessage(), [
                        'requestId' => $request->attributes->get('requestId') ?? bin2hex(random_bytes(8)),
                        'env' => app()->environment(),
                        'logger' => 'exception.handler',
                        'method' => $request->method(),
                        'url' => $request->fullUrl(),
                        'status' => $response->getStatusCode(),
                        'exceptionClass' => get_class($e),
                    ]);
                } catch (\Throwable) {
                    // logging must never be able to break an error response
                }
            }

            if (! $isApiJsonError) {
                return $response;
            }

            $payload = json_decode($response->getContent(), true);

            if (! is_array($payload)) {
                return $response;
            }

            unset($payload['exception'], $payload['file'], $payload['line'], $payload['trace']);

            // ModelNotFoundException's own message names the Eloquent model
            // class (e.g. "No query results for model [App\Models\Product]
            // 999999") -- fine for a web debug page, an internal-
            // implementation-detail leak for an API.
            //
            // Unlike the old App\Exceptions\Handler::render() override,
            // which received Laravel's render($request, $e) $e argument
            // (the original, untouched exception), this respond() callback
            // receives $e only after Handler::prepareException() has already
            // run -- which rewraps ModelNotFoundException into a
            // NotFoundHttpException with the original as ->getPrevious().
            // Both shapes are checked so the rewrite still fires.
            $modelNotFound = $e instanceof ModelNotFoundException
                || ($e instanceof NotFoundHttpException && $e->getPrevious() instanceof ModelNotFoundException);

            if ($modelNotFound) {
                $payload['message'] = 'Resource not found.';
            }

            if (! array_key_exists('success', $payload)) {
                $payload = ['success' => false] + $payload;
            }

            $response->setContent(json_encode($payload));

            return $response;
        });
    })->create();


