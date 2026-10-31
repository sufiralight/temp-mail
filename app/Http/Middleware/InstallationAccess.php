<?php

namespace App\Http\Middleware;

use App\Installation\Access;
use App\Installation\InstallationException;
use App\Installation\InstallationStore;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstallationAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $store = app(InstallationStore::class);
        if ($request->is('/')) {
            $store->read();
        }
        if ($request->is('install', 'install/*')) {
            app(Access::class)->check($request, $store->read());
        } elseif ($request->is('login')) {
            if (! $store->completed()) {
                throw new InstallationException('not_complete', 403);
            }
        }

        return $next($request);
    }
}
