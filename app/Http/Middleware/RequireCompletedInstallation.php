<?php

namespace App\Http\Middleware;

use App\Installation\InstallationState;
use App\Installation\InstallationStatus;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireCompletedInstallation
{
    public function __construct(private readonly InstallationStatus $installation) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('up') || ($request->is('install') && $request->isMethodCacheable())) {
            return $next($request);
        }

        $state = $this->installation->current();
        if ($request->is('install', 'install/*')) {
            $setupRoute = ($request->isMethodCacheable() && $request->is('install/admin'))
                || ($request->isMethod('POST') && $request->is('install/database', 'install/admin'));

            return $state === InstallationState::Fresh && $setupRoute ? $next($request) : new Response(status: 404);
        }

        if ($state === InstallationState::Installed) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return new JsonResponse(['message' => 'Installation is required.'], 503);
        }

        if (! $request->isMethodCacheable()) {
            return new Response(status: 503);
        }

        return new RedirectResponse(route('install'), 302, ['Cache-Control' => 'no-store']);
    }
}
