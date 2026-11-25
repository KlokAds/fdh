<?php

namespace App\Support\Install;

use Illuminate\Contracts\Console\Kernel;
use Minishlink\WebPush\VAPID;
use PDO;
use Throwable;

/**
 * The browser installer (public/install.php) for shared hosting without SSH: checks the server,
 * writes .env, creates the database tables, the first Super Admin and the starting content, then locks itself.
 *
 * It runs before Laravel can boot (no .env, no APP_KEY yet), so everything up to install() is plain PHP.
 * Only someone who can read storage/app/install-token.txt on the server (made on the first visit) may use it.
 * When finished it writes storage/app/installed and never runs again; it also refuses wherever a working
 * .env already exists.
 */
class WebInstaller
{
    public const PHP_MIN = '8.3.0';

    public const EXTENSIONS = ['pdo_mysql', 'pdo_sqlite', 'mbstring', 'gd', 'zip', 'curl', 'openssl', 'fileinfo', 'dom', 'xml', 'tokenizer', 'ctype', 'bcmath'];

    /** Marks a .env this installer wrote but has not finished with, so a failed run can be retried. */
    private const IN_PROGRESS = 'FDH_INSTALLING=true';

    public function __construct(private string $base) {}

    public function lockFile(): string
    {
        return $this->base.'/storage/app/installed';
    }

    public function tokenFile(): string
    {
        return $this->base.'/storage/app/install-token.txt';
    }

    /**
     * Closed when finished, or when a .env with a key exists that this installer did not write.
     */
    public function closed(): bool
    {
        if (is_file($this->lockFile())) {
            return true;
        }

        $env = $this->base.'/.env';
        if (! is_file($env)) {
            return false;
        }
        $contents = (string) file_get_contents($env);

        return preg_match('/^APP_KEY=\S+/m', $contents) === 1 && ! str_contains($contents, self::IN_PROGRESS);
    }

    public function token(): string
    {
        $file = $this->tokenFile();
        if (! is_file($file)) {
            @mkdir(dirname($file), 0755, true);
            file_put_contents($file, bin2hex(random_bytes(12)).PHP_EOL);
            @chmod($file, 0600);
        }

        return trim((string) file_get_contents($file));
    }

    public function tokenMatches(string $given): bool
    {
        return $given !== '' && hash_equals($this->token(), trim($given));
    }

    /**
     * @return list<array{label: string, ok: bool, help: string}>
     */
    public function requirements(): array
    {
        $checks = [[
            'label' => 'PHP '.self::PHP_MIN.' or newer (now '.PHP_VERSION.')',
            'ok' => version_compare(PHP_VERSION, self::PHP_MIN, '>='),
            'help' => 'hPanel → Advanced → PHP Configuration → choose PHP 8.3 or 8.4.',
        ]];
        foreach (self::EXTENSIONS as $ext) {
            $checks[] = ['label' => "PHP extension {$ext}", 'ok' => extension_loaded($ext), 'help' => 'hPanel → PHP Configuration → PHP extensions: turn it on.'];
        }
        foreach (['storage', 'storage/app', 'storage/framework', 'storage/logs', 'bootstrap/cache', 'database'] as $dir) {
            $checks[] = ['label' => "{$dir}/ is writable", 'ok' => is_dir($this->base.'/'.$dir) && is_writable($this->base.'/'.$dir), 'help' => 'File Manager → the folder → permissions 755 (or 775).'];
        }
        $checks[] = ['label' => 'The site folder is writable (to save .env)', 'ok' => is_writable($this->base), 'help' => 'File Manager → the site folder → permissions 755.'];
        $checks[] = ['label' => 'PHP packages are present (vendor/)', 'ok' => is_file($this->base.'/vendor/autoload.php'), 'help' => 'Upload the release zip from GitHub (branch "release"), not the plain source.'];
        $checks[] = ['label' => 'Built styles and scripts are present (public/build/)', 'ok' => is_file($this->base.'/public/build/manifest.json'), 'help' => 'Upload the release zip from GitHub (branch "release").'];

        return $checks;
    }

    /**
     * @param  array<string, string>  $in  db_driver, db_host, db_port, db_name, db_user, db_pass
     */
    public function databaseError(array $in): ?string
    {
        if (($in['db_driver'] ?? 'mysql') === 'sqlite') {
            return is_writable($this->base.'/database') ? null : 'The database/ folder is not writable, so the SQLite file cannot be created.';
        }

        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $in['db_host'], (int) $in['db_port'], $in['db_name']),
                $in['db_user'],
                $in['db_pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5],
            );
            $pdo->query('SELECT 1');

            return null;
        } catch (Throwable $e) {
            return 'Could not connect to the database: '.$e->getMessage();
        }
    }

    /**
     * @param  array<string, string>  $in
     */
    public function envContents(array $in): string
    {
        $template = (string) file_get_contents($this->base.'/deploy/env.production.example');
        $sqlite = ($in['db_driver'] ?? 'mysql') === 'sqlite';
        $mailFrom = $in['mail_from'] !== '' ? $in['mail_from'] : $in['admin_email'];

        $values = [
            'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)),
            'APP_URL' => rtrim($in['app_url'], '/'),
            'SESSION_SECURE_COOKIE' => str_starts_with($in['app_url'], 'https://') ? 'true' : 'false',
            'DB_CONNECTION' => $sqlite ? 'sqlite' : 'mysql',
            'DB_HOST' => $sqlite ? '' : $in['db_host'],
            'DB_PORT' => $sqlite ? '' : (string) (int) $in['db_port'],
            'DB_DATABASE' => $sqlite ? $this->base.'/database/database.sqlite' : $in['db_name'],
            'DB_USERNAME' => $sqlite ? '' : $in['db_user'],
            'DB_PASSWORD' => $sqlite ? '' : $in['db_pass'],
            'MAIL_MAILER' => $in['mail_pass'] === '' ? 'log' : 'smtp',
            'MAIL_USERNAME' => $mailFrom,
            'MAIL_PASSWORD' => $in['mail_pass'],
            'MAIL_FROM_ADDRESS' => $mailFrom,
            'FDH_ADMIN_PATH' => $in['admin_path'],
            'VAPID_SUBJECT' => 'mailto:'.$in['admin_email'],
        ];

        foreach ($this->vapidKeys() as $key => $value) {
            $values[$key] = $value;
        }

        foreach ($values as $key => $value) {
            $line = $key.'='.self::quote($value);
            $template = preg_match("/^{$key}=.*$/m", $template)
                ? (string) preg_replace("/^{$key}=.*$/m", str_replace(['\\', '$'], ['\\\\', '\\$'], $line), $template)
                : rtrim($template).PHP_EOL.$line.PHP_EOL;
        }

        return rtrim($template).PHP_EOL.self::IN_PROGRESS.PHP_EOL;
    }

    /**
     * Keys for free push notifications. Left empty if this server's OpenSSL cannot make them;
     * they can be added later with `php artisan fdh:vapid`.
     *
     * @return array<string, string>
     */
    private function vapidKeys(): array
    {
        try {
            $keys = VAPID::createVapidKeys();

            return ['VAPID_PUBLIC_KEY' => $keys['publicKey'], 'VAPID_PRIVATE_KEY' => $keys['privateKey']];
        } catch (Throwable) {
            return [];
        }
    }

    /** A value safe in .env: quoted when it has spaces, quotes, # or $, with those escaped. */
    public static function quote(string $value): string
    {
        $value = str_replace(["\r", "\n"], '', $value);
        if ($value === '' || preg_match('/^[A-Za-z0-9_.:\/@+=-]+$/', $value)) {
            return $value;
        }

        return '"'.str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value).'"';
    }

    /**
     * Write .env, boot Laravel and set everything up. Returns log lines; throws on failure
     * (the .env stays marked in progress so the wizard can be run again).
     *
     * @param  array<string, string>  $in
     * @return list<string>
     */
    public function install(array $in): array
    {
        @set_time_limit(0);
        ignore_user_abort(true);

        file_put_contents($this->base.'/.env', $this->envContents($in));
        @chmod($this->base.'/.env', 0600);
        foreach (glob($this->base.'/bootstrap/cache/*.php') ?: [] as $cached) {
            if (! str_ends_with($cached, '.gitignore')) {
                @unlink($cached);
            }
        }

        if (($in['db_driver'] ?? 'mysql') === 'sqlite' && ! is_file($this->base.'/database/database.sqlite')) {
            touch($this->base.'/database/database.sqlite');
        }

        $app = require $this->base.'/bootstrap/app.php';
        $kernel = $app->make(Kernel::class);
        $kernel->bootstrap();

        $log = [];
        $kernel->call('migrate', ['--force' => true]);
        $log[] = 'Database tables created.';

        $kernel->call('db:seed', ['--force' => true]);
        $log[] = 'Services, FAQs, roles and permissions added.';

        if (($in['samples'] ?? '1') === '1') {
            $kernel->call('db:seed', ['--class' => 'SampleContentSeeder', '--force' => true]);
            $log[] = 'Sample photos and projects added (remove them anytime from the admin dashboard).';
        }

        try {
            $kernel->call('storage:link');
            $log[] = 'Image folder linked.';
        } catch (Throwable) {
            $log[] = 'Could not link public/storage — uploaded images may not show until the host allows symlinks.';
        }

        $app->make(FirstAdmin::class)->create($in['admin_name'], $in['admin_email'], $in['admin_password']);
        $log[] = "Super Admin {$in['admin_email']} created. You will set up two-factor sign-in at the first login.";

        $env = (string) file_get_contents($this->base.'/.env');
        file_put_contents($this->base.'/.env', str_replace(self::IN_PROGRESS.PHP_EOL, '', $env));
        $kernel->call('optimize:clear');
        file_put_contents($this->lockFile(), json_encode(['installed_at' => gmdate('c')]).PHP_EOL);
        @unlink($this->tokenFile());
        $log[] = 'Installer locked.';

        return $log;
    }
}
