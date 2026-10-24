<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Use the visitor's chosen language, else the site default from settings.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locales = array_keys(config('fdh.locales'));
        $locale = $request->session()->get('locale');

        if (! in_array($locale, $locales, true)) {
            $locale = Setting::read('default_locale', 'en');
        }

        app()->setLocale(in_array($locale, $locales, true) ? $locale : 'en');

        return $next($request);
    }
}
