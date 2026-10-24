<?php

namespace App\Providers;

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\RequireTwoFactor;
use App\Http\Middleware\SetLocale;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        if ($this->app->isProduction()) {
            URL::forceHttps();
        }

        Password::defaults(fn (): Password => Password::min(12)->mixedCase()->numbers()->symbols());

        // Super Admins may do everything; every other role is limited to its permissions.
        Gate::before(fn (User $user): ?bool => $user->is_active && $user->isSuperAdmin() ? true : null);

        View::composer('layouts.site', function ($view): void {
            $view->with([
                'navServices' => Service::query()->active()->get(['id', 'slug', 'title', 'summary', 'icon']),
                'logoUrl' => Setting::imageUrl('logo', 'images/fdh-logo.jpg'),
            ]);
        });

        // Re-apply these on every Livewire update request, not just the first page load.
        Livewire::addPersistentMiddleware([SetLocale::class, EnsureUserIsAdmin::class, RequireTwoFactor::class]);
    }
}
