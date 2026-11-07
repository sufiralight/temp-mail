<?php

namespace App\Installation;

use Illuminate\Foundation\Application;

class InstallationStatus
{
    public function __construct(private readonly Application $app, private readonly InstallationFiles $files) {}

    public function current(): InstallationState
    {
        $privateDirectory = $this->app->storagePath('app/private');
        $marker = $this->files->statePath();
        $environment = $this->files->environmentPath();

        if (! is_dir($privateDirectory) || ! is_readable($privateDirectory)) {
            return InstallationState::Uncertain;
        }

        if (! file_exists($marker) && ! is_link($marker)) {
            if (! is_file($environment) && $this->app->configurationIsCached()) {
                return InstallationState::Uncertain;
            }

            return InstallationState::Fresh;
        }

        $record = ! is_link($marker) && is_file($marker) ? trim((string) @file_get_contents($marker, length: 128)) : '';

        if (preg_match('/\Apending [0-9a-f-]{36} [0-9a-f]{64}\z/', $record) === 1) {
            return InstallationState::Fresh;
        }

        if ($record !== 'complete' && preg_match('/\Acomplete [0-9a-f-]{36}\z/', $record) !== 1) {
            return InstallationState::Uncertain;
        }

        if (! file_exists($environment) && ! is_link($environment)) {
            return InstallationState::Recovery;
        }

        return is_file($environment) && is_readable($environment)
            ? InstallationState::Installed
            : InstallationState::Uncertain;
    }
}
