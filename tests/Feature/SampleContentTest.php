<?php

namespace Tests\Feature;

use App\Livewire\Admin\Content;
use App\Livewire\Admin\Dashboard;
use App\Models\Project;
use App\Models\Setting;
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
