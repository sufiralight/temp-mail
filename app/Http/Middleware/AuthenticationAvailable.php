<?php

namespace App\Http\Middleware;

use App\Installation\State;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationAvailable
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app(State::class)->status() === 'complete'
            && is_file(app()->environmentFilePath()) && filled(config('app.key')), 404);

        $url = parse_url((string) config('app.url'));
        if (! app()->environment('local', 'testing')) {
            abort_unless(($url['scheme'] ?? null) === 'https' && $request->isSecure()
                && $request->getHost() === ($url['host'] ?? null)
                && $request->getPort() === ($url['port'] ?? 443), 404);
        }
        if (($url['scheme'] ?? null) === 'https' || $request->isSecure()) {
            config(['session.secure' => true]);
        }

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store');
        $response->headers->set('Referrer-Policy', 'no-referrer');

        return $response;
    }
}
