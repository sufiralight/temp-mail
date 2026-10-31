<?php

namespace App\Installation;

use Illuminate\Http\Request;

class Access
{
    public function check(Request $request, array $state): void
    {
        if ($state['phase'] === 'completed') {
            throw new InstallationException('locked', 410);
        }
        if (! config('installation.enabled') || ($state['access_expires'] ?? 0) < time()) {
            throw new InstallationException('access_denied', 403);
        }
        if (! in_array($state['profile'], ['local', 'ssh-tunnel'], true)
            || ($state['profile'] === 'local' && ! app()->environment(['local', 'testing']))
            || ($state['profile'] === 'ssh-tunnel' && config('app.debug'))) {
            throw new InstallationException('access_denied', 403);
        }
        // The operator must also verify the listener is loopback-only and not publicly forwarded.
        if (! in_array($request->server('REMOTE_ADDR'), ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true)
            || ! in_array($request->getHost(), ['localhost', '127.0.0.1'], true)
            || $request->getPort() !== $state['port']) {
            throw new InstallationException('access_denied', 403);
        }
        foreach (array_keys($request->headers->all()) as $name) {
            if ($name === 'forwarded' || str_starts_with($name, 'x-forwarded-') || $name === 'x-real-ip') {
                throw new InstallationException('access_denied', 403);
            }
        }
        if ($state['phase'] === 'finalizing') {
            throw new InstallationException('recovery_required', 409);
        }
        $origin = $request->header('Origin');
        // no-referrer can serialize a native form's Origin as "null". It grants no trust;
        // Laravel must still validate the CSRF token for such requests.
        if (! $request->isMethodSafe() && $origin !== null && $origin !== 'null' && $origin !== $request->getSchemeAndHttpHost()) {
            throw new InstallationException('access_denied', 403);
        }
    }
}
