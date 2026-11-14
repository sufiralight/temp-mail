<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class AuthorizeInstallation
{
    public function allows(Request $request): bool
    {
        if (app()->environment('local') && config('installation.local') === true
            && in_array(PHP_SAPI, ['cli-server', 'cli'], true)
            && in_array($request->server('REMOTE_ADDR'), ['127.0.0.1', '::1'], true)
            && in_array($request->getHost(), ['localhost', '127.0.0.1'], true)
            && ! $request->headers->has('Forwarded')
            && ! $request->headers->has('X-Forwarded-For')
            && ! $request->headers->has('X-Forwarded-Host')
            && ! $request->headers->has('X-Forwarded-Proto')) {
            return true;
        }

        $operator = config('installation.operator');
        $identity = $request->server('REMOTE_USER');

        return config('app.debug') === false
            && is_string($operator) && $operator !== ''
            && is_string($identity) && hash_equals($operator, $identity)
            && in_array(strtolower((string) $request->server('HTTPS')), ['on', '1'], true)
            && $request->getHost() === parse_url((string) config('app.url'), PHP_URL_HOST);
    }
}
