<?php

namespace App\Installation;

use Dotenv\Dotenv;
use RuntimeException;

class EnvironmentFile
{
    public function prepare(array $input): string
    {
        $path = app()->environmentFilePath();
        if (! is_file($path) || is_link($path) || ! is_writable($path)
            || ! is_writable(dirname($path)) || app()->configurationIsCached()) {
            throw new RuntimeException('Environment is not ready for installation.');
        }

        $original = file_get_contents($path);
        $existing = Dotenv::parse($original);
        $key = $existing['APP_KEY'] ?? '';
        if ($key === '' || ! hash_equals($key, (string) config('app.key'))) {
            throw new RuntimeException('Application key must be provisioned first.');
        }

        $values = [
            'DB_CONNECTION' => $input['engine'],
            'DB_HOST' => $input['host'],
            'DB_PORT' => (string) $input['port'],
            'DB_DATABASE' => $input['database'],
            'DB_USERNAME' => $input['username'],
            'DB_PASSWORD' => $input['database_password'] ?? '',
            'DB_URL' => '',
            'DB_SOCKET' => $input['host'] === 'localhost' ? ($existing['DB_SOCKET'] ?? '') : '',
        ];

        $updated = $original;
        foreach ($values as $name => $value) {
            $updated = preg_replace('/^[ \t]*(?:export[ \t]+)?'.$name.'[ \t]*=.*(?:\r?\n|$)/m', '', $updated);
            $encoded = strtr($value, ['\\' => '\\\\', '"' => '\\"', '$' => '\\$']);
            $updated = rtrim($updated, "\r\n")."\n".$name.'="'.$encoded.'"'."\n";
        }

        $expected = array_replace($existing, $values);
        $parsed = Dotenv::parse($updated);
        ksort($expected);
        ksort($parsed);
        if ($parsed !== $expected) {
            throw new RuntimeException('Environment contains conflicting entries.');
        }

        return $updated;
    }
}
