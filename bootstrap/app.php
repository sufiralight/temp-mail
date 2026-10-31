<?php

use App\Http\Middleware\InstallationAccess;
use App\Http\Middleware\SecurityHeaders;
use App\Installation\InstallationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(SecurityHeaders::class);
        $middleware->web(prepend: [InstallationAccess::class]);
        $middleware->trimStrings(except: ['db_password', 'password', 'password_confirmation']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['password', 'password_confirmation', 'current_password', 'db_password']);
        $exceptions->dontReport([InstallationException::class]);
        $exceptions->report(function (Throwable $exception) {
            if (request()->is('/', 'install', 'install/*', 'login')) {
                return false;
            }
        });
        $exceptions->render(function (Throwable $exception, Request $request) {
            if ($request->is('/', 'install', 'install/*', 'login')) {
                if ($exception instanceof ValidationException) {
                    return null;
                }
                $reason = $exception instanceof InstallationException ? $exception->reason : 'unavailable';
                $status = $exception instanceof InstallationException ? $exception->status : ($exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 503);

                return response()->view('install.error', compact('reason'), $status);
            }
        });
        $exceptions->respond(function (Response $response) {
            return app(SecurityHeaders::class)->secure($response, (string) request()->attributes->get('csp_nonce', ''));
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
