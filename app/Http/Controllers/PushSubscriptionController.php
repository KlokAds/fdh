<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PushSubscription;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PushSubscriptionController extends Controller
{
    /**
     * Save (or refresh) the signed-in admin's push subscription for this device.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            // Only real browser push services, so the server never posts to an arbitrary or internal host.
            'endpoint' => ['required', 'url:https', 'max:1000', 'regex:#^https://(fcm\.googleapis\.com|updates\.push\.services\.mozilla\.com|[a-z0-9-]+\.notify\.windows\.com|web\.push\.apple\.com)/#i'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'contentEncoding' => ['nullable', 'in:aes128gcm,aesgcm'],
        ]);

        $hash = PushSubscription::hashEndpoint($data['endpoint']);
        $existing = PushSubscription::query()->where('endpoint_hash', $hash)->first();

        // An endpoint belongs to one browser; never let one admin take over another admin's device.
        abort_if($existing && $existing->user_id !== $request->user()->id, 409);

        PushSubscription::query()->updateOrCreate(['endpoint_hash' => $hash], [
            'user_id' => $request->user()->id,
            'endpoint' => $data['endpoint'],
            'public_key' => $data['keys']['p256dh'],
            'auth_token' => $data['keys']['auth'],
            'content_encoding' => $data['contentEncoding'] ?? 'aes128gcm',
            'device' => Str::limit((string) $request->userAgent(), 115),
        ]);

        if (! $existing) {
            ActivityLog::record('push.subscribed', 'Turned on inquiry notifications on a device');
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => ['required', 'string', 'max:1000']]);

        PushSubscription::query()
            ->where('user_id', $request->user()->id)
            ->where('endpoint_hash', PushSubscription::hashEndpoint($request->input('endpoint')))
            ->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Service worker that shows the notification and opens the inquiry when tapped.
     */
    public function serviceWorker(): Response
    {
        $js = <<<'JS'
self.addEventListener('push', (event) => {
    let data = {};
    try { data = event.data ? event.data.json() : {}; } catch (e) { data = { title: 'New inquiry', body: event.data ? event.data.text() : '' }; }
    event.waitUntil(self.registration.showNotification(data.title || 'New inquiry', {
        body: data.body || '',
        icon: 'ICON_URL',
        badge: 'ICON_URL',
        tag: data.tag || undefined,
        renotify: true,
        data: { url: data.url || 'START_URL' },
    }));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.url) || 'START_URL';
    event.waitUntil(clients.matchAll({ type: 'window', includeUncontrolled: true }).then((list) => {
        for (const client of list) {
            if ('focus' in client && client.url.startsWith(self.registration.scope)) {
                client.navigate(url);
                return client.focus();
            }
        }
        return clients.openWindow(url);
    }));
});
JS;

        $js = str_replace(['ICON_URL', 'START_URL'], [asset('images/icon-192.png'), route('admin.dashboard')], $js);

        return response($js, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * Web app manifest so the admin panel can be installed on a phone's home screen (required for iPhone push).
     */
    public function manifest(): JsonResponse
    {
        return response()->json([
            'name' => Setting::read('site_name', 'en').' Admin',
            'short_name' => 'FDH Admin',
            'start_url' => route('admin.dashboard', [], false),
            'scope' => '/'.trim(config('fdh.admin_path'), '/').'/',
            'display' => 'standalone',
            'background_color' => '#0a1628',
            'theme_color' => '#0a1628',
            'icons' => [
                ['src' => asset('images/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => asset('images/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }
}
