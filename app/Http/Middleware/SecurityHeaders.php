<?php

namespace App\Http\Middleware;

use App\Support\MetaConversions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add a nonce-based Content Security Policy and hardening headers to every web response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();
        $nonce = Vite::cspNonce();

        $response = $next($request);

        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }
        $response->headers->remove('X-Powered-By');

        $viteDev = app()->environment('local') && file_exists(public_path('hot'))
            ? ' '.trim((string) file_get_contents(public_path('hot'))).' ws://localhost:* ws://127.0.0.1:* ws://[::1]:*'
            : '';

        // Facebook hosts are allowed only once a Meta Pixel ID is set in the admin.
        $meta = MetaConversions::pixelId() !== '';
        $metaScript = $meta ? ' https://connect.facebook.net' : '';
        $metaConnect = $meta ? ' https://connect.facebook.net https://www.facebook.com' : '';
        $metaImg = $meta ? ' https://www.facebook.com' : '';

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'".$metaScript.$viteDev,
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com".$viteDev,
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: blob:".$metaImg,
            "connect-src 'self'".$metaConnect.$viteDev,
            'frame-src https://www.google.com',
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        if ($request->isSecure()) {
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
