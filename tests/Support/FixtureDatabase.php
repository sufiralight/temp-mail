<?php

namespace Tests\Support;

use App\Installation\Database;
use App\Installation\InstallationException;
use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory;

// Explicit workflow fixture: SQLite does not qualify MySQL version/TLS/DDL behavior.
class FixtureDatabase extends Database
{
    public bool $fail = false;

    public function __construct(private string $path) {}

    public function configuration(array $input): array
    {
        return ['driver' => 'sqlite', 'database' => $this->path, 'prefix' => '', 'foreign_key_constraints' => true, 'password' => $input['db_password']];
    }

    public function verify(array $configuration, bool $empty = true): array
    {
        if ($this->fail || ($empty && $this->connect($configuration)->getSchemaBuilder()->hasTable('users'))) {
            throw new InstallationException('database_failed', 422);
        }

        return ['version' => '8.4.fixture', 'tls' => false];
    }

    public function connect(array $configuration): Connection
    {
        return app(ConnectionFactory::class)->make(['driver' => 'sqlite', 'database' => $this->path, 'prefix' => '', 'foreign_key_constraints' => true], 'installed');
    }
}
