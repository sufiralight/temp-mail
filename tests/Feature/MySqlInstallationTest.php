<?php

namespace Tests\Feature;

use App\Installation\Database;
use App\Installation\InstallationException;
use App\Installation\InstallationStore;
use App\Installation\Installer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use Pdo\Mysql;
use Tests\TestCase;

class MySqlInstallationTest extends TestCase
{
    private ?PDO $operator = null;

    private ?string $databaseName = null;

    private ?string $directory = null;

    protected function setUp(): void
    {
        parent::setUp();
        if (! getenv('MYSQL_INSTALLATION_TEST_PORT') || ! getenv('MYSQL_INSTALLATION_TEST_CA')) {
            $this->markTestSkipped('Set explicit disposable MySQL 8.4 loopback port and CA; no real configured database is used.');
        }
        $port = (int) getenv('MYSQL_INSTALLATION_TEST_PORT');
        $ca = getenv('MYSQL_INSTALLATION_TEST_CA');
        $this->operator = new PDO('mysql:host=127.0.0.1;port='.$port, 'root', '', [Mysql::ATTR_SSL_CA => $ca, Mysql::ATTR_SSL_VERIFY_SERVER_CERT => true]);
        $this->databaseName = 'temp_mail_test_'.bin2hex(random_bytes(8));
        $this->operator->exec('CREATE DATABASE `'.$this->databaseName.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci');
        $this->directory = storage_path('framework/testing/'.$this->databaseName);
        mkdir($this->directory, 0700, true);
        config(['app.key' => 'base64:'.base64_encode(str_repeat('m', 32)), 'app.debug' => false, 'installation.directory' => $this->directory, 'installation.database_port' => $port, 'installation.tls_required' => true, 'installation.tls_ca' => $ca]);
        $this->app->forgetInstance('encrypter');
        file_put_contents($this->directory.'/provisioned', 'disposable');
        app(InstallationStore::class)->write(['id' => 'cb592916-2e70-4f25-918b-a1c53e3f051f', 'phase' => 'fresh', 'profile' => 'local', 'port' => 8000, 'access_expires' => time() + 600]);
    }

    protected function tearDown(): void
    {
        DB::purge('installed');
        // Only the random database created by this test is eligible for deletion.
        if ($this->operator && $this->databaseName && preg_match('/\Atemp_mail_test_[a-f0-9]{16}\z/', $this->databaseName)) {
            $this->operator->exec('DROP DATABASE `'.$this->databaseName.'`');
        }
        if ($this->directory) {
            File::deleteDirectory($this->directory);
        }
        $this->operator = null;
        parent::tearDown();
    }

    public function test_real_mysql_tls_probe_migrations_receipt_and_completion(): void
    {
        $key = config('app.key');
        $database = app(Database::class);
        $configuration = $database->configuration($this->input());
        $capabilities = $database->verify($configuration);
        $this->assertStringStartsWith('8.4.', $capabilities['version']);
        $this->assertTrue($capabilities['tls']);
        $installer = app(Installer::class);
        $installer->probe($this->input(), 'mysql-owner');
        $installer->administrator(['name' => 'MySQL Owner', 'email' => 'mysql@example.test', 'password' => 'A real isolated password ✓'], 'mysql-owner');
        $installer->complete('mysql-owner');
        $this->assertTrue(app(InstallationStore::class)->completed());
        $this->assertSame(1, DB::connection('installed')->table('users')->where('role', 'super_admin')->count());
        $this->assertSame(1, DB::connection('installed')->table('installation_receipts')->count());
        $this->assertSame($key, config('app.key'));
        $this->assertTrue($installer->schemaComplete(app(InstallationStore::class)->read()));
    }

    public function test_real_populated_mysql_is_not_modified_by_probe(): void
    {
        $this->operator->exec('CREATE TABLE `'.$this->databaseName.'`.valuable_data (id INT)');
        try {
            app(Database::class)->verify(app(Database::class)->configuration($this->input()));
            $this->fail('Existing database must be refused');
        } catch (InstallationException $exception) {
            $this->assertSame('database_not_empty', $exception->reason);
        }
        $this->assertSame(0, (int) $this->operator->query('SELECT COUNT(*) FROM `'.$this->databaseName.'`.valuable_data')->fetchColumn());
    }

    public function test_real_untrusted_ca_is_rejected_without_disabling_verification(): void
    {
        config(['installation.tls_ca' => __FILE__]);
        $this->expectException(InstallationException::class);
        app(Database::class)->verify(app(Database::class)->configuration($this->input()));
    }

    public function test_real_mysql_two_workers_cannot_create_duplicate_initial_administrators(): void
    {
        $installer = app(Installer::class);
        $installer->probe($this->input(), 'process-owner');
        $installer->administrator(['name' => 'Concurrent Owner', 'email' => 'concurrent@example.test', 'password' => 'A real isolated password ✓'], 'process-owner');
        $env = array_merge(getenv(), ['APP_ENV' => 'local', 'APP_DEBUG' => 'false', 'APP_KEY' => config('app.key'), 'INSTALLATION_DIRECTORY' => $this->directory, 'INSTALLATION_TLS_REQUIRED' => 'true', 'INSTALLATION_TLS_CA' => config('installation.tls_ca')]);
        $processes = [];
        foreach ([1, 2] as $worker) {
            $process = proc_open([PHP_BINARY, base_path('tests/Support/compete-installation.php')], [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, base_path(), $env);
            $this->assertSame("ready\n", fgets($pipes[1]));
            $processes[] = [$process, $pipes];
        }
        foreach ($processes as [, $pipes]) {
            fwrite($pipes[0], "go\n");
        }
        $outcomes = [];
        foreach ($processes as [$process, $pipes]) {
            fclose($pipes[0]);
            $outcomes[] = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $this->assertSame(0, proc_close($process), $error);
        }
        $this->assertContains('completed', $outcomes);
        $this->assertCount(1, array_filter($outcomes, fn ($outcome) => $outcome === 'completed'));
        $this->assertSame(1, DB::connection('installed')->table('users')->count());
        $this->assertSame(1, DB::connection('installed')->table('installation_receipts')->count());
        $this->assertTrue(app(InstallationStore::class)->completed());
    }

    public function test_real_mysql_complete_schema_recovers_before_account_commit(): void
    {
        $installer = app(Installer::class);
        $installer->probe($this->input(), 'old-session');
        $installer->administrator(['name' => 'Interrupted Owner', 'email' => 'interrupted@example.test', 'password' => 'A real isolated password ✓'], 'old-session');
        $state = app(InstallationStore::class)->read();
        $state['phase'] = 'finalizing';
        $state['schema_fingerprint'] = $installer->schemaFingerprint();
        app(InstallationStore::class)->write($state);
        Artisan::call('migrate', ['--database' => 'installed', '--path' => $installer->migrations(), '--force' => true]);
        $installer->recover();
        $this->assertSame('verified', app(InstallationStore::class)->read()['phase']);
        $installer->claimRecovery('new-session');
        $expired = app(InstallationStore::class)->read();
        $expired['proof'] = time() - 1;
        app(InstallationStore::class)->write($expired);
        $installer->recover();
        $installer->claimRecovery('new-session');
        $installer->administrator(['name' => 'Recovered Owner', 'email' => 'recovered@example.test', 'password' => 'A real isolated password ✓'], 'new-session');
        $installer->complete('new-session');
        $this->assertSame(1, DB::connection('installed')->table('users')->count());
        $this->assertSame(4, DB::connection('installed')->table('migrations')->count());
        $this->assertTrue(app(InstallationStore::class)->completed());
    }

    private function input(): array
    {
        return ['db_host' => '127.0.0.1', 'db_port' => (int) getenv('MYSQL_INSTALLATION_TEST_PORT'), 'db_name' => $this->databaseName, 'db_user' => 'root', 'db_password' => ''];
    }
}
