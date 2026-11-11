<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Setting;
use App\Models\TeamMember;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportContentTest extends TestCase
{
    use RefreshDatabase;

    private string $folder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentSeeder::class);
        Storage::fake('public');

        $this->folder = storage_path('framework/testing/import-'.uniqid());
        File::ensureDirectoryExists($this->folder.'/photos');
        foreach (['cover', 'extra', 'hero'] as $name) {
            File::put("{$this->folder}/photos/{$name}.jpg", UploadedFile::fake()->image("{$name}.jpg", 1200, 900)->getContent());
        }
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->folder);
        parent::tearDown();
    }

    public function test_prepared_content_is_added_once_however_often_it_runs(): void
    {
        Project::factory()->create(['is_sample' => true]);
        $manifest = $this->manifest([
            'settings' => ['youtube' => 'https://www.youtube.com/@friendsdreamhome'],
            'images' => ['hero_image' => 'photos/hero.jpg'],
            'team' => [
                ['name' => ['en' => 'Engr. Noman', 'bn' => 'ইঞ্জি. নোমান'], 'designation' => ['en' => 'Managing Director'], 'is_lead' => true, 'sort' => 0],
                ['name' => ['en' => 'Md Rabiul Islam'], 'designation' => ['en' => 'Senior Architect'], 'is_lead' => false, 'sort' => 3],
            ],
            'projects' => [
                ['slug' => 'duplex-house', 'category' => 'Duplex & Villa', 'title' => ['en' => 'Duplex house'], 'cover' => 'photos/cover.jpg', 'gallery' => ['photos/extra.jpg'], 'featured' => true],
            ],
        ]);

        $this->artisan('fdh:import-content', ['manifest' => $manifest])->assertSuccessful();
        $this->artisan('fdh:import-content', ['manifest' => $manifest])->assertSuccessful();

        $this->assertSame(2, TeamMember::query()->count());
        $this->assertTrue(TeamMember::query()->where('name->en', 'Engr. Noman')->firstOrFail()->is_lead);
        $this->assertSame(1, Project::query()->count(), 'the sample project is gone and the real one is not duplicated');
        $project = Project::query()->firstOrFail();
        $this->assertFalse($project->is_sample);
        $this->assertCount(1, $project->gallery);
        Storage::disk('public')->assertExists($project->cover_image);
        $this->assertSame('https://www.youtube.com/@friendsdreamhome', Setting::query()->find('youtube')->value);
        Storage::disk('public')->assertExists(Setting::raw()['hero_image']);
        // Running twice leaves no orphaned copies of the first run's photos.
        $this->assertCount(2, array_filter(Storage::disk('public')->allFiles('projects'), fn (string $path): bool => ! preg_match('/-\d+\.webp$/', $path)));
    }

    public function test_photos_outside_the_manifest_folder_are_refused(): void
    {
        $manifest = $this->manifest(['projects' => [['slug' => 'x', 'category' => 'Interior', 'title' => ['en' => 'X'], 'cover' => '../../../.env']]]);

        $this->expectExceptionMessage('Photo not found');
        $this->artisan('fdh:import-content', ['manifest' => $manifest]);
    }

    public function test_the_head_of_the_team_leads_the_team_page(): void
    {
        TeamMember::factory()->create(['name' => ['en' => 'Site Engineer'], 'sort' => 1]);
        TeamMember::factory()->create(['name' => ['en' => 'The Director'], 'sort' => 9, 'is_lead' => true]);

        $this->withSession(['locale' => 'en'])->get(route('team'))
            ->assertOk()
            ->assertSeeInOrder(['lead-card', 'The Director', 'Site Engineer']);

        // On the home page the director has the principal block, so the team strip lists the others.
        $this->withSession(['locale' => 'en'])->get('/')->assertSee('Site Engineer')->assertDontSee('The Director');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function manifest(array $data): string
    {
        $path = $this->folder.'/manifest.json';
        File::put($path, json_encode($data, JSON_UNESCAPED_UNICODE));

        return $path;
    }
}
