<?php

namespace App\Installation;

use Illuminate\Encryption\Encrypter;

class Requirements
{
    public function checks(): array
    {
        $checks = [__('install.checks.php') => version_compare(PHP_VERSION, '8.3', '>=') && version_compare(PHP_VERSION, '8.6', '<')];
        foreach (['ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring', 'openssl', 'pcre', 'pdo_mysql', 'session', 'tokenizer', 'xml'] as $extension) {
            $checks[__('install.checks.extension', ['extension' => $extension])] = extension_loaded($extension);
        }
        $checks[__('install.checks.hashing')] = defined('PASSWORD_ARGON2ID');
        $key = (string) config('app.key');
        $decoded = str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7), true) : $key;
        $checks[__('install.checks.key')] = is_string($decoded) && Encrypter::supported($decoded, config('app.cipher'));
        foreach ([config('session.files'), config('cache.stores.file.path'), config('view.compiled'), base_path('bootstrap/cache'), app(InstallationStore::class)->directory()] as $index => $path) {
            $checks[__('install.checks.'.['sessions', 'cache', 'views', 'bootstrap', 'private'][$index])] = is_string($path) && is_dir($path) && is_writable($path);
        }
        $checks[__('install.checks.outside_public')] = ! str_starts_with(strtolower(str_replace('\\', '/', (string) realpath(app(InstallationStore::class)->directory()))).'/', strtolower(str_replace('\\', '/', (string) realpath(public_path()))).'/');
        $checks[__('install.checks.debug')] = ! app()->environment('production') || ! config('app.debug');
        $checks[__('install.checks.tls')] = (! config('installation.tls_required') && ! app()->environment('production')) || (is_string(config('installation.tls_ca')) && is_readable(config('installation.tls_ca')));

        return $checks;
    }

    public function passed(): bool
    {
        return ! in_array(false, $this->checks(), true);
    }
}
