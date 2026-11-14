<?php

namespace App\Installation;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PDO;
use RuntimeException;

class DatabaseSetup
{
    public function connection(array $input, string $name): Connection
    {
        $config = config('database.connections.'.$input['engine']);

        return DB::build(array_replace($config, [
            'name' => $name,
            'url' => null,
            'host' => $input['host'],
            'port' => $input['port'],
            'database' => $input['database'],
            'username' => $input['username'],
            'password' => $input['database_password'] ?? '',
            'unix_socket' => $input['host'] === 'localhost' ? $config['unix_socket'] : '',
            'options' => [PDO::ATTR_TIMEOUT => 3] + $config['options'],
        ]));
    }

    public function probe(array $input): void
    {
        try {
            $this->connection($input, 'installer_probe')->selectOne('SELECT 1');
        } finally {
            DB::purge('installer_probe');
        }
    }

    public function provision(array $input, array $admin, callable $beforeMigration, callable $complete): void
    {
        $connection = $this->connection($input, 'installer_target');
        try {
            $lock = hash('sha256', 'temp-mail-install:'.$connection->getDatabaseName());
            if ((int) $connection->selectOne('SELECT GET_LOCK(?, 0) AS acquired', [$lock])->acquired !== 1
                || $connection->getSchemaBuilder()->getTableListing($connection->getDatabaseName()) !== []
                || $connection->getSchemaBuilder()->getViews($connection->getDatabaseName()) !== []) {
                throw new RuntimeException('A dedicated empty database is required.');
            }

            $beforeMigration();

            if (Artisan::call('migrate', ['--database' => 'installer_target', '--force' => true, '--no-interaction' => true]) !== 0
                || array_diff(['migrations', 'users', 'sessions', 'password_reset_tokens', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'], $connection->getSchemaBuilder()->getTableListing($connection->getDatabaseName(), schemaQualified: false)) !== []) {
                throw new RuntimeException('Schema setup did not complete.');
            }

            if (! $connection->getSchemaBuilder()->hasColumn('users', 'is_super_admin')) {
                throw new RuntimeException('Administrator schema unavailable.');
            }

            $connection->transaction(function () use ($connection, $admin): void {
                if ($connection->table('users')->exists()) {
                    throw new RuntimeException('User records already exist.');
                }

                $connection->table('users')->insert([
                    'name' => $admin['name'],
                    'email' => $admin['email'],
                    'password' => Hash::make($admin['password']),
                    'is_super_admin' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            $complete();
        } finally {
            DB::purge('installer_target');
        }
    }
}
