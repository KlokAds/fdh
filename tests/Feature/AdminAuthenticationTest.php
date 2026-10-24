<?php

namespace Tests\Feature;

use App\Livewire\Admin\Login;
use App\Livewire\Admin\TwoFactorChallenge;
use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_redirect_guests_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_without_two_factor_is_logged_in_and_sent_to_security_setup(): void
    {
        $user = $this->admin(secured: false);
        $user->forceFill(['password' => 'Correct-Horse-9!'])->save();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'Correct-Horse-9!')
            ->call('authenticate')
            ->assertRedirect(route('admin.security'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.security'));
    }

    public function test_admin_with_two_factor_must_pass_the_challenge(): void
    {
        $user = $this->admin();
        $user->forceFill(['password' => 'Correct-Horse-9!'])->save();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'Correct-Horse-9!')
            ->call('authenticate')
            ->assertRedirect(route('admin.two-factor'));

        $this->assertGuest();

        Livewire::test(TwoFactorChallenge::class)
            ->set('code', '000000')
            ->call('verify')
            ->assertHasErrors('code');
        $this->assertGuest();

        $code = app(Google2FA::class)->getCurrentOtp($user->two_factor_secret);

        Livewire::test(TwoFactorChallenge::class)
            ->set('code', $code)
            ->call('verify')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_two_factor_code_cannot_be_reused(): void
    {
        $user = $this->admin();
        $code = app(Google2FA::class)->getCurrentOtp($user->two_factor_secret);
        $twoFactor = app(TwoFactor::class);

        $this->assertTrue($twoFactor->verifyForUser($user, $code));
        $this->assertFalse($twoFactor->verifyForUser($user->fresh(), $code));
    }

    public function test_login_locks_out_after_repeated_failures(): void
    {
        $user = $this->admin();
        $user->forceFill(['password' => 'Correct-Horse-9!'])->save();

        foreach (range(1, 5) as $attempt) {
            Livewire::test(Login::class)->set('email', $user->email)->set('password', 'wrong')->call('authenticate')
                ->assertHasErrors('email');
        }

        // Even the right password is refused while locked out.
        Livewire::test(Login::class)->set('email', $user->email)->set('password', 'Correct-Horse-9!')->call('authenticate')
            ->assertHasErrors('email')
            ->assertNoRedirect();

        $this->assertGuest();
        $this->assertDatabaseHas('activity_logs', ['action' => 'login.locked']);
    }

    public function test_inactive_accounts_and_accounts_without_a_role_cannot_sign_in(): void
    {
        $inactive = $this->admin();
        $inactive->forceFill(['password' => 'Correct-Horse-9!', 'is_active' => false])->save();

        $noRole = User::factory()->create(['password' => 'Correct-Horse-9!']);

        foreach ([$inactive, $noRole] as $user) {
            Livewire::test(Login::class)->set('email', $user->email)->set('password', 'Correct-Horse-9!')->call('authenticate')
                ->assertHasErrors('email');
        }

        $this->assertGuest();
    }

    public function test_deactivated_admin_is_signed_out_on_next_request(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();

        $user->forceFill(['is_active' => false])->save();

        $this->actingAs($user->fresh())->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_temporary_password_must_be_changed_before_using_the_panel(): void
    {
        $user = $this->admin();
        $user->forceFill(['must_change_password' => true])->save();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('admin.security'));
    }

    public function test_admin_responses_carry_security_headers(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $this->assertStringContainsString("frame-ancestors 'none'", $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }
}
