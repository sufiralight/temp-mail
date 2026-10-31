<?php

namespace App\Installation;

use Closure;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class InstallationStore
{
    public function directory(): string
    {
        return (string) config('installation.directory');
    }

    public function read(): array
    {
        $path = $this->directory();
        if (! is_file($path.'/provisioned')) {
            throw new InstallationException('not_provisioned', 403);
        }
        try {
            $state = json_decode(Crypt::decryptString(file_get_contents($path.'/state')), true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($state) || ! isset($state['id'], $state['phase'], $state['profile'], $state['port'])) {
                throw new InstallationException;
            }
            if (! in_array($state['phase'], ['fresh', 'verified', 'ready', 'finalizing', 'completed'], true)) {
                throw new InstallationException;
            }

            return $state;
        } catch (Throwable) {
            throw new InstallationException('state_unavailable');
        }
    }

    public function completed(): bool
    {
        return $this->read()['phase'] === 'completed';
    }

    public function lock(Closure $callback): mixed
    {
        $path = $this->directory();
        if (! is_dir($path)) {
            throw new InstallationException('not_provisioned', 403);
        }
        $lock = fopen($path.'/lock', 'c');
        if ($lock === false) {
            throw new InstallationException;
        }
        chmod($path.'/lock', 0600);
        try {
            if (! flock($lock, LOCK_EX | LOCK_NB)) {
                throw new InstallationException('busy', 409);
            }

            return $callback();
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function write(array $state): void
    {
        $path = $this->directory();
        $temporary = $path.'/state.'.bin2hex(random_bytes(12));
        $handle = fopen($temporary, 'x');
        if ($handle === false) {
            throw new InstallationException;
        }
        try {
            chmod($temporary, 0600);
            $payload = Crypt::encryptString(json_encode($state, JSON_THROW_ON_ERROR));
            if (fwrite($handle, $payload) !== strlen($payload) || ! fflush($handle) || ! fsync($handle)) {
                throw new InstallationException;
            }
        } finally {
            fclose($handle);
        }
        // Windows scanners may briefly hold the destination. Never unlink the last durable state.
        for ($attempt = 0; $attempt < 10; $attempt++) {
            if (@rename($temporary, $path.'/state')) {
                return;
            }
            usleep(50000);
        }
        throw new InstallationException;
    }
}
