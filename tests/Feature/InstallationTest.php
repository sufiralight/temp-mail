<?php

namespace Tests\Feature;

use App\Installation\DatabaseSetup;
use App\Installation\State;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class InstallationTest extends TestCase
{
    private string $directory;

    private array $database = [
        'engine' => 'mysql', 'host' => '127.0.0.1', 'port' => 3306,
        'database' => 'isolated_test', 'username' => 'installer',
        'database_password' => 'Only-a-fixture-$"\\',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->directory = sys_get_temp_dir().'/temp-mail-test-'.Str::uuid();
        File::makeDirectory($this->directory, 0700);
        $this->app->useEnvironmentPath($this->directory);
        $key = 'base64:'.base64_encode(random_bytes(32));
        File::put($this->directory.'/.env', "APP_KEY={$key}\nCUSTOM_SETTING=preserved\n");
        config([
            'app.key' => $key,
            'app.debug' => false,
            'app.url' => 'https://example.test',
            'installation.path' => $this->directory,
            'installation.operator' => 'test-operator',
            'session.files' => $this->directory,
            'cache.stores.file.path' => $this->directory.'/cache',
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);
        parent::tearDown();
    }

    private function operator(): static
    {
        return $this->withServerVariables(['REMOTE_USER' => 'test-operator', 'HTTPS' => 'on'])
            ->withHeaders(['Host' => 'example.test']);
    }

    private function submit(string $path, array $fields = [])
    {
        return $this->withSession(['_token' => 'fixture-token'])
            ->post('https://example.test/install/'.$path, $fields + ['_token' => 'fixture-token']);
    }

    public function test_anonymous_and_header_spoofed_requests_cannot_access_installation(): void
    {
        $this->get('https://example.test/install')->assertNotFound();
        $this->withHeaders([
            'Host' => 'localhost', 'Remote-User' => 'test-operator',
            'X-Forwarded-For' => '127.0.0.1', 'X-Forwarded-Proto' => 'https',
        ])->get('https://example.test/install')->assertNotFound();
        $this->withServerVariables(['REMOTE_USER' => 'test-operator', 'HTTPS' => 'off'])
            ->get('http://example.test/install')->assertNotFound();
    }

    public function test_local_policy_requires_opt_in_and_a_direct_loopback_peer(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        config(['installation.local' => true]);
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get('http://localhost/install')->assertOk()->assertSee('Database Configuration');
        $this->get('http://127.0.0.1/install')->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.8'])
            ->withHeaders(['X-Forwarded-For' => '127.0.0.1'])
            ->get('http://localhost/install')->assertNotFound();
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get('http://localhost/install')->assertNotFound();
    }

    public function test_first_run_requires_csrf_and_verified_database_before_advancing(): void
    {
        $this->operator()->get('https://example.test/')->assertRedirect('/install');
        $this->get('https://example.test/install')->assertOk()->assertSee('Database Configuration')->assertDontSee('Administrator Account');
        $this->post('https://example.test/install/test', $this->database)->assertStatus(419);
        $this->submit('next')->assertSessionHasErrors('connection');
        $this->submit('commit')->assertSessionHasErrors('connection');
        $this->submit('test', ['host' => 'localhost;dbname=unsafe'] + $this->database)
            ->assertSessionHasErrors('host')->assertSessionMissing('_old_input');
    }

    public function test_connection_failure_is_sanitized_and_password_is_not_flashed(): void
    {
        $this->mock(DatabaseSetup::class)->shouldReceive('probe')->once()
            ->andThrow(new RuntimeException('private connection detail'));
        $this->operator()->submit('test', $this->database)
            ->assertSessionHasErrors('connection')->assertSessionMissing('_old_input');
        $this->get('https://example.test/install')->assertOk()->assertDontSee('private connection detail')
            ->assertDontSee($this->database['database_password']);
    }

    public function test_verified_values_are_encrypted_expire_and_are_revoked_when_going_back(): void
    {
        $this->mock(DatabaseSetup::class)->shouldReceive('probe')->twice();
        $this->operator()->submit('test', $this->database)
            ->assertSessionHas('installation.verified.ciphertext')
            ->assertSessionMissing('installation.fields.database_password');
        $this->submit('next');
        $this->get('https://example.test/install')->assertOk()->assertSee('Administrator Account');
        $this->submit('commit', ['email' => 'invalid'])->assertSessionHasErrors(['name', 'email', 'password']);
        $this->submit('next', ['direction' => 'back'])->assertSessionMissing('installation.verified');
        $this->get('https://example.test/install')->assertSee('isolated_test')->assertDontSee($this->database['database_password']);
        $this->submit('test', $this->database);
        $this->travel(11)->minutes();
        $this->submit('next')->assertSessionHasErrors('connection');
    }

    public function test_connection_attempts_are_rate_limited(): void
    {
        $this->mock(DatabaseSetup::class)->shouldReceive('probe')->times(6);
        $this->operator();
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->submit('test', $this->database)->assertRedirect('/install');
        }
        $this->submit('test', $this->database)->assertStatus(429);
    }

    public function test_exclusive_lock_rejects_a_second_writer(): void
    {
        $state = app(State::class);
        $this->expectExceptionMessage('Installation lock held.');
        $state->locked(fn () => $state->locked(fn () => null));
    }

    public function test_incomplete_and_corrupt_state_fail_closed(): void
    {
        foreach (['in-progress-v1', 'damaged'] as $marker) {
            File::put($this->directory.'/state', $marker);
            $this->operator()->get('https://example.test/install')->assertStatus(503);
            $this->submit('commit')->assertNotFound();
        }
    }

    public function test_installation_writes_configuration_and_completion_blocks_duplicate_requests(): void
    {
        $mock = $this->mock(DatabaseSetup::class);
        $mock->shouldReceive('probe')->once();
        $mock->shouldReceive('provision')->once()->andReturnUsing(function ($database, $admin, $begin, $finish): void {
            $this->assertSame($this->database['database_password'], $database['database_password']);
            $this->assertSame('owner@example.test', $admin['email']);
            $begin();
            $this->assertSame('recovery', app(State::class)->status());
            $finish();
        });
        $this->operator()->submit('test', $this->database);
        $this->submit('next');
        $this->submit('commit', [
            'name' => 'Owner', 'email' => 'OWNER@EXAMPLE.TEST',
            'password' => 'fixture-password-123', 'password_confirmation' => 'fixture-password-123',
        ])->assertRedirect('/install');
        $this->get('https://example.test/install')->assertSee('Installation Complete')->assertSee('Continue to Login');
        $this->get('https://example.test/login')->assertStatus(503)->assertSee('authentication module');
        $this->submit('commit')->assertNotFound();
        $this->assertSame('complete', app(State::class)->status());
        $env = Dotenv::parse(File::get($this->directory.'/.env'));
        $this->assertSame($this->database['database_password'], $env['DB_PASSWORD']);
        $this->assertSame('preserved', $env['CUSTOM_SETTING']);
        $this->assertSame(config('app.key'), $env['APP_KEY']);
        File::delete($this->directory.'/.env');
        $this->get('https://example.test/install')->assertStatus(503);
        $this->assertSame('complete', app(State::class)->status());
    }

    public function test_migration_failure_requires_operator_recovery(): void
    {
        $mock = $this->mock(DatabaseSetup::class);
        $mock->shouldReceive('probe')->once();
        $mock->shouldReceive('provision')->once()->andReturnUsing(function ($database, $admin, $begin): void {
            $begin();
            throw new RuntimeException('private migration detail');
        });
        $this->operator()->submit('test', $this->database);
        $this->submit('next');
        $this->submit('commit', [
            'name' => 'Owner', 'email' => 'owner@example.test',
            'password' => 'fixture-password-123', 'password_confirmation' => 'fixture-password-123',
        ])->assertStatus(503)->assertDontSee('private migration detail');
        $this->assertSame('recovery', app(State::class)->status());
        $this->submit('commit')->assertNotFound();
    }
}
