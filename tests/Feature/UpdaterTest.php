<?php

namespace Tests\Feature;

use App\Support\Updater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

class UpdaterTest extends TestCase
{
    use RefreshDatabase;

    private string $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->site = sys_get_temp_dir().'/fdh-update-test-'.bin2hex(random_bytes(4));
        File::ensureDirectoryExists($this->site);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->site);
        parent::tearDown();
    }

    /**
     * A release zip shaped like GitHub's: one top folder with the built site.
     *
     * @param  array<string, string>  $files
     */
    private function releaseZip(array $files): string
    {
        $path = sys_get_temp_dir().'/fdh-release-'.bin2hex(random_bytes(4)).'.zip';
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE);
        $files += [
            'artisan' => '<?php',
            'REVISION' => "abc1234def5678\n2026-10-24T10:00:00Z\nAdd calculator",
            'vendor/autoload.php' => '<?php',
            'public/build/manifest.json' => '{}',
        ];
        foreach ($files as $name => $contents) {
            $zip->addFromString('KlokAds-fdh-abc1234/'.$name, $contents);
        }
        $zip->close();

        return $path;
    }

    public function test_release_is_copied_and_server_files_are_never_touched(): void
    {
        File::put($this->site.'/.env', 'APP_KEY=keep-me');
        File::ensureDirectoryExists($this->site.'/storage/app/public/projects');
        File::put($this->site.'/storage/app/public/projects/photo.webp', 'uploaded');
        File::ensureDirectoryExists($this->site.'/app/Old');
        File::put($this->site.'/app/Old/Gone.php', '<?php // removed upstream');
        File::put($this->site.'/app/Kept.php', '<?php // old');

        $zip = $this->releaseZip([
            'app/Kept.php' => '<?php // new',
            'app/New.php' => '<?php',
            '.env' => 'APP_KEY=from-zip',
            'storage/app/public/projects/photo.webp' => 'from-zip',
        ]);

        $log = (new Updater($this->site))->installFromZip($zip);
        @unlink($zip);

        $this->assertSame('<?php // new', File::get($this->site.'/app/Kept.php'));
        $this->assertFileExists($this->site.'/app/New.php');
        $this->assertFileDoesNotExist($this->site.'/app/Old/Gone.php');
        $this->assertSame('APP_KEY=keep-me', File::get($this->site.'/.env'));
        $this->assertSame('uploaded', File::get($this->site.'/storage/app/public/projects/photo.webp'));
        $this->assertSame('abc1234def5678', (new Updater($this->site))->current()['sha']);
        $this->assertStringContainsString('removed 1 old file', $log[0]);
    }

    public function test_incomplete_release_changes_nothing(): void
    {
        File::put($this->site.'/app.txt', 'original');

        $path = sys_get_temp_dir().'/fdh-broken-'.bin2hex(random_bytes(4)).'.zip';
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE);
        $zip->addFromString('x/app.txt', 'broken');
        $zip->close();

        try {
            (new Updater($this->site))->installFromZip($path);
            $this->fail('An incomplete release must be refused.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('incomplete', $e->getMessage());
        } finally {
            @unlink($path);
        }

        $this->assertSame('original', File::get($this->site.'/app.txt'));
    }

    public function test_a_development_copy_is_never_overwritten(): void
    {
        File::ensureDirectoryExists($this->site.'/.git');

        $this->expectException(RuntimeException::class);
        (new Updater($this->site))->update();
    }

    public function test_reads_the_newest_build_from_github(): void
    {
        Http::fake(['api.github.com/repos/KlokAds/fdh/contents/REVISION*' => Http::response([
            'content' => base64_encode("0123456789abcdef\n2026-10-24T12:00:00Z\nNew feature"),
        ])]);

        $latest = (new Updater($this->site))->latest(fresh: true);

        $this->assertSame('0123456789abcdef', $latest['sha']);
        $this->assertSame('New feature', $latest['subject']);
    }

    public function test_only_permitted_admins_can_open_the_update_page(): void
    {
        Http::fake();

        $this->actingAs($this->admin('Admin'))->get(route('admin.system-update'))->assertForbidden();
        $this->actingAs($this->admin())->get(route('admin.system-update'))->assertOk()->assertSee('development copy');
    }
}
