<?php

namespace App\Installation;

use Illuminate\Foundation\Application;

class InstallationStatus
{
    public function __construct(private readonly Application $app) {}

    public function current(): InstallationState
    {
        $privateDirectory = $this->app->storagePath('app/private');
        $marker = $privateDirectory.'/installation.state';
        $environment = $this->app->environmentPath().'/.env';

        if (! is_dir($privateDirectory) || ! is_readable($privateDirectory)) {
            return InstallationState::Uncertain;
        }

        if (! file_exists($marker) && ! is_link($marker)) {
            if (! is_file($environment) && $this->app->configurationIsCached()) {
                return InstallationState::Uncertain;
            }

            return InstallationState::Fresh;
        }

        if (is_link($marker) || ! is_file($marker) || trim((string) @file_get_contents($marker, length: 32)) !== 'complete') {
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
