<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * Updates a live site from GitHub without SSH: downloads the built "release" branch (made by
 * .github/workflows/release.yml), copies it over the site, removes code files that no longer exist,
 * runs new database migrations and clears the caches. What the server owns — .env, uploads, logs,
 * the SQLite file — is never touched.
 */
class Updater
{
    /**
     * Paths (relative to the site root) an update never writes or deletes.
     */
    private const PROTECTED = ['.env', '.git', 'storage', 'public/storage', 'bootstrap/cache', 'database/database.sqlite', 'public/.user.ini', '.user.ini'];

    /**
     * Code folders where files that are gone from the new release are deleted.
     */
    private const MANAGED = ['app', 'config', 'database/migrations', 'database/seeders', 'database/factories', 'lang', 'resources', 'routes', 'public/build', 'vendor'];

    public function __construct(private ?string $root = null) {}

    public function root(): string
    {
        return rtrim($this->root ?? base_path(), '/\\');
    }

    public function repository(): string
    {
        return (string) config('fdh.updates.repo');
    }

    public function branch(): string
    {
        return (string) config('fdh.updates.branch');
    }

    /**
     * A git checkout of the source (a developer's computer) must never be overwritten by the built release.
     */
    public function isSourceCheckout(): bool
    {
        return is_dir($this->root().'/.git');
    }

    /**
     * @return array{sha: string, built_at: string, subject: string}|null
     */
    public function current(): ?array
    {
        $file = $this->root().'/REVISION';

        return is_file($file) ? self::parseRevision((string) file_get_contents($file)) : null;
    }

    /**
     * The newest build on GitHub, or null when GitHub cannot be reached.
     *
     * @return array{sha: string, built_at: string, subject: string}|null
     */
    public function latest(bool $fresh = false): ?array
    {
        if ($fresh) {
            Cache::forget('fdh-update-latest');
        }

        return Cache::remember('fdh-update-latest', 300, function (): ?array {
            try {
                $response = $this->github()->get("repos/{$this->repository()}/contents/REVISION", ['ref' => $this->branch()]);
                if (! $response->successful()) {
                    return null;
                }

                return self::parseRevision((string) base64_decode((string) $response->json('content'), true));
            } catch (Throwable) {
                return null;
            }
        });
    }

    /**
     * Commit messages between the installed build and the newest one, newest first.
     *
     * @return list<string>
     */
    public function changes(string $from, string $to): array
    {
        // Cached so reloading the update page does not use up GitHub's hourly API allowance.
        return Cache::remember("fdh-update-changes:{$from}:{$to}", 600, fn (): array => $this->fetchChanges($from, $to));
    }

    /**
     * @return list<string>
     */
    private function fetchChanges(string $from, string $to): array
    {
        try {
            $response = $this->github()->get("repos/{$this->repository()}/compare/{$from}...{$to}");
            if (! $response->successful()) {
                return [];
            }

            return collect($response->json('commits', []))
                ->map(fn (array $commit): string => (string) strtok((string) ($commit['commit']['message'] ?? ''), "\n"))
                ->filter()
                ->reverse()
                ->take(30)
                ->values()
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Download and install the newest build, then migrate and clear caches.
     *
     * @return list<string>
     */
    public function update(): array
    {
        if ($this->isSourceCheckout()) {
            throw new RuntimeException('This is a development copy (it has a .git folder). Update it with git, not from the admin panel.');
        }
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('The PHP zip extension is turned off. Turn it on in hPanel → PHP Configuration.');
        }

        @set_time_limit(0);
        ignore_user_abort(true);

        $work = storage_path('app/updates');
        File::ensureDirectoryExists($work);
        $zip = $work.'/release.zip';

        $response = $this->github()->timeout(300)->withOptions(['sink' => $zip])->get("repos/{$this->repository()}/zipball/{$this->branch()}");
        if (! $response->successful() || ! is_file($zip)) {
            throw new RuntimeException('Could not download the update from GitHub (HTTP '.$response->status().').');
        }

        $log = ['Downloaded the newest build ('.round(filesize($zip) / 1048576, 1).' MB).'];

        Artisan::call('down', ['--retry' => 15, '--refresh' => 15]);
        try {
            $log = array_merge($log, $this->installFromZip($zip));

            Artisan::call('migrate', ['--force' => true]);
            $log[] = 'Database is up to date.';
            Artisan::call('optimize:clear');
            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }
            $log[] = 'Caches cleared.';
        } finally {
            Artisan::call('up');
            @unlink($zip);
            Cache::forget('fdh-update-latest');
        }

        return $log;
    }

    /**
     * Copy a release zip over the site and delete code files the release no longer has.
     *
     * @return list<string>
     */
    public function installFromZip(string $zipPath): array
    {
        $archive = new ZipArchive;
        if ($archive->open($zipPath) !== true) {
            throw new RuntimeException('The downloaded update is not a valid zip file.');
        }

        $extract = storage_path('app/updates/extract-'.bin2hex(random_bytes(4)));
        File::ensureDirectoryExists($extract);
        $archive->extractTo($extract);
        $archive->close();

        try {
            // GitHub zips have one top-level folder (owner-repo-sha); hand-made zips may not.
            $entries = array_values(array_diff(scandir($extract) ?: [], ['.', '..']));
            $source = count($entries) === 1 && is_dir($extract.'/'.$entries[0]) ? $extract.'/'.$entries[0] : $extract;

            foreach (['artisan', 'REVISION', 'vendor/autoload.php', 'public/build/manifest.json'] as $required) {
                if (! file_exists($source.'/'.$required)) {
                    throw new RuntimeException("The update is incomplete ({$required} is missing). Nothing was changed.");
                }
            }

            $incoming = [];
            $copied = 0;
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS));
            foreach ($files as $file) {
                $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($source) + 1));
                if ($this->isProtected($relative)) {
                    continue;
                }
                $incoming[$relative] = true;
                $target = $this->root().'/'.$relative;
                File::ensureDirectoryExists(dirname($target));
                if (! is_file($target) || md5_file($target) !== md5_file($file->getPathname())) {
                    copy($file->getPathname(), $target);
                    $copied++;
                }
            }

            $removed = 0;
            foreach (self::MANAGED as $folder) {
                $dir = $this->root().'/'.$folder;
                if (! is_dir($dir)) {
                    continue;
                }
                $existing = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
                foreach ($existing as $file) {
                    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($this->root()) + 1));
                    if (! isset($incoming[$relative]) && ! $this->isProtected($relative)) {
                        @unlink($file->getPathname());
                        $removed++;
                    }
                }
            }

            return ["Updated {$copied} file(s), removed {$removed} old file(s)."];
        } finally {
            File::deleteDirectory($extract);
        }
    }

    private function isProtected(string $relative): bool
    {
        foreach (self::PROTECTED as $path) {
            if ($relative === $path || str_starts_with($relative, $path.'/')) {
                return true;
            }
        }

        return false;
    }

    private function github(): PendingRequest
    {
        $request = Http::baseUrl('https://api.github.com/')
            ->acceptJson()
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28', 'User-Agent' => 'fdh-site-updater'])
            ->timeout(20);

        $token = (string) config('fdh.updates.token');

        return $token !== '' ? $request->withToken($token) : $request;
    }

    /**
     * REVISION is three lines: source commit SHA, build time, commit subject.
     *
     * @return array{sha: string, built_at: string, subject: string}|null
     */
    public static function parseRevision(string $contents): ?array
    {
        $lines = array_map('trim', explode("\n", trim($contents)));
        if (! preg_match('/^[0-9a-f]{7,40}$/', $lines[0] ?? '')) {
            return null;
        }

        return ['sha' => $lines[0], 'built_at' => $lines[1] ?? '', 'subject' => $lines[2] ?? ''];
    }
}
