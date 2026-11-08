<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TraceMiddleware
{
    /**
     * For each request, mint a short hex request id and share it across
     * the whole request lifecycle so every log line (direct or indirect)
     * for the same request carries it. Keeps the middleware trivial so it
     * never becomes a unit of work on its own.
     */
    public function handle(Request $request, Closure $next)
    {
        $requestId = bin2hex(random_bytes(8));

        Log::shareContext(['requestId' => $requestId]);
        $request->attributes->set('requestId', $requestId);

        return $next($request);
    }
}
