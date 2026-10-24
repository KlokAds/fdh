<?php

namespace Tests\Feature;

use App\Livewire\Admin\Content;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Media;
use App\Livewire\Admin\Projects;
use App\Models\Project;
use App\Models\Setting;
use App\Support\MediaLibrary;
use App\Support\SampleContent;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SampleContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(ContentSeeder::class);
    }

    public function test_samples_fill_the_site_and_are_marked(): void
    {
        app(SampleContent::class)->install();

        $this->assertSame(6, Project::query()->where('is_sample', true)->count());
        $this->assertNotSame('', Setting::raw()['hero_image']);
        $this->assertTrue(app(SampleContent::class)->isSampleImage('hero_image'));

        $this->get('/')->assertOk()->assertSee('দোতলা পারিবারিক বাড়ি');

        // Running it again never duplicates anything.
        app(SampleContent::class)->install();
        $this->assertSame(6, Project::query()->count());
    }

    public function test_uploading_a_real_photo_replaces_the_sample(): void
    {
        app(SampleContent::class)->install();
        $super = $this->admin();

        Livewire::actingAs($super)->test(Content::class, ['tab' => 'hero'])
            ->set('uploads.hero_image', UploadedFile::fake()->image('real.jpg', 1600, 900))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse(app(SampleContent::class)->isSampleImage('hero_image'));
    }

    public function test_picked_images_preview_and_can_be_discarded_before_saving(): void
    {
        $super = $this->admin();

        $content = Livewire::actingAs($super)->test(Content::class, ['tab' => 'hero'])
            ->set('uploads.hero_image', UploadedFile::fake()->image('hero.jpg', 1600, 900))
            ->assertSee('saves with the form');
        $content->call('discardUpload', 'uploads.hero_image')->assertSet('uploads', [])->assertDontSee('saves with the form');

        Livewire::actingAs($super)->test(Projects::class)
            ->call('create')
            ->set('photos', [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')])
            ->call('discardUpload', 'photos', 0)
            ->assertCount('photos', 1)
            ->call('discardUpload', 'is_published')
            ->assertForbidden();
    }

    public function test_media_library_protects_photos_in_use(): void
    {
        app(SampleContent::class)->install();
        Storage::disk('public')->put('projects/orphan.webp', 'x');
        $used = Project::query()->first()->cover_image;

        $media = Livewire::actingAs($this->admin())->test(Media::class)
            ->assertSee('Not used on any page')
            ->call('delete', $used)
            ->assertHasErrors('media');
        Storage::disk('public')->assertExists($used);

        $media->call('deleteAllUnused');
        Storage::disk('public')->assertMissing('projects/orphan.webp');
        Storage::disk('public')->assertExists($used);

        $this->actingAs($this->admin('Sales'))->get(route('admin.media'))->assertForbidden();
    }

    public function test_editor_photos_upload_and_count_as_used(): void
    {
        $url = $this->actingAs($this->admin('Editor'))
            ->post(route('admin.editor-image'), ['image' => UploadedFile::fake()->image('plan.jpg', 800, 600)])
            ->assertOk()
            ->json('url');
        $path = 'editor/'.basename($url);
        Storage::disk('public')->assertExists($path);

        $this->actingAs($this->admin('Sales'))->post(route('admin.editor-image'), ['image' => UploadedFile::fake()->image('x.jpg', 800, 600)])->assertForbidden();

        Project::factory()->create(['description' => ['en' => '<p>Plan</p><img src="'.$url.'" class="rt-md rt-left">']]);
        $this->assertArrayHasKey($path, app(MediaLibrary::class)->usage());
    }

    public function test_remove_all_samples_keeps_real_content(): void
    {
        app(SampleContent::class)->install();
        $real = Project::factory()->create(['title' => ['en' => 'Real project']]);
        $super = $this->admin();

        Livewire::actingAs($super)->test(Dashboard::class)->assertSee('Remove all samples')->call('removeSamples');

        $this->assertSame(0, Project::query()->where('is_sample', true)->count());
        $this->assertTrue(Project::query()->whereKey($real->id)->exists());
        $this->assertSame('', Setting::raw()['hero_image']);
        $this->assertFalse(app(SampleContent::class)->present());
    }

    public function test_only_settings_managers_can_remove_samples(): void
    {
        app(SampleContent::class)->install();

        Livewire::actingAs($this->admin('Sales'))->test(Dashboard::class)->call('removeSamples')->assertForbidden();
        $this->assertSame(6, Project::query()->where('is_sample', true)->count());
    }
}
