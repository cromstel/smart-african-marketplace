<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Controllers under /api/v1 mostly hand-build a {success, data/errors,
     * message} envelope, but that only covers responses a controller
     * returns itself. An exception thrown before that point -- a failed
     * $request->validate() call, an expired/missing auth token, a 404 route
     * model binding, a throttle limit, an uncaught 500 -- skips every
     * controller and is rendered by Laravel's default handler instead,
     * which returns {message, errors?} with no 'success' key. That made
     * "was this request an error" inconsistent depending on *how* the
     * error happened rather than what endpoint it hit. This adds the same
     * 'success' => false flag to every JSON error response coming out of
     * the API, without changing status codes, messages, or the 'errors'
     * shape validation consumers already rely on.
     *
     * It also strips 'exception'/'file'/'line'/'trace' from API responses
     * unconditionally. Laravel's default renderer includes those whenever
     * APP_DEBUG is true, which for this app's .env it is -- so every
     * uncaught API error was leaking the framework's exception class,
     * absolute server file paths, and a full stack trace straight into the
     * JSON response. That's a developer convenience for the web app's HTML
     * debug page, not something an HTTP API client should ever receive,
     * regardless of how APP_DEBUG happens to be set in a given
     * environment.
     */
    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

        if ($this->shouldEnvelopeAsApiError($request, $response)) {
            $payload = json_decode($response->getContent(), true);

            if (is_array($payload)) {
                unset($payload['exception'], $payload['file'], $payload['line'], $payload['trace']);

                // ModelNotFoundException's own message names the Eloquent
                // model class (e.g. "No query results for model
                // [App\Models\Product] 999999") -- fine for a web debug
                // page, an internal-implementation-detail leak for an API.
                if ($e instanceof ModelNotFoundException) {
                    $payload['message'] = 'Resource not found.';
                }

                if (! array_key_exists('success', $payload)) {
                    $payload = ['success' => false] + $payload;
                }

                $response->setContent(json_encode($payload));
            }
        }

        return $response;
    }

    private function shouldEnvelopeAsApiError(Request $request, $response): bool
    {
        return $request->is('api/*')
            && $response instanceof JsonResponse
            && $response->getStatusCode() >= 400;
    }
}
