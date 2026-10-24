<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Support\ImageStore;
use App\Support\MediaLibrary;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResponsiveImagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentSeeder::class);
        Storage::fake('public');
    }

    public function test_an_upload_gets_smaller_copies_only_below_its_own_width(): void
    {
        $path = (new ImageStore)->store(UploadedFile::fake()->image('site.jpg', 1000, 600), 'projects');

        Storage::disk('public')->assertExists([ImageStore::variantPath($path, 160), ImageStore::variantPath($path, 480), ImageStore::variantPath($path, 960)]);
        Storage::disk('public')->assertMissing(ImageStore::variantPath($path, 1440));

        $srcset = ImageStore::srcset($path);
        $this->assertStringContainsString('-480.webp 480w', $srcset);
        $this->assertStringEndsWith('.webp 1000w', $srcset);
    }

    public function test_copies_stay_out_of_the_media_library_and_go_with_their_photo(): void
    {
        $path = (new ImageStore)->store(UploadedFile::fake()->image('site.jpg', 800, 600), 'projects');

        $this->assertSame([$path], array_column((new MediaLibrary)->files(), 'path'));

        (new MediaLibrary)->delete($path);

        $this->assertSame([], Storage::disk('public')->allFiles('projects'));
    }

    public function test_older_photos_get_their_copies_from_the_command(): void
    {
        Storage::disk('public')->put('team/'.str_repeat('a', 40).'.webp', (string) UploadedFile::fake()->image('old.webp', 700, 700)->getContent());

        $this->artisan('fdh:image-variants')->expectsOutput('2 resized copies written.')->assertSuccessful();
        $this->artisan('fdh:image-variants')->expectsOutput('0 resized copies written.')->assertSuccessful();
    }

    public function test_project_cards_offer_the_smaller_copies(): void
    {
        $cover = (new ImageStore)->store(UploadedFile::fake()->image('cover.jpg', 1200, 800), 'projects');
        Project::factory()->create(['cover_image' => $cover]);

        $this->get(route('projects'))->assertOk()->assertSee('-480.webp 480w', false)->assertSee('sizes="(max-width: 760px) 85vw', false);
    }
}
