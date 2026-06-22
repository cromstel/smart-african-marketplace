<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Session::get('locale', config('app.locale'));
        $supportedLocales = config('app.supported_locales', ['en']);

        if (in_array($locale, $supportedLocales, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
