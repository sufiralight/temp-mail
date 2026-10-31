<?php

namespace App\Installation;

use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory;
use PDO;
use Pdo\Mysql;
use Throwable;

class Database
{
    public function configuration(array $input): array
    {
        if (! in_array($input['db_host'] ?? '', config('installation.database_hosts'), true)
            || (int) ($input['db_port'] ?? 0) !== config('installation.database_port')
            || ! preg_match('/\A[a-zA-Z0-9_]{1,64}\z/', $input['db_name'] ?? '')) {
            throw new InstallationException('database_failed', 422);
        }
        $tls = config('installation.tls_required') || app()->environment('production');
        $options = [PDO::ATTR_TIMEOUT => 5, PDO::ATTR_EMULATE_PREPARES => false];
        if ($tls) {
            $ca = config('installation.tls_ca');
            if (! is_string($ca) || ! is_readable($ca)) {
                throw new InstallationException('database_tls', 422);
            }
            $options[Mysql::ATTR_SSL_CA] = $ca;
            $options[Mysql::ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }

        return [
            'driver' => 'mysql', 'host' => $input['db_host'], 'port' => (int) $input['db_port'],
            'database' => $input['db_name'], 'username' => $input['db_user'], 'password' => $input['db_password'],
            'charset' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci', 'prefix' => '',
            'strict' => true, 'engine' => 'InnoDB', 'options' => $options,
        ];
    }

    public function connect(array $configuration): Connection
    {
        try {
            $required = config('installation.tls_required') || app()->environment('production');
            if ($required && (empty($configuration['options'][Mysql::ATTR_SSL_CA]) || ($configuration['options'][Mysql::ATTR_SSL_VERIFY_SERVER_CERT] ?? null) !== true)) {
                throw new InstallationException('database_tls', 422);
            }
            $connection = app(ConnectionFactory::class)->make($configuration, 'installed');
            $connection->getPdo();
            if ($required && empty($connection->selectOne("SHOW SESSION STATUS LIKE 'Ssl_cipher'")->Value)) {
                $connection->disconnect();
                throw new InstallationException('database_tls', 422);
            }

            return $connection;
        } catch (InstallationException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new InstallationException('database_failed', 422);
        }
    }

    public function verify(array $configuration, bool $empty = true): array
    {
        $connection = null;
        try {
            $connection = $this->connect($configuration);
            $version = (string) $connection->selectOne('SELECT VERSION() AS version')->version;
            if (! preg_match('/\A8\.4\.\d+(?:[-.].*)?\z/', $version) || stripos($version, 'mariadb') !== false) {
                throw new InstallationException('database_version', 422);
            }
            $collation = $connection->selectOne('SELECT @@character_set_connection AS charset, @@collation_connection AS collation');
            if ($collation->charset !== 'utf8mb4' || $collation->collation !== 'utf8mb4_0900_ai_ci') {
                throw new InstallationException('database_failed', 422);
            }
            $ssl = $connection->selectOne("SHOW SESSION STATUS LIKE 'Ssl_cipher'");
            $encrypted = ! empty($ssl->Value);
            if ((config('installation.tls_required') || app()->environment('production')) && ! $encrypted) {
                throw new InstallationException('database_tls', 422);
            }
            if ($empty) {
                $objects = $connection->selectOne('SELECT (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()) + (SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema = DATABASE()) + (SELECT COUNT(*) FROM information_schema.events WHERE event_schema = DATABASE()) AS total');
                if ((int) $objects->total !== 0) {
                    throw new InstallationException('database_not_empty', 422);
                }
            }

            return ['version' => $version, 'tls' => $encrypted];
        } catch (InstallationException $exception) {
            throw $exception;
        } catch (Throwable) {
            // Do not retain the connector exception or DSN in a previous-exception chain.
            throw new InstallationException('database_failed', 422);
        } finally {
            $connection?->disconnect();
        }
    }
}
