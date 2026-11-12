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

if (! function_exists('is_site_url')) {
    /**
     * Whether a URL points at this site (same scheme and host), so redirects never leave it.
     */
    function is_site_url(string $url): bool
    {
        $site = parse_url(url('/'));
        $target = parse_url($url);

        return isset($target['host'], $site['host'])
            && strcasecmp($target['host'], $site['host']) === 0
            && ($target['scheme'] ?? '') === ($site['scheme'] ?? '')
            && ($target['port'] ?? null) === ($site['port'] ?? null);
    }
}
