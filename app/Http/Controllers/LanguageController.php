<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class LanguageController extends Controller
{
    public function switch(Request $request, $locale)
    {
        $supportedLocales = config('app.supported_locales', ['en']);

        if (! in_array($locale, $supportedLocales, true)) {
            abort(404);
        }

        Session::put('locale', $locale);
        App::setLocale($locale);

        return redirect()->to(URL::previous() ?: route('home'));
    }
}
