<?php

namespace Tests\Feature;

use Dotenv\Dotenv;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PDO;
use RuntimeException;
use Tests\TestCase;

class FirstInstallationTest extends TestCase
{
    private const string KEY = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';

    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().'/temp-mail-install-'.Str::uuid();
        File::ensureDirectoryExists($this->directory.'/app/private');
        $this->app->useStoragePath($this->directory);
        $this->app->useEnvironmentPath($this->directory);
        $this->app->useBootstrapPath($this->directory);
        File::put($this->directory.'/app/private/installation.key', self::KEY);
        config(['app.key' => null, 'database.default' => 'unavailable', 'session.driver' => 'database']);
    }

    protected function tearDown(): void
    {
        $path = realpath($this->directory);
        if ($path !== false && realpath(dirname($path)) === realpath(sys_get_temp_dir()) && str_starts_with(basename($path), 'temp-mail-install-')) {
            File::deleteDirectory($path);
        }

        parent::tearDown();
    }

    public function test_connection_submissions_require_csrf_and_operator_authorization(): void
    {
        $token = $this->openForm('/install');

        $this->post('/install/database', $this->databaseFields())->assertStatus(419);
        $this->post('/install/database', ['_token' => $token, 'installation_key' => 'wrong'] + $this->databaseFields())
            ->assertForbidden()
            ->assertSee('Enter the installation key from your hosting panel.');
    }

    public function test_a_public_installation_requires_https(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->get('http://example.test/install')
            ->assertForbidden()
            ->assertSee('Open this page over HTTPS before continuing setup.');
    }

    public function test_repeated_setup_attempts_are_rate_limited(): void
    {
        $token = $this->openForm('/install');
        $submission = ['_token' => $token, 'installation_key' => 'wrong'] + $this->databaseFields();

        for ($attempt = 0; $attempt < 15; $attempt++) {
            $this->post('/install/database', $submission)->assertForbidden();
        }

        $this->post('/install/database', $submission)->assertTooManyRequests();
    }

    public function test_a_failed_connection_leaves_configuration_untouched_and_requires_a_fresh_test(): void
    {
        $this->useDisposableDatabase();
        $this->authorizeAndTestDatabase();
        $token = $this->openForm('/install');
        DB::extend('installation', fn () => throw new RuntimeException('internal connection detail'));

        $this->post('/install/database', ['_token' => $token] + $this->databaseFields())
            ->assertUnprocessable()
            ->assertSee('Could not connect')
            ->assertDontSee('internal connection detail');
        $this->get('/install/admin')->assertRedirect('/install');
        $this->assertFileDoesNotExist($this->directory.'/.env');
        $this->assertFileDoesNotExist($this->directory.'/app/private/installation.state');
    }

    public function test_the_first_installation_creates_one_admin_and_locks_setup(): void
    {
        $this->useDisposableDatabase();
        File::put($this->directory.'/.env', "# Preserve operator settings\nAPP_NAME=Workspace\nDB_CONNECTION=sqlite\n");
        $token = $this->authorizeAndTestDatabase();

        $this->post('/install/admin', ['_token' => $token] + $this->adminFields())
            ->assertRedirect('/login?installation=complete');

        $environment = File::get($this->directory.'/.env');
        $this->assertStringContainsString('# Preserve operator settings', $environment);
        $this->assertSame('mysql', Dotenv::parse($environment)['DB_CONNECTION']);
        $this->assertSame($this->databaseFields()['database_password'], Dotenv::parse($environment)['DB_PASSWORD']);
        $this->assertStringStartsWith('complete ', File::get($this->directory.'/app/private/installation.state'));
        $this->assertFirstAdministrator();
        $this->get('/login?installation=complete')->assertOk()->assertSee('Installation complete. Sign in to continue.');
        $this->post('/install/admin', ['_token' => $token] + $this->adminFields())->assertNotFound();
        $this->post('/install/database', ['_token' => $token] + $this->databaseFields())->assertNotFound();
    }

    public function test_a_failed_environment_write_can_resume_without_creating_another_admin(): void
    {
        $this->useDisposableDatabase();
        $token = $this->authorizeAndTestDatabase();
        mkdir($this->directory.'/.env');

        $this->post('/install/admin', ['_token' => $token] + $this->adminFields())
            ->assertUnprocessable()
            ->assertSee('Check that the .env path is a writable file.');
        $this->assertStringStartsWith('pending ', File::get($this->directory.'/app/private/installation.state'));
        $this->assertFirstAdministrator();

        rmdir($this->directory.'/.env');
        $this->post('/install/admin', ['_token' => $token] + $this->adminFields())->assertRedirect('/login?installation=complete');
        $this->assertFirstAdministrator();
    }

    public function test_an_existing_database_is_preserved(): void
    {
        $this->useDisposableDatabase();
        $pdo = new PDO('sqlite:'.$this->directory.'/database.sqlite');
        $pdo->exec('CREATE TABLE existing_data (value TEXT)');
        $pdo->exec("INSERT INTO existing_data VALUES ('keep me')");
        $token = $this->authorizeAndTestDatabase();

        $this->post('/install/admin', ['_token' => $token] + $this->adminFields())
            ->assertUnprocessable()
            ->assertSee('Choose an empty database for the first installation.');
        $this->assertSame('keep me', $pdo->query('SELECT value FROM existing_data')->fetchColumn());
        $this->assertFileDoesNotExist($this->directory.'/app/private/installation.state');
    }

    public function test_an_interrupted_schema_migration_recreates_a_missing_index(): void
    {
        $this->useDisposableDatabase();
        $identity = '11111111-1111-4111-8111-111111111111';
        $target = hash('sha256', '["localhost",3306,"temp_mail"]');
        File::put($this->directory.'/app/private/installation.state', "pending {$identity} {$target}\n");
        $pdo = new PDO('sqlite:'.$this->directory.'/database.sqlite');
        $pdo->exec('CREATE TABLE installation_identity (slot INTEGER PRIMARY KEY, id TEXT NOT NULL, admin_user_id INTEGER)');
        $pdo->exec("INSERT INTO installation_identity (slot, id) VALUES (1, '{$identity}')");
        $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT, password TEXT, created_at TEXT, updated_at TEXT)');
        $token = $this->authorizeAndTestDatabase();

        $this->post('/install/admin', ['_token' => $token] + $this->adminFields())
            ->assertRedirect('/login?installation=complete');
        $this->assertFirstAdministrator();
        $this->assertContains('users_email_unique', array_column($pdo->query("PRAGMA index_list('users')")->fetchAll(), 'name'));
    }

    public function test_a_concurrent_installation_request_is_rejected(): void
    {
        $token = $this->openForm('/install');
        $lock = fopen($this->directory.'/app/private/installation.lock', 'c');
        flock($lock, LOCK_EX);

        try {
            $this->post('/install/database', ['_token' => $token, 'installation_key' => self::KEY] + $this->databaseFields())
                ->assertConflict()
                ->assertSee('Another setup request is in progress.');
        } finally {
            fclose($lock);
        }
    }

    private function useDisposableDatabase(): void
    {
        $file = $this->directory.'/database.sqlite';
        DB::extend('installation', fn (array $config, string $name) => new SQLiteConnection(
            new PDO('sqlite:'.$file), $file, '', ['name' => $name, 'driver' => 'sqlite'],
        ));
    }

    private function authorizeAndTestDatabase(): string
    {
        $token = $this->openForm('/install');
        $response = $this->post('/install/database', ['_token' => $token, 'installation_key' => self::KEY] + $this->databaseFields());
        $response->assertRedirect('/install/admin');
        $this->useCookie($response);

        return $this->openForm('/install/admin');
    }

    private function openForm(string $uri): string
    {
        $response = $this->get($uri)->assertOk();
        $this->useCookie($response);
        preg_match('/name="_token" value="([^"]+)"/', $response->getContent(), $matches);
        $this->assertArrayHasKey(1, $matches);

        return $matches[1];
    }

    private function useCookie(TestResponse $response): void
    {
        $cookie = $response->getCookie('temp_mail_install', false);
        $this->assertNotNull($cookie);
        $this->withUnencryptedCookie($cookie->getName(), $cookie->getValue());
    }

    private function assertFirstAdministrator(): void
    {
        $pdo = new PDO('sqlite:'.$this->directory.'/database.sqlite');
        $users = $pdo->query('SELECT email, password, is_admin FROM users')->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(1, $users);
        $this->assertSame('owner@example.com', $users[0]['email']);
        $this->assertSame(1, $users[0]['is_admin']);
        $this->assertTrue(Hash::check($this->adminFields()['password'], $users[0]['password']));
    }

    /** @return array<string, string> */
    private function databaseFields(): array
    {
        return [
            'database_host' => 'localhost',
            'database_port' => '3306',
            'database_name' => 'temp_mail',
            'database_username' => 'operator',
            'database_password' => ' password # ${literal} " \\ end ',
        ];
    }

    /** @return array<string, string> */
    private function adminFields(): array
    {
        return [
            'name' => 'Workspace Owner',
            'email' => 'owner@example.com',
            'password' => 'A strong setup password',
            'password_confirmation' => 'A strong setup password',
        ];
    }
}
