<?php

namespace Tests\Feature;

use App\Installation\Database;
use App\Installation\InstallationException;
use App\Installation\InstallationStore;
use App\Installation\Installer;
use App\Installation\Requirements;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\FixtureDatabase;
use Tests\TestCase;

class InstallationTest extends TestCase
{
    private string $directory;

    private FixtureDatabase $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->directory = storage_path('framework/testing/installation-'.bin2hex(random_bytes(8)));
        mkdir($this->directory, 0700, true);
        touch($this->directory.'/fixture.sqlite');
        config(['app.key' => 'base64:'.base64_encode(str_repeat('t', 32)), 'app.debug' => false, 'installation.directory' => $this->directory, 'installation.enabled' => true, 'installation.tls_required' => false, 'installation.database_hosts' => ['127.0.0.1', 'localhost'], 'installation.database_port' => 3306]);
        $this->app->forgetInstance('encrypter');
        file_put_contents($this->directory.'/provisioned', 'fixture');
        app(InstallationStore::class)->write(['id' => 'cb592916-2e70-4f25-918b-a1c53e3f051f', 'phase' => 'fresh', 'profile' => 'local', 'port' => 8000, 'access_expires' => time() + 7200]);
        $this->database = new FixtureDatabase($this->directory.'/fixture.sqlite');
        $this->app->instance(Database::class, $this->database);
        $this->mock(Requirements::class, function ($mock): void {
            $mock->shouldReceive('checks')->andReturn(['Fixture requirements' => true]);
            $mock->shouldReceive('passed')->andReturn(true);
        });
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1']);
    }

    protected function tearDown(): void
    {
        DB::purge('installed');
        File::deleteDirectory($this->directory);
        parent::tearDown();
    }

    public static function localOrigins(): array
    {
        return [['localhost', '127.0.0.1'], ['127.0.0.1', 'localhost'], ['localhost', 'localhost'], ['127.0.0.1', '127.0.0.1']];
    }

    #[DataProvider('localOrigins')]
    public function test_both_local_origins_work_without_canonical_redirect(string $host, string $canonical): void
    {
        config(['app.url' => 'http://'.$canonical.':8000']);
        $this->get('http://'.$host.':8000/install')->assertOk()->assertSee('Welcome &amp; Requirements', false)->assertDontSee('Setup Credential')->assertDontSee('install/authorize');
    }

    public static function badConnections(): array
    {
        return [
            ['localhost', '203.0.113.5', []],
            ['127.0.0.1', '203.0.113.5', ['X-Forwarded-For' => '127.0.0.1']],
            ['localhost.attacker.test', '127.0.0.1', []],
            ['attacker.test', '127.0.0.1', []],
            ['localhost', '127.0.0.1', ['Forwarded' => 'for=127.0.0.1']],
            ['localhost', '127.0.0.1', ['X-Forwarded-Host' => 'localhost']],
            ['localhost', '127.0.0.1', ['X-Forwarded-Proto' => 'http']],
            ['localhost', '127.0.0.1', ['X-Real-IP' => '127.0.0.1']],
            ['localhost', '172.17.0.1', []],
        ];
    }

    #[DataProvider('badConnections')]
    public function test_spoofed_hosts_forwarded_headers_and_remote_peers_fail(string $host, string $peer, array $headers): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => $peer])->get('http://'.$host.':8000/install', $headers)->assertForbidden();
    }

    public function test_ipv6_connection_peer_supports_both_approved_hosts(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '::1'])->get('http://localhost:8000/install')->assertOk();
    }

    public function test_environment_or_host_alone_does_not_enable_installer(): void
    {
        config(['installation.enabled' => false]);
        $this->get('http://localhost:8000/install')->assertForbidden();
    }

    public function test_missing_or_corrupt_state_fails_closed(): void
    {
        file_put_contents($this->directory.'/state', 'broken');
        $this->get('http://localhost:8000/install')->assertStatus(503)->assertDontSee('broken');
    }

    public function test_expired_authorization_and_wrong_port_fail(): void
    {
        $this->get('http://localhost:8001/install')->assertForbidden();
        $state = app(InstallationStore::class)->read();
        $state['access_expires'] = time() - 1;
        app(InstallationStore::class)->write($state);
        $this->get('http://localhost:8000/install')->assertForbidden();
    }

    public function test_step_order_and_login_boundary(): void
    {
        $this->get('http://localhost:8000/install/admin')->assertStatus(409);
        $this->get('http://localhost:8000/install/complete')->assertStatus(409);
        $this->get('http://localhost:8000/login')->assertForbidden();
        $this->get('/register')->assertNotFound();
        $this->get('/dashboard')->assertNotFound();
    }

    public function test_requirements_block_database_step(): void
    {
        $this->mock(Requirements::class, fn ($mock) => $mock->shouldReceive('passed')->andReturn(false));
        $this->get('http://localhost:8000/install/database')->assertStatus(422);
    }

    public function test_connection_failure_is_safe_and_password_is_not_flashed(): void
    {
        $this->database->fail = true;
        $this->from('http://localhost:8000/install/database')->post('http://localhost:8000/install/database', $this->input())->assertSessionHasErrors('db_host')->assertSessionMissing('_old_input.db_password');
        $this->assertSame('fresh', app(InstallationStore::class)->read()['phase']);
    }

    public function test_invalid_input_invalidates_previous_database_proof(): void
    {
        app(Installer::class)->probe($this->input(), 'first-browser');
        $this->post('http://localhost:8000/install/database', ['db_host' => 'evil.test'])->assertSessionHasErrors();
        $this->assertSame('fresh', app(InstallationStore::class)->read()['phase']);
        $this->assertArrayNotHasKey('database', app(InstallationStore::class)->read());
    }

    public function test_invalid_admin_never_creates_an_account(): void
    {
        $this->post('http://localhost:8000/install/admin', ['name' => '', 'email' => 'bad', 'password' => 'short', 'password_confirmation' => 'other', 'role' => 'admin'])->assertSessionHasErrors(['name', 'email', 'password'])->assertSessionMissing('_old_input.password');
    }

    public function test_successful_workflow_creates_one_super_admin_and_durable_lock(): void
    {
        $key = config('app.key');
        $installer = app(Installer::class);
        $installer->probe($this->input(), 'owner');
        $installer->administrator($this->adminInput(), 'owner');
        $this->assertStringNotContainsString('db-secret', file_get_contents($this->directory.'/state'));
        $installer->complete('owner');
        $this->assertTrue(app(InstallationStore::class)->completed());
        $admin = DB::connection('installed')->table('users')->first();
        $this->assertSame('super_admin', $admin->role);
        $this->assertSame('owner@example.test', $admin->email);
        $this->assertTrue(Hash::driver('argon2id')->check($this->adminInput()['password'], $admin->password));
        $this->assertSame(1, DB::connection('installed')->table('installation_receipts')->count());
        $this->assertSame($key, config('app.key'));
        $this->assertArrayNotHasKey('admin', app(InstallationStore::class)->read());
        $this->get('http://localhost:8000/install')->assertStatus(410);
        $this->post('http://localhost:8000/install/complete')->assertStatus(410);
        $this->get('http://localhost:8000/login')->assertOk()->assertSee('Installation complete')->assertDontSee('<form', false);
        $this->assertSame(1, DB::connection('installed')->table('users')->count());
    }

    public function test_another_session_cannot_use_verified_database(): void
    {
        app(Installer::class)->probe($this->input(), 'owner');
        $this->expectException(InstallationException::class);
        app(Installer::class)->administrator($this->adminInput(), 'other-session');
    }

    public function test_independent_process_lock_blocks_competing_finalization(): void
    {
        $installer = app(Installer::class);
        $installer->probe($this->input(), 'owner');
        $installer->administrator($this->adminInput(), 'owner');
        $process = proc_open([PHP_BINARY, base_path('tests/Support/hold-lock.php'), $this->directory], [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
        try {
            $this->assertSame("locked\n", fgets($pipes[1]));
            try {
                $installer->complete('owner');
                $this->fail('Concurrent lock should reject completion');
            } catch (InstallationException $exception) {
                $this->assertSame('busy', $exception->reason);
            }
            $this->assertSame('ready', app(InstallationStore::class)->read()['phase']);
        } finally {
            fwrite($pipes[0], "release\n");
            foreach ($pipes as $pipe) {
                fclose($pipe);
            }
            proc_close($process);
        }
        $installer->complete('owner');
        $this->assertTrue(app(InstallationStore::class)->completed());
    }

    public function test_partial_ddl_recovery_refuses_to_rerun_or_reset(): void
    {
        $installer = app(Installer::class);
        $installer->probe($this->input(), 'owner');
        $installer->administrator($this->adminInput(), 'owner');
        $state = app(InstallationStore::class)->read();
        $state['phase'] = 'finalizing';
        $state['schema_fingerprint'] = $installer->schemaFingerprint();
        app(InstallationStore::class)->write($state);
        DB::connection('installed')->statement('CREATE TABLE valuable_data (id INTEGER)');
        $this->artisan('install:recover --reviewed')->assertFailed();
        $this->assertTrue(DB::connection('installed')->getSchemaBuilder()->hasTable('valuable_data'));
        $this->assertSame('finalizing', app(InstallationStore::class)->read()['phase']);
    }

    public function test_committed_receipt_recovers_without_duplicate_admin(): void
    {
        $installer = app(Installer::class);
        $installer->probe($this->input(), 'owner');
        $installer->administrator($this->adminInput(), 'owner');
        $before = app(InstallationStore::class)->read();
        $installer->complete('owner');
        $before['phase'] = 'finalizing';
        $before['schema_fingerprint'] = $installer->schemaFingerprint();
        app(InstallationStore::class)->write($before);
        $this->artisan('install:recover --reviewed')->assertSuccessful();
        $this->assertTrue(app(InstallationStore::class)->completed());
        $this->assertSame(1, DB::connection('installed')->table('users')->count());
    }

    public function test_configuration_never_contains_installed_database_password(): void
    {
        app(Installer::class)->probe($this->input(), 'owner');
        $this->assertStringNotContainsString('db-secret', json_encode(config()->all()));
    }

    public function test_http_flow_redirects_to_login_and_ignores_submitted_privileges(): void
    {
        $this->post('http://localhost:8000/install/database', $this->input())->assertRedirect('/install/admin');
        $this->withCookie(config('session.cookie'), session()->getId());
        $this->post('http://localhost:8000/install/admin', [...$this->adminInput(), 'password_confirmation' => $this->adminInput()['password'], 'role' => 'user', 'email_verified_at' => '2020-01-01'])->assertRedirect('/install/complete');
        $this->get('http://localhost:8000/install/complete')->assertOk();
        $this->post('http://localhost:8000/install/complete')->assertRedirect('/login');
        $admin = DB::connection('installed')->table('users')->first();
        $this->assertSame('super_admin', $admin->role);
        $this->assertNull($admin->email_verified_at);
    }

    public function test_cross_origin_post_is_rejected_even_on_loopback(): void
    {
        $this->post('http://localhost:8000/install/database', $this->input(), ['Origin' => 'https://attacker.test'])->assertForbidden();
    }

    public function test_requirements_inspect_missing_key_without_replacing_it(): void
    {
        config(['app.key' => '']);
        $checks = (new Requirements)->checks();
        $this->assertFalse($checks['Existing valid application key']);
        $this->assertSame('', config('app.key'));
    }

    public function test_provisioning_cannot_replace_or_renew_completed_state(): void
    {
        $this->artisan('install:prepare --restricted')->assertFailed();
        $state = app(InstallationStore::class)->read();
        $state['phase'] = 'completed';
        app(InstallationStore::class)->write($state);
        $this->artisan('install:prepare --restricted --renew')->assertFailed();
        $this->assertTrue(app(InstallationStore::class)->completed());
    }

    public function test_proof_expiry_cannot_create_administrator(): void
    {
        app(Installer::class)->probe($this->input(), 'owner');
        $state = app(InstallationStore::class)->read();
        $state['proof'] = time() - 1;
        app(InstallationStore::class)->write($state);
        $this->expectException(InstallationException::class);
        app(Installer::class)->administrator($this->adminInput(), 'owner');
    }

    public function test_cached_configuration_restart_does_not_serialize_database_secret(): void
    {
        $installer = app(Installer::class);
        $installer->probe($this->input(), 'owner');
        $installer->administrator($this->adminInput(), 'owner');
        $installer->complete('owner');
        $relative = str_replace('\\', '/', substr($this->directory, strlen(base_path()) + 1)).'/config.php';
        $env = array_merge(getenv(), ['APP_ENV' => 'local', 'APP_KEY' => config('app.key'), 'APP_CONFIG_CACHE' => $relative, 'INSTALLATION_DIRECTORY' => $this->directory, 'INSTALLATION_TLS_REQUIRED' => 'false']);
        $run = function (array $arguments) use ($env): array {
            $process = proc_open([PHP_BINARY, ...$arguments], [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, base_path(), $env);
            fclose($pipes[0]);
            $out = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            return [proc_close($process), $out, $error];
        };
        [$status, , $error] = $run(['artisan', 'config:cache']);
        $this->assertSame(0, $status, $error);
        $this->assertStringNotContainsString('db-secret', file_get_contents($this->directory.'/config.php'));
        [$status, $output, $error] = $run(['tests/Support/cached-probe.php']);
        $this->assertSame(0, $status, $error);
        $this->assertSame('1', trim($output));
    }

    public function test_local_flags_cannot_enable_production_profile(): void
    {
        $state = app(InstallationStore::class)->read();
        $state['profile'] = 'unverified-proxy';
        app(InstallationStore::class)->write($state);
        $this->get('http://localhost:8000/install')->assertForbidden();
    }

    public function test_database_probe_rate_limit_is_enforced(): void
    {
        $this->database->fail = true;
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('http://localhost:8000/install/database', $this->input())->assertRedirect();
        }
        $this->post('http://localhost:8000/install/database', $this->input())->assertStatus(429);
    }

    private function input(): array
    {
        return ['db_host' => '127.0.0.1', 'db_port' => 3306, 'db_name' => 'isolated_fixture', 'db_user' => 'fixture', 'db_password' => 'db-secret'];
    }

    private function adminInput(): array
    {
        return ['name' => 'Owner', 'email' => 'OWNER@example.test', 'password' => 'A long fixture password ✓'];
    }
}
