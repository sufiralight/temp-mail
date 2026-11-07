<?php

namespace App\Installation;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use PDO;

class InstallationDatabase
{
    private const string CONNECTION = 'installation';

    public function __construct(private readonly InstallationFiles $files) {}

    /** @param array<string, mixed> $database */
    public function test(array $database): void
    {
        $this->connect($database, fn (Connection $connection) => $connection->getPdo());
    }

    /**
     * @param  array<string, mixed>  $database
     * @param  array<string, string>  $admin
     */
    public function install(array $database, array $admin): void
    {
        $identity = $this->connect($database, function (Connection $connection) use ($database, $admin): string {
            $schema = $connection->getSchemaBuilder();
            $tables = $schema->getTableListing($schema->getCurrentSchemaName(), false);
            $empty = $tables === [] && $schema->getViews($schema->getCurrentSchemaName()) === [];
            $target = hash('sha256', json_encode([strtolower($database['database_host']), (int) $database['database_port'], $database['database_name']], JSON_THROW_ON_ERROR));
            $identity = $this->files->pendingIdentity($target, $empty);

            if ($empty) {
                $schema->create('installation_identity', function (Blueprint $table): void {
                    $table->unsignedTinyInteger('slot')->primary();
                    $table->uuid('id');
                    $table->unsignedBigInteger('admin_user_id')->nullable();
                });
                $tables = ['installation_identity'];
            }

            if (! in_array('installation_identity', $tables, true)) {
                throw new InstallationException('The database does not match the pending installation.');
            }

            $identities = $connection->table('installation_identity');
            if ($tables === ['installation_identity'] && ! $identities->exists()) {
                $identities->insert(['slot' => 1, 'id' => $identity]);
            }
            if (! $identities->where('id', $identity)->exists()) {
                throw new InstallationException('The database does not match the pending installation.');
            }

            $migrator = app(Migrator::class);
            $migrator->usingConnection(self::CONNECTION, function () use ($migrator): void {
                if (! $migrator->repositoryExists()) {
                    $migrator->getRepository()->createRepository();
                }
                $migrator->run([database_path('migrations')]);
            });

            $this->createAdmin($connection, $identity, $admin);

            return $identity;
        });

        $this->files->writeEnvironment($database);
        $this->files->complete($identity);
    }

    /** @param array<string, string> $admin */
    private function createAdmin(Connection $connection, string $identity, array $admin): void
    {
        $connection->transaction(function () use ($connection, $identity, $admin): void {
            $installation = $connection->table('installation_identity')->where('id', $identity);
            $adminId = $installation->lockForUpdate()->value('admin_user_id');
            $users = $connection->table('users');

            if ($adminId !== null) {
                $existing = $users->where('id', $adminId)->first();
                if (! $existing || ! $existing->is_admin || $existing->email !== $admin['email'] || ! Hash::check($admin['password'], $existing->password)) {
                    throw new InstallationException('Use the original administrator email and password to resume installation.');
                }

                return;
            }

            if ($users->exists()) {
                throw new InstallationException('The database already contains accounts. Choose the database prepared for this installation.');
            }

            $adminId = $users->insertGetId([
                'name' => $admin['name'],
                'email' => $admin['email'],
                'password' => Hash::make($admin['password']),
                'is_admin' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $installation->update(['admin_user_id' => $adminId]);
        });
    }

    /** @param array<string, mixed> $database */
    private function connect(array $database, Closure $callback): mixed
    {
        $key = 'database.connections.'.self::CONNECTION;
        config([$key => array_replace(config('database.connections.mysql'), [
            'url' => null,
            'unix_socket' => '',
            'engine' => 'InnoDB',
            'host' => $database['database_host'],
            'port' => $database['database_port'],
            'database' => $database['database_name'],
            'username' => $database['database_username'],
            'password' => $database['database_password'],
            'options' => config('database.connections.mysql.options', []) + [PDO::ATTR_TIMEOUT => 5],
        ])]);

        try {
            return $callback(DB::connection(self::CONNECTION));
        } finally {
            DB::purge(self::CONNECTION);
            config([$key => null]);
        }
    }

    public static function createMigrationTable(string $name, Closure $definition): void
    {
        $schema = Schema::getFacadeRoot();
        $connection = $schema->getConnection();
        if ($connection->getName() !== self::CONNECTION || ! $schema->hasTable($name)) {
            $schema->create($name, $definition);

            return;
        }

        // MySQL can commit table creation before executing its separate index statements.
        $blueprint = new Blueprint($connection, $name);
        $blueprint->create();
        $definition($blueprint);
        $blueprint->toSql();

        foreach ($blueprint->getCommands() as $command) {
            if (in_array($command->name, ['primary', 'unique', 'index'], true)
                && ! $schema->hasIndex($name, $command->columns, $command->name === 'index' ? null : $command->name)) {
                $schema->table($name, fn (Blueprint $table) => $table->{$command->name}($command->columns, $command->index));
            }
        }
    }
}
