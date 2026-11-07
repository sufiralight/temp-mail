<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class InstallationAccessTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().'/temp-mail-'.Str::uuid();
        File::ensureDirectoryExists($this->directory.'/app/private');
        $this->app->useEnvironmentPath($this->directory);
        $this->app->useStoragePath($this->directory);

        config(['app.key' => null, 'session.driver' => 'database', 'database.default' => 'unavailable']);
    }

    protected function tearDown(): void
    {
        File::delete([
            $this->directory.'/.env',
            $this->directory.'/app/private/installation.state',
        ]);

        rmdir($this->directory.'/app/private');
        rmdir($this->directory.'/app');
        rmdir($this->directory);

        parent::tearDown();
    }

    public function test_a_fresh_deployment_redirects_browser_entry_points_to_install(): void
    {
        $this->get('/')->assertRedirect('/install');
        $this->get('/dashboard')->assertRedirect('/install');
        $this->get('/install')->assertOk()->assertSee('Connect your database');
    }

    public function test_a_starter_environment_file_does_not_mark_setup_complete(): void
    {
        File::put($this->directory.'/.env', 'APP_NAME=Temp Mail');

        $this->get('/install')->assertOk()->assertSee('Connect your database');
    }

    public function test_the_database_screen_renders_fields_without_a_submission_form(): void
    {
        $this->get('/install')
            ->assertOk()
            ->assertSee('Database Host')
            ->assertSee('for="database_port"', false)
            ->assertSee('aria-controls="database_password"', false)
            ->assertSee('data-password-show', false)
            ->assertSee('data-password-hide', false)
            ->assertSee('Test Connection & Continue')
            ->assertDontSee('<form', false);
    }

    public function test_the_admin_view_exposes_the_current_step_and_password_error(): void
    {
        $this->view('install.admin', [
            'fieldErrors' => ['password' => 'Enter a stronger password.'],
            'formError' => 'Review the highlighted fields.',
        ])
            ->assertSee('Step 2 of 2')
            ->assertSee('Database connected')
            ->assertSee('data-focus-on-load', false)
            ->assertSee('aria-describedby="password-hint password-error"', false)
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('role="alert"', false)
            ->assertSee('Enter a stronger password.');
    }

    public function test_an_installed_deployment_redirects_the_installer_to_login(): void
    {
        File::put($this->directory.'/.env', 'APP_NAME=Temp Mail');
        File::put($this->directory.'/app/private/installation.state', "complete\n");

        $this->get('/install')->assertRedirect('/login');
    }

    public function test_an_installed_deployment_passes_regular_requests_through(): void
    {
        File::put($this->directory.'/.env', 'APP_NAME=Temp Mail');
        File::put($this->directory.'/app/private/installation.state', "complete\n");
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32)), 'session.driver' => 'array']);

        $this->get('/')->assertOk();
    }

    public function test_missing_environment_file_opens_recovery_even_with_a_configuration_cache(): void
    {
        File::put($this->directory.'/app/private/installation.state', "complete\n");
        $this->app->instance('config_loaded_from_cache', true);

        $this->get('/')->assertRedirect('/install');
        $this->get('/install')
            ->assertOk()
            ->assertSee('Restore configuration')
            ->assertSee('Operator verification is required')
            ->assertDontSee('Connect your database')
            ->assertDontSee('name="database_password"', false);
    }

    public function test_an_invalid_marker_requires_operator_intervention(): void
    {
        File::put($this->directory.'/app/private/installation.state', 'invalid');

        $this->get('/install')->assertServiceUnavailable()->assertSee('Installation status unavailable');
    }

    public function test_cached_configuration_without_state_or_environment_requires_operator_intervention(): void
    {
        $this->app->instance('config_loaded_from_cache', true);

        $this->get('/install')->assertServiceUnavailable()->assertSee('Installation status unavailable');
    }

    public function test_an_installed_deployment_rejects_direct_installer_submissions(): void
    {
        File::put($this->directory.'/.env', 'APP_NAME=Temp Mail');
        File::put($this->directory.'/app/private/installation.state', "complete\n");

        $this->post('/install')->assertNotFound();
        $this->post('/install/restore')->assertNotFound();
    }

    public function test_non_browser_requests_are_unavailable_during_setup(): void
    {
        $this->getJson('/')->assertServiceUnavailable();
        $this->post('/dashboard')->assertServiceUnavailable();
    }
}
