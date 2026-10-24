<?php

namespace Tests\Feature;

use App\Livewire\Admin\Content;
use App\Livewire\Admin\Faqs;
use App\Livewire\Admin\Projects;
use App\Livewire\Admin\Roles;
use App\Livewire\Site\InquiryForm;
use App\Models\Faq;
use App\Models\Inquiry;
use App\Models\Project;
use App\Models\Setting;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReviewFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentSeeder::class);
    }

    public function test_removing_a_settings_image_only_deletes_the_saved_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('projects/keep.webp', 'x');
        Storage::disk('public')->put('site/logo.webp', 'x');
        Setting::write(['logo' => 'site/logo.webp']);

        Livewire::actingAs($this->admin())->test(Content::class, ['tab' => 'branding'])
            ->set('values.logo', 'projects/keep.webp')
            ->call('removeImage', 'logo');

        Storage::disk('public')->assertExists('projects/keep.webp');
        Storage::disk('public')->assertMissing('site/logo.webp');
    }

    public function test_a_cleared_bangla_text_falls_back_to_english_not_the_old_default(): void
    {
        Setting::write(['hero_title_start' => ['en' => 'New English heading']]);

        $this->assertSame('New English heading', Setting::read('hero_title_start', 'bn'));
    }

    public function test_phone_numbers_typed_in_bangla_digits_are_accepted(): void
    {
        $this->travelTo(now());
        $component = Livewire::test(InquiryForm::class);
        $this->travel(5)->seconds();

        $component
            ->set('name', 'রহিম')
            ->set('phone', '০১৭১২৩৪৫৬৭৮')
            ->call('submit')
            ->assertHasNoErrors('phone');

        $this->assertSame('01712345678', Inquiry::query()->first()?->phone);
    }

    public function test_language_switch_never_redirects_off_site(): void
    {
        $this->withHeader('referer', 'https://fdh.com.bd.evil.test/x')->get('/lang/en')->assertRedirect(route('home'));
    }

    public function test_managers_cannot_grant_permissions_they_do_not_have(): void
    {
        $manager = $this->admin('Admin');
        Role::findByName('Admin')->givePermissionTo('users.manage');

        Livewire::actingAs($manager->fresh())->test(Roles::class)
            ->call('create')
            ->set('name', 'Updater')
            ->set('selected', ['system.update'])
            ->call('save')
            ->assertForbidden();

        Livewire::actingAs($manager->fresh())->test(Roles::class)
            ->call('toggleTwoFactor', Role::findByName('Admin')->id)
            ->assertForbidden();
    }

    public function test_editing_a_sample_project_keeps_it_when_samples_are_removed(): void
    {
        $project = Project::factory()->create(['is_sample' => true]);

        Livewire::actingAs($this->admin())->test(Projects::class)
            ->call('edit', $project->id)
            ->set('form.title.en', 'Our own project now')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse($project->fresh()->is_sample);
    }

    public function test_admin_actions_send_success_toasts(): void
    {
        $faq = Faq::factory()->create();

        Livewire::actingAs($this->admin())->test(Faqs::class)
            ->call('delete', $faq->id)
            ->assertDispatched('toast', type: 'success', message: 'FAQ deleted.');

        Livewire::actingAs($this->admin())->test(Faqs::class)
            ->call('create')
            ->set('form.question.en', 'How long does approval take?')
            ->set('form.answer.en', '<p>Usually a few weeks.</p>')
            ->call('save')
            ->assertDispatched('toast', type: 'success');
    }

    public function test_gallery_upload_cannot_exceed_twenty_four_photos(): void
    {
        Storage::fake('public');
        $project = Project::factory()->create(['gallery' => array_map(fn (int $i): string => "projects/{$i}.webp", range(1, 23))]);

        Livewire::actingAs($this->admin())->test(Projects::class)
            ->call('edit', $project->id)
            ->set('photos', [UploadedFile::fake()->image('a.jpg', 800, 600), UploadedFile::fake()->image('b.jpg', 800, 600)])
            ->call('save')
            ->assertHasErrors('photos');
    }
}
