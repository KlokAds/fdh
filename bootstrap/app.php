<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\MetaFirstParty;
use App\Http\Middleware\RequireTwoFactor;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Support\MetaVisitor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [SetLocale::class, SecurityHeaders::class]);

        // Meta's cookies and our visitor id are read by the pixel and sent to the Conversions API as they are.
        $middleware->encryptCookies(except: ['_fbp', '_fbc', MetaVisitor::VISITOR_COOKIE]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'two-factor' => RequireTwoFactor::class,
            'meta' => MetaFirstParty::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
