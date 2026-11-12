<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Only active accounts with a role may continue; idle sessions are signed out.
     * Admin pages are never cached or indexed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessAdmin()) {
            if ($user) {
                ActivityLog::record('access.denied', 'Signed out: account inactive or without a role', $user->id);
            }

            return $this->signOut($request, 'Your account does not have access to the admin panel.');
        }

        $lastActivity = (int) $request->session()->get('admin.last_activity', time());
        if (time() - $lastActivity > config('fdh.admin_idle_minutes') * 60) {
            ActivityLog::record('logout.idle', 'Signed out after inactivity');

            return $this->signOut($request, 'You were signed out after a period of inactivity.');
        }
        $request->session()->put('admin.last_activity', time());
        app()->setLocale('en');

        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }

    private function signOut(Request $request, string $message): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', $message);
    }
}
