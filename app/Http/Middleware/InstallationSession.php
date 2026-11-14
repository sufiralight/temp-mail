<?php

namespace App\Http\Middleware;

use App\Installation\State;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstallationSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('install', 'install/*', '/')) {
            return $next($request);
        }

        $status = app(State::class)->status();
        if ($request->is('/')) {
            abort_if($status === 'recovery' || ($status === 'complete' && ! is_file(app()->environmentFilePath())), 503);
            if ($status === 'fresh' && app(AuthorizeInstallation::class)->allows($request)) {
                return redirect('/install');
            }

            return $next($request);
        }

        abort_unless(app(AuthorizeInstallation::class)->allows($request), 404);
        abort_if(! $request->isMethodSafe() && $status !== 'fresh', 404);
        abort_if($status === 'recovery' || blank(config('app.key')) || ! is_file(app()->environmentFilePath()), 503);

        config([
            'session.driver' => app()->environment('testing') ? 'array' : 'file',
            'session.cookie' => 'temp_mail_install',
            'session.domain' => null,
            'session.path' => '/install',
            'session.encrypt' => true,
            'session.secure' => in_array(strtolower((string) $request->server('HTTPS')), ['on', '1'], true),
            'session.same_site' => 'strict',
            'session.http_only' => true,
            'cache.limiter' => 'file',
        ]);

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store');
        $response->headers->set('Referrer-Policy', 'no-referrer');

        return $response;
    }
}
