<?php

namespace Tests\Feature;

use App\Support\Install\WebInstaller;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class WebInstallerTest extends TestCase
{
    private string $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->site = sys_get_temp_dir().'/fdh-install-test-'.bin2hex(random_bytes(4));
        File::ensureDirectoryExists($this->site.'/storage/app');
        File::ensureDirectoryExists($this->site.'/deploy');
        File::copy(base_path('deploy/env.production.example'), $this->site.'/deploy/env.production.example');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->site);
        parent::tearDown();
    }

    /**
     * @return array<string, string>
     */
    private function answers(array $overrides = []): array
    {
        return $overrides + [
            'db_driver' => 'mysql', 'db_host' => 'localhost', 'db_port' => '3306', 'db_name' => 'u1_fdh', 'db_user' => 'u1_fdh', 'db_pass' => 'p@ss "word" $x',
            'app_url' => 'https://fdh.com.bd', 'admin_path' => 'admin-secret', 'mail_from' => 'info@fdh.com.bd', 'mail_pass' => '',
            'admin_name' => 'Engr. Noman', 'admin_email' => 'noman@example.com', 'admin_password' => 'x', 'samples' => '1',
        ];
    }

    public function test_installer_is_open_on_a_fresh_upload_and_closed_after(): void
    {
        $installer = new WebInstaller($this->site);
        $this->assertFalse($installer->closed());

        // A server set up by hand (working .env with a key) is never touched.
        File::put($this->site.'/.env', "APP_KEY=base64:abc\n");
        $this->assertTrue($installer->closed());

        File::delete($this->site.'/.env');
        File::put($installer->lockFile(), '{}');
        $this->assertTrue($installer->closed());
    }

    public function test_setup_code_is_required(): void
    {
        $installer = new WebInstaller($this->site);
        $token = $installer->token();

        $this->assertFalse($installer->tokenMatches(''));
        $this->assertFalse($installer->tokenMatches('wrong'));
        $this->assertTrue($installer->tokenMatches($token));
    }

    public function test_env_is_written_for_production_with_safe_quoting(): void
    {
        $env = (new WebInstaller($this->site))->envContents($this->answers());

        $this->assertStringContainsString('APP_ENV=production', $env);
        $this->assertStringContainsString('APP_DEBUG=false', $env);
        $this->assertMatchesRegularExpression('/^APP_KEY=base64:[A-Za-z0-9+\/=]{40,}$/m', $env);
        $this->assertStringContainsString('APP_URL=https://fdh.com.bd', $env);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=true', $env);
        $this->assertStringContainsString('DB_PASSWORD="p@ss \"word\" \$x"', $env);
        $this->assertStringContainsString('FDH_ADMIN_PATH=admin-secret', $env);
        $this->assertStringContainsString('MAIL_MAILER=log', $env);
        $this->assertStringContainsString('FDH_INSTALLING=true', $env);
    }

    public function test_sqlite_option_points_at_the_database_folder(): void
    {
        $env = (new WebInstaller($this->site))->envContents($this->answers(['db_driver' => 'sqlite']));

        $this->assertStringContainsString('DB_CONNECTION=sqlite', $env);
        $this->assertMatchesRegularExpression('#^DB_DATABASE=.*database/database.sqlite"?$#m', $env);
    }
}
