<?php

namespace Tests\Feature;

use App\Livewire\Admin\Content;
use App\Livewire\Admin\Roles;
use App\Livewire\Admin\TopbarAlerts;
use App\Livewire\Admin\Users;
use App\Models\Inquiry;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_top_bar_shows_unread_inquiries_only_to_inquiry_staff(): void
    {
        Inquiry::factory()->count(2)->create(['name' => 'Rahim Uddin']);

        Livewire::actingAs($this->admin('Sales'))->test(TopbarAlerts::class)->assertSee('2 new')->assertSee('Rahim Uddin');
        Livewire::actingAs($this->admin('Editor'))->test(TopbarAlerts::class)->assertDontSee('Rahim Uddin');

        $this->actingAs($this->admin())->get(route('admin.dashboard'))->assertOk()->assertSee('Sign out')->assertSee('Notification settings');
    }

    public function test_each_role_only_reaches_its_own_pages(): void
    {
        $sales = $this->admin('Sales');
        $this->actingAs($sales)->get(route('admin.inquiries'))->assertOk();
        $this->actingAs($sales)->get(route('admin.services'))->assertForbidden();
        $this->actingAs($sales)->get(route('admin.users'))->assertForbidden();

        $editor = $this->admin('Editor');
        $this->actingAs($editor)->get(route('admin.projects'))->assertOk();
        $this->actingAs($editor)->get(route('admin.inquiries'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.activity'))->assertForbidden();

        $super = $this->admin();
        foreach (['admin.users', 'admin.roles', 'admin.activity', 'admin.content', 'admin.team'] as $route) {
            $this->actingAs($super)->get(route($route))->assertOk();
        }
    }

    public function test_content_tabs_follow_permissions(): void
    {
        $editor = $this->admin('Editor');

        Livewire::actingAs($editor)->test(Content::class)
            ->assertSet('tab', 'hero')
            ->set('tab', 'branding')
            ->call('save')
            ->assertForbidden();
    }

    public function test_content_saves_both_languages(): void
    {
        $editor = $this->admin('Editor');

        Livewire::actingAs($editor)->test(Content::class, ['tab' => 'hero'])
            ->set('values.hero_title_highlight', ['en' => 'Perfect Home', 'bn' => 'নিখুঁত বাড়ি'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Perfect Home', Setting::read('hero_title_highlight', 'en'));
        $this->assertSame('নিখুঁত বাড়ি', Setting::read('hero_title_highlight', 'bn'));
    }

    public function test_users_manager_cannot_take_over_a_super_admin(): void
    {
        $this->admin();
        $manager = $this->admin('Admin');
        Role::findByName('Admin')->givePermissionTo('users.manage');
        $super = User::role('Super Admin')->first();

        Livewire::actingAs($manager)->test(Users::class)->call('resetPassword', $super->id)->assertForbidden();
        Livewire::actingAs($manager)->test(Users::class)->call('toggleActive', $super->id)->assertForbidden();
        Livewire::actingAs($manager)->test(Users::class)->call('delete', $super->id)->assertForbidden();

        // Nor promote anyone, including themselves, to Super Admin.
        Livewire::actingAs($manager)->test(Users::class)
            ->set('name', 'New')->set('email', 'new@example.com')->set('role', 'Super Admin')
            ->call('save')
            ->assertForbidden();
    }

    public function test_only_super_admin_can_grant_user_management_to_a_role(): void
    {
        $manager = $this->admin('Admin');
        Role::findByName('Admin')->givePermissionTo('users.manage');
        $editorRole = Role::findByName('Editor');

        Livewire::actingAs($manager)->test(Roles::class)
            ->call('edit', $editorRole->id)
            ->set('selected', ['users.manage'])
            ->call('save')
            ->assertForbidden();

        $this->assertFalse($editorRole->fresh()->hasPermissionTo('users.manage'));
    }

    public function test_last_super_admin_cannot_be_removed(): void
    {
        $first = $this->admin();
        $second = $this->admin();

        // With two active Super Admins, one may deactivate the other…
        Livewire::actingAs($first)->test(Users::class)->call('toggleActive', $second->id)->assertHasNoErrors();
        $this->assertFalse($second->fresh()->is_active);

        // …but the last active one can neither demote itself nor be left without a successor.
        Livewire::actingAs($first)->test(Users::class)
            ->call('edit', $first->id)
            ->set('role', 'Editor')
            ->call('save')
            ->assertForbidden();

        Livewire::actingAs($first)->test(Users::class)->call('toggleActive', $first->id)->assertForbidden();
        $this->assertSame(1, User::role('Super Admin')->where('is_active', true)->count());
    }

    public function test_new_user_gets_a_temporary_password_and_must_change_it(): void
    {
        $super = $this->admin();

        Livewire::actingAs($super)->test(Users::class)
            ->call('create')
            ->set('name', 'Sales Person')->set('email', 'sales@example.com')->set('role', 'Sales')
            ->call('save')
            ->assertHasNoErrors()
            ->assertNotSet('temporaryPassword', null);

        $user = User::query()->where('email', 'sales@example.com')->firstOrFail();
        $this->assertTrue($user->must_change_password);
        $this->assertTrue($user->hasRole('Sales'));
    }
}
