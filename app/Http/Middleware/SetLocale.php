<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $supported = config('restaurant.supported_locales', ['tr', 'en', 'de']);
        $default   = config('restaurant.default_locale', 'tr');

        // 1. Check URL segment (e.g. /en/..., /de/...)
        $segment = $request->segment(1);
        if (in_array($segment, $supported) && $segment !== $default) {
            $locale = $segment;
        }
        // 2. Check session
        elseif (Session::has('locale') && in_array(Session::get('locale'), $supported)) {
            $locale = Session::get('locale');
        }
        // 3. Default
        else {
            $locale = $default;
        }

        App::setLocale($locale);
        Session::put('locale', $locale);

        return $next($request);
    }
}
