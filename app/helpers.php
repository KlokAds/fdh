<?php

use App\Models\Setting;

if (! function_exists('site')) {
    /**
     * A site setting or page text in the current language (English fallback).
     */
    function site(string $key): string
    {
        static $resolved = [];

        $locale = app()->getLocale();
        $resolved[$locale] ??= Setting::forLocale($locale);

        return $resolved[$locale][$key] ?? '';
    }
}

if (! function_exists('site_phone_href')) {
    /**
     * tel: link for the site phone number in international format.
     */
    function site_phone_href(): string
    {
        $digits = preg_replace('/\D/', '', site('phone')) ?? '';

        return 'tel:+'.(str_starts_with($digits, '88') ? $digits : '88'.$digits);
    }
}
