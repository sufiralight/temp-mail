<?php

namespace App\Console\Commands;

use App\Installation\InstallationException;
use App\Installation\InstallationStore;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

class PrepareInstallation extends Command
{
    protected $signature = 'install:prepare {--profile=local : local or ssh-tunnel} {--port=8000} {--restricted : Confirm loopback-only listener and no public forwarding} {--renew : Renew only the original non-completed access profile}';

    protected $description = 'Provision a fresh private installer after verifying infrastructure restrictions (no setup secret)';

    public function handle(InstallationStore $store): int
    {
        $profile = $this->option('profile');
        $port = filter_var($this->option('port'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
        if (! $this->option('restricted') || ! config('installation.enabled') || ! in_array($profile, ['local', 'ssh-tunnel'], true) || ! $port
            || ($profile === 'local' && ! app()->environment(['local', 'testing'])) || ($profile === 'ssh-tunnel' && config('app.debug'))) {
            $this->error('Enable installation explicitly and verify a loopback-only, non-public listener. Production requires a confirmed restricted tunnel and debug disabled.');

            return self::FAILURE;
        }
        try {
            // Fail before provisioning if APP_KEY is absent/invalid; never generate or replace it.
            app('encrypter');
            $directory = $store->directory();
            if (! is_dir($directory) && ! mkdir($directory, 0700, true)) {
                throw new InstallationException;
            }
            $real = str_replace('\\', '/', (string) realpath($directory));
            if (str_starts_with(strtolower($real).'/', strtolower(str_replace('\\', '/', public_path())).'/')) {
                throw new InstallationException;
            }
            $store->lock(function () use ($directory, $profile, $port, $store): void {
                if (is_file($directory.'/state') || is_file($directory.'/provisioned')) {
                    if ($this->option('renew')) {
                        $state = $store->read();
                        if ($state['phase'] !== 'completed' && $state['profile'] === $profile && $state['port'] === $port) {
                            $state['access_expires'] = time() + 7200;
                            $store->write($state);

                            return;
                        }
                    }
                    throw new InstallationException;
                }
                $marker = fopen($directory.'/provisioned', 'x');
                if (! $marker) {
                    throw new InstallationException;
                }
                chmod($directory.'/provisioned', 0600);
                fwrite($marker, 'Provisioned: do not delete. Restore matching state if unavailable.');
                fflush($marker);
                fsync($marker);
                fclose($marker);
                $store->write(['id' => (string) Str::uuid(), 'phase' => 'fresh', 'profile' => $profile, 'port' => $port, 'access_expires' => time() + 7200]);
            });
            $this->info('Private installation provisioned for two hours. Open /install on the verified loopback listener. No setup password is needed.');

            return self::SUCCESS;
        } catch (Throwable) {
            $this->error('Provisioning refused. Check the existing key, private permissions and state. Never delete existing installation state.');

            return self::FAILURE;
        }
    }
}
