<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    /**
     * Admins must replace a temporary password and turn on two-factor authentication
     * before using any other admin page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->must_change_password) {
            return redirect()->route('admin.security')
                ->with('status', 'Please choose a new password to replace your temporary one.');
        }

        if (! $user?->hasTwoFactorEnabled()) {
            return redirect()->route('admin.security')
                ->with('status', 'Please set up two-factor authentication to secure your account.');
        }

        return $next($request);
    }
}
