<?php

namespace Tests\Feature;

use App\Installation\Database;
use App\Installation\InstallationException;
use Pdo\Mysql;
use Tests\TestCase;

class DatabaseSecurityTest extends TestCase
{
    public function test_required_tls_cannot_be_bypassed_without_ca(): void
    {
        config(['installation.tls_required' => true, 'installation.tls_ca' => null]);
        $this->expectException(InstallationException::class);
        app(Database::class)->configuration($this->input());
    }

    public function test_tls_configuration_always_verifies_certificate(): void
    {
        config(['installation.tls_required' => true, 'installation.tls_ca' => __FILE__]);
        $configuration = app(Database::class)->configuration($this->input());
        $this->assertTrue($configuration['options'][Mysql::ATTR_SSL_VERIFY_SERVER_CERT]);
        $this->assertSame('utf8mb4_0900_ai_ci', $configuration['collation']);
    }

    public function test_probe_connection_failure_has_no_secret_or_previous_exception(): void
    {
        config(['installation.tls_required' => false]);
        $configuration = app(Database::class)->configuration($this->input());
        $configuration['port'] = 1;
        try {
            app(Database::class)->verify($configuration);
            $this->fail('No database should listen at port 1');
        } catch (InstallationException $exception) {
            $this->assertSame('database_failed', $exception->reason);
            $this->assertNull($exception->getPrevious());
            $this->assertStringNotContainsString('private-password', (string) $exception);
        }
    }

    public function test_host_and_database_identifier_are_allowlisted(): void
    {
        config(['installation.tls_required' => false]);
        $input = $this->input();
        $input['db_host'] = 'evil.test;port=1';
        $this->expectException(InstallationException::class);
        app(Database::class)->configuration($input);
    }

    public function test_production_environment_cannot_opt_out_of_verified_tls(): void
    {
        $this->app['env'] = 'production';
        config(['installation.tls_required' => false, 'installation.tls_ca' => null]);
        $this->expectException(InstallationException::class);
        app(Database::class)->configuration($this->input());
    }

    private function input(): array
    {
        return ['db_host' => '127.0.0.1', 'db_port' => 3306, 'db_name' => 'isolated_test', 'db_user' => 'fixture', 'db_password' => 'private-password'];
    }
}
