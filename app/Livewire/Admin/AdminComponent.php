<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Base for every signed-in admin page. Re-checks access, account security and the page's
 * permission on every Livewire request, in addition to the route middleware.
 */
#[Layout('layouts.admin')]
abstract class AdminComponent extends Component
{
    /**
     * Permission required to open this page; null means any admin.
     */
    protected ?string $permission = null;

    public function boot(): void
    {
        // The admin panel is always in English; visitors' language choice only affects the public site.
        app()->setLocale('en');

        $user = auth()->user();

        abort_unless($user !== null && $user->canAccessAdmin(), 403);

        if ($this->requiresSecureAccount()) {
            abort_unless($user->hasTwoFactorEnabled() && ! $user->must_change_password, 403);
        }

        if ($this->permission !== null) {
            abort_unless($user->can($this->permission), 403);
        }
    }

    protected function requiresSecureAccount(): bool
    {
        return true;
    }

    /**
     * Guard an individual action with an extra permission.
     */
    protected function authorizeAbility(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403);
    }
}
