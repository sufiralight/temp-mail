<?php

namespace App\Installation;

use RuntimeException;

class State
{
    public function status(): string
    {
        $path = $this->path('state');
        if (is_link($path)) {
            return 'recovery';
        }
        if (! file_exists($path)) {
            return is_dir(dirname($path)) && is_readable(dirname($path)) ? 'fresh' : 'recovery';
        }

        return match (@file_get_contents($path)) {
            'complete-v1' => 'complete',
            default => 'recovery',
        };
    }

    public function path(string $file): string
    {
        return config('installation.path').'/'.$file;
    }

    public function locked(callable $action): mixed
    {
        $lock = @fopen($this->path('lock'), 'c+b');
        if ($lock === false) {
            throw new RuntimeException('Installation lock unavailable.');
        }

        try {
            if (! flock($lock, LOCK_EX | LOCK_NB)) {
                throw new RuntimeException('Installation lock held.');
            }

            return $action();
        } finally {
            fclose($lock);
        }
    }

    public function write(string $path, string $content): void
    {
        $temporary = @tempnam(dirname($path), '.install-');
        if ($temporary === false) {
            throw new RuntimeException('Private write unavailable.');
        }

        try {
            if (realpath(dirname($temporary)) !== realpath(dirname($path))
                || (PHP_OS_FAMILY !== 'Windows' && ! @chmod($temporary, 0600))) {
                throw new RuntimeException('Private permissions unavailable.');
            }

            $file = @fopen($temporary, 'wb');
            if ($file === false) {
                throw new RuntimeException('Private write unavailable.');
            }

            try {
                if (@fwrite($file, $content) !== strlen($content) || ! @fsync($file)) {
                    throw new RuntimeException('Private write incomplete.');
                }
            } finally {
                fclose($file);
            }

            if (! @rename($temporary, $path)) {
                throw new RuntimeException('Atomic replacement unavailable.');
            }
        } finally {
            if (is_file($temporary)) {
                @unlink($temporary);
            }
        }
    }
}
