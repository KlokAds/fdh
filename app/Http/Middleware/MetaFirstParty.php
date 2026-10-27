<?php

namespace App\Http\Middleware;

use App\Support\MetaConversions;
use App\Support\MetaVisitor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Server-side Meta tracking for public pages, once a Pixel ID is set:
 *  - sets the visitor id and Meta's _fbp / _fbc cookies from our own domain (they outlive
 *    cookies written by JavaScript, and _fbc is captured from the ad click's fbclid even
 *    before the pixel loads);
 *  - sends each page's events (PageView, plus ViewContent and so on queued by the page) to the
 *    Conversions API after the response, with the same ids the browser pixel uses.
 */
class MetaFirstParty
{
    public function handle(Request $request, Closure $next): Response
    {
        if (MetaConversions::pixelId() === '' || ! $request->isMethod('GET')) {
            return $next($request);
        }

        $now = (int) floor(microtime(true) * 1000);
        $cookies = [];

        if (! MetaVisitor::visitorId($request)) {
            $cookies[] = $this->remember($request, MetaVisitor::VISITOR_COOKIE, MetaVisitor::newVisitorId(), days: 730, httpOnly: true);
        }

        $fbclid = (string) $request->query('fbclid');
        if (preg_match('/^[A-Za-z0-9_\-.]{10,400}$/', $fbclid) && ! str_ends_with((string) $request->cookie('_fbc'), '.'.$fbclid)) {
            $cookies[] = $this->remember($request, '_fbc', "fb.1.{$now}.{$fbclid}", days: 90);
        }

        if (! preg_match('/^fb\.\d\.\d+\.\d+$/', (string) $request->cookie('_fbp'))) {
            $cookies[] = $this->remember($request, '_fbp', 'fb.1.'.$now.'.'.random_int(1000000000, 2147483647), days: 90);
        }

        $request->attributes->set('meta_events', [['name' => 'PageView', 'id' => MetaVisitor::eventId('PageView'), 'custom' => []]]);

        $response = $next($request);

        foreach ($cookies as $cookie) {
            $response->headers->setCookie($cookie);
        }

        if ($response->isSuccessful() && str_contains((string) $response->headers->get('Content-Type'), 'text/html') && ! MetaVisitor::isBot($request)) {
            $events = $request->attributes->get('meta_events');
            $userData = MetaVisitor::userData($request);
            $url = $request->fullUrl();

            defer(fn () => app(MetaConversions::class)->track($userData, $url, $events));
        }

        return $response;
    }

    /**
     * Queue a first-party cookie and make it visible to the rest of this request.
     */
    private function remember(Request $request, string $name, string $value, int $days, bool $httpOnly = false): Cookie
    {
        $request->cookies->set($name, $value);

        return cookie($name, $value, $days * 24 * 60, null, null, $request->isSecure(), $httpOnly, false, 'lax');
    }
}
