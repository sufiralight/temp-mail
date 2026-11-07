<?php

namespace App\Installation;

use Dotenv\Dotenv;
use Illuminate\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Str;

class InstallationFiles
{
    public function __construct(private readonly Application $app, private readonly Filesystem $files) {}

    public function statePath(): string
    {
        return $this->privatePath('installation.state');
    }

    public function environmentPath(): string
    {
        return $this->app->environmentPath().'/.env';
    }

    public function privatePath(string $file): string
    {
        return $this->app->storagePath('app/private/'.$file);
    }

    public function operatorKey(): ?string
    {
        $path = $this->privatePath('installation.key');
        $key = ! is_link($path) && is_file($path) ? trim((string) @file_get_contents($path, length: 128)) : '';

        return preg_match('/\A[0-9a-f]{64}\z/i', $key) === 1 ? $key : null;
    }

    public function pendingIdentity(string $target, bool $empty): string
    {
        $path = $this->statePath();

        if (is_file($path)) {
            $state = trim($this->files->get($path));
            if (preg_match('/\Apending ([0-9a-f-]{36}) ([0-9a-f]{64})\z/', $state, $matches) !== 1 || $matches[2] !== $target) {
                throw new InstallationException('Continue with the original database to resume this installation.');
            }

            return $matches[1];
        }

        if (! $empty) {
            throw new InstallationException('Choose an empty database for the first installation.');
        }

        $identity = (string) Str::uuid();
        $this->files->replace($path, "pending {$identity} {$target}\n", 0600);

        return $identity;
    }

    /** @param array<string, mixed> $database */
    public function writeEnvironment(array $database): void
    {
        $path = $this->environmentPath();
        if (is_link($path) || (file_exists($path) && ! is_file($path))) {
            throw new InstallationException('Check that the .env path is a writable file.');
        }

        $existing = is_file($path) ? $this->files->get($path) : '';
        $parsed = Dotenv::parse($existing);
        $cipher = config('app.cipher');
        $key = $parsed['APP_KEY'] ?? '';
        $rawKey = str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7), true) : $key;
        if (! is_string($rawKey) || ! Encrypter::supported($rawKey, $cipher)) {
            $key = 'base64:'.base64_encode(Encrypter::generateKey($cipher));
        }

        $values = [
            'APP_ENV' => $parsed['APP_ENV'] ?? 'production',
            'APP_DEBUG' => 'false',
            'APP_KEY' => $key,
            'DB_CONNECTION' => 'mysql',
            'DB_URL' => '',
            'DB_SOCKET' => '',
            'DB_HOST' => $database['database_host'],
            'DB_PORT' => (string) $database['database_port'],
            'DB_DATABASE' => $database['database_name'],
            'DB_USERNAME' => $database['database_username'],
            'DB_PASSWORD' => $database['database_password'],
        ];

        $start = '# Temp Mail installation';
        $end = '# End Temp Mail installation';
        $existing = preg_replace('/^'.preg_quote($start, '/').'\R.*?^'.preg_quote($end, '/').'(?:\R|$)/ms', '', $existing);
        $lines = [$start];
        foreach ($values as $name => $value) {
            $escaped = strtr($value, ['\\' => '\\\\', '"' => '\\"', '$' => '\\$', "\n" => '\\n', "\r" => '\\r', "\t" => '\\t']);
            $lines[] = $name.'="'.$escaped.'"';
        }
        $lines[] = $end;
        $contents = ltrim(rtrim($existing)."\n\n".implode("\n", $lines)."\n", "\n");
        $verified = Dotenv::parse($contents);
        foreach ($values as $name => $value) {
            if (($verified[$name] ?? null) !== $value) {
                throw new InstallationException('Check the existing .env file before retrying.');
            }
        }

        $this->files->replace($path, $contents, 0600);

        $cachePath = $this->app->getCachedConfigPath();
        if (is_file($cachePath) && ! $this->files->delete($cachePath)) {
            throw new InstallationException('Clear the cached configuration before retrying.');
        }
    }

    public function complete(string $identity): void
    {
        $this->files->replace($this->statePath(), "complete {$identity}\n", 0600);
    }
}
