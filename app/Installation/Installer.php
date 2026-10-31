<?php

namespace App\Installation;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class Installer
{
    public function __construct(private InstallationStore $store, private Database $database) {}

    public function invalidate(): void
    {
        $this->store->lock(function (): void {
            $state = $this->store->read();
            $this->mutable($state);
            unset($state['database'], $state['proof'], $state['admin'], $state['owner'], $state['capabilities']);
            $state['phase'] = 'fresh';
            $this->store->write($state);
        });
    }

    public function probe(array $input, string $owner): void
    {
        $this->store->lock(function () use ($input, $owner): void {
            $state = $this->store->read();
            $this->mutable($state);
            $configuration = $this->database->configuration($input);
            $capabilities = $this->database->verify($configuration);
            $state = array_merge($state, ['phase' => 'verified', 'database' => $configuration, 'capabilities' => $capabilities, 'owner' => hash('sha256', $owner), 'proof' => time() + 600]);
            $this->store->write($state);
        });
    }

    public function owned(string $owner, array $phases = ['verified', 'ready']): array
    {
        $state = $this->store->read();
        if (! in_array($state['phase'], $phases, true) || ! hash_equals($state['owner'] ?? '', hash('sha256', $owner)) || ($state['proof'] ?? 0) < time()) {
            throw new InstallationException('verification_required', 409);
        }

        return $state;
    }

    public function claimRecovery(string $owner): void
    {
        $state = $this->store->read();
        if (($state['schema_ready'] ?? false) && $state['phase'] === 'verified' && ! isset($state['owner'])) {
            $this->store->lock(function () use ($owner): void {
                $state = $this->store->read();
                if ($state['phase'] === 'verified' && ! isset($state['owner'])) {
                    $state['owner'] = hash('sha256', $owner);
                    $state['proof'] = time() + 600;
                    $this->store->write($state);
                }
            });
        }
    }

    public function administrator(array $input, string $owner): void
    {
        $this->store->lock(function () use ($input, $owner): void {
            $state = $this->owned($owner);
            $state['admin'] = ['name' => $input['name'], 'email' => mb_strtolower(trim($input['email'])), 'password' => Hash::driver('argon2id')->make($input['password'])];
            $state['phase'] = 'ready';
            $this->store->write($state);
        });
    }

    public function migrations(): array
    {
        return [
            'database/migrations/0001_01_01_000000_create_users_table.php',
            'database/migrations/0001_01_01_000001_create_cache_table.php',
            'database/migrations/0001_01_01_000002_create_jobs_table.php',
            'database/migrations/2026_10_31_000003_add_installation_foundation.php',
        ];
    }

    public function schemaFingerprint(): string
    {
        return hash('sha256', implode('', array_map(fn (string $file) => hash_file('sha256', base_path($file)), $this->migrations())));
    }

    public function schemaComplete(array $state): bool
    {
        if (($state['schema_fingerprint'] ?? '') !== $this->schemaFingerprint()) {
            return false;
        }
        $connection = DB::connection('installed');
        $schema = $connection->getSchemaBuilder();
        foreach (['users', 'migrations', 'installation_receipts', 'sessions', 'password_reset_tokens', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'] as $table) {
            if (! $schema->hasTable($table)) {
                return false;
            }
        }
        if (! $schema->hasColumns('users', ['id', 'name', 'email', 'password', 'role']) || ! $schema->hasColumns('installation_receipts', ['id', 'attempt', 'admin_id'])) {
            return false;
        }
        $expected = array_map(fn (string $file) => basename($file, '.php'), $this->migrations());
        $actual = $connection->table('migrations')->pluck('migration')->all();
        sort($expected);
        sort($actual);

        return $expected === $actual;
    }

    public function complete(string $owner): void
    {
        $this->store->lock(function () use ($owner): void {
            $state = $this->owned($owner, ['ready']);
            if (! app(Requirements::class)->passed()) {
                throw new InstallationException('requirements', 422);
            }
            $this->database->verify($state['database'], ! ($state['schema_ready'] ?? false));
            if (($state['schema_ready'] ?? false) && ! $this->schemaComplete($state)) {
                throw new InstallationException('recovery_required', 409);
            }
            $state['phase'] = 'finalizing';
            $state['schema_fingerprint'] = $this->schemaFingerprint();
            $this->store->write($state);
            try {
                DB::purge('installed');
                if (! ($state['schema_ready'] ?? false)) {
                    $result = Artisan::call('migrate', ['--database' => 'installed', '--path' => $this->migrations(), '--force' => true]);
                    if ($result !== 0) {
                        throw new InstallationException('recovery_required');
                    }
                }
                if (! $this->schemaComplete($state)) {
                    throw new InstallationException('recovery_required');
                }
                $state['schema_ready'] = true;
                $this->store->write($state);
                $connection = DB::connection('installed');
                $connection->transaction(function () use ($connection, $state): void {
                    if ($connection->table('users')->exists() || $connection->table('installation_receipts')->exists()) {
                        throw new InstallationException('recovery_required');
                    }
                    $adminId = $connection->table('users')->insertGetId([...$state['admin'], 'role' => 'super_admin', 'created_at' => now(), 'updated_at' => now()]);
                    $connection->table('installation_receipts')->insert(['id' => 1, 'attempt' => $state['id'], 'admin_id' => $adminId, 'created_at' => now()]);
                });
                $this->seal($state);
            } catch (Throwable) {
                // Persisted finalizing checkpoint is authoritative; never silently retry DDL.
                throw new InstallationException('recovery_required', 409);
            } finally {
                DB::purge('installed');
            }
        });
    }

    public function recover(): void
    {
        $this->store->lock(function (): void {
            $state = $this->store->read();
            if ($state['phase'] !== 'finalizing' && ! (($state['schema_ready'] ?? false) && in_array($state['phase'], ['verified', 'ready'], true))) {
                throw new InstallationException('recovery_required', 409);
            }
            try {
                $this->database->verify($state['database'], false);
                if (! $this->schemaComplete($state)) {
                    throw new InstallationException;
                }
                $connection = DB::connection('installed');
                $receipt = $connection->table('installation_receipts')->where('id', 1)->first();
                if ($receipt && $receipt->attempt === $state['id'] && $connection->table('users')->where('id', $receipt->admin_id)->where('role', 'super_admin')->exists()) {
                    $this->seal($state);

                    return;
                }
                if ($receipt || $connection->table('users')->exists()) {
                    throw new InstallationException;
                }
                $state['phase'] = 'verified';
                $state['schema_ready'] = true;
                $state['proof'] = time() + 600;
                unset($state['admin'], $state['owner']);
                $this->store->write($state);
            } catch (Throwable) {
                throw new InstallationException('recovery_required', 409);
            } finally {
                DB::purge('installed');
            }
        });
    }

    private function seal(array $state): void
    {
        $state['phase'] = 'completed';
        unset($state['admin'], $state['owner'], $state['proof']);
        $state['access_expires'] = 0;
        $this->store->write($state);
    }

    private function mutable(array $state): void
    {
        if (! in_array($state['phase'], ['fresh', 'verified', 'ready'], true) || ($state['schema_ready'] ?? false)) {
            throw new InstallationException('recovery_required', 409);
        }
    }
}
