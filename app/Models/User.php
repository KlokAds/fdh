<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'preferred_locale'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_hidden' => 'boolean',
            'must_change_password' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * An account may use the admin panel when it is active and holds at least one role.
     */
    public function canAccessAdmin(): bool
    {
        return $this->is_active && $this->roles->isNotEmpty();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(config('fdh.super_role'));
    }

    /**
     * Two-factor authentication is mandatory only when one of the user's roles demands it.
     */
    public function requiresTwoFactor(): bool
    {
        return $this->roles->contains(fn ($role): bool => (bool) $role->requires_two_factor);
    }

    /**
     * Whether the account still has a required setup step (temporary password or mandatory 2FA).
     */
    public function needsSecuritySetup(): bool
    {
        return $this->must_change_password || ($this->requiresTwoFactor() && ! $this->hasTwoFactorEnabled());
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }
}
