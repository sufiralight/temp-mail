<?php

namespace App\Http\Middleware;

use App\Installation\InstallationFiles;
use App\Installation\InstallationState;
use App\Installation\InstallationStatus;
use Closure;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\Repository;
use Illuminate\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\Response as BladeResponse;
use Illuminate\Session\EncryptedStore;
use Illuminate\Session\FileSessionHandler;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class StartInstallationSession
{
    private const string COOKIE = 'temp_mail_install';

    private const int LIFETIME = 30;

    public function __construct(
        private readonly InstallationStatus $status,
        private readonly InstallationFiles $installationFiles,
        private readonly Filesystem $files,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $entry = $request->routeIs('install');
        if ($this->status->current() !== InstallationState::Fresh) {
            return $entry ? $next($request) : new Response(status: 404);
        }

        $key = $this->installationFiles->operatorKey();
        if ($key === null) {
            return $entry ? $next($request) : new Response(status: 404);
        }

        $loopback = in_array($request->server('REMOTE_ADDR'), ['127.0.0.1', '::1'], true)
            && in_array($request->getHost(), ['localhost', '127.0.0.1', '[::1]'], true);
        if (! $request->isSecure() && ! $loopback) {
            return $this->message('Open this page over HTTPS before continuing setup.', 403);
        }

        $path = $this->installationFiles->privatePath('installation.lock');
        $lock = ! is_link($path) ? @fopen($path, 'c') : false;
        if ($lock === false) {
            return $this->message('Check the write permissions for the private storage directory.', 503);
        }

        try {
            if (! flock($lock, LOCK_EX | LOCK_NB)) {
                return $this->message('Another setup request is in progress. Try again shortly.', 409);
            }

            clearstatcache();
            if ($this->status->current() !== InstallationState::Fresh) {
                return $entry ? $next($request) : new Response(status: 404);
            }

            return $this->runSession($request, $next, $key);
        } catch (Throwable) {
            return $this->message('Check write access to private storage and try again.', 503);
        } finally {
            fclose($lock);
        }
    }

    /** @param Closure(Request): Response $next */
    private function runSession(Request $request, Closure $next, string $key): Response
    {
        $directory = $this->installationFiles->privatePath('installer-sessions');
        $this->files->ensureDirectoryExists($directory, 0700, true);
        $handler = new FileSessionHandler($this->files, $directory, self::LIFETIME);
        $encrypter = new Encrypter(hash_hkdf('sha256', hex2bin($key), 32, 'temp-mail installer session'), 'AES-256-CBC');
        $session = new EncryptedStore(self::COOKIE, $handler, $encrypter, $request->cookies->get(self::COOKIE), 'json');
        $session->start();
        $request->setLaravelSession($session);

        if ($request->isMethod('POST')) {
            $token = $request->input('_token');
            if (! is_string($token) || ! hash_equals($session->token(), $token)) {
                return $this->message('Your setup form expired. Open the setup page and try again.', 419);
            }

            $limiter = new RateLimiter(new Repository(new FileStore($this->files, $directory.'/limits')));
            $bucket = 'installation:'.$request->ip();
            if ($limiter->tooManyAttempts($bucket, 15)) {
                return $this->message('Too many setup requests. Wait a minute and try again.', 429);
            }
            $limiter->hit($bucket, 60);
        }

        $response = $next($request);
        $session->save();
        if (random_int(1, 100) <= 2) {
            $handler->gc(self::LIFETIME * 60);
        }

        $response->headers->set('Cache-Control', 'no-store');
        $response->headers->setCookie(new Cookie(
            self::COOKIE,
            $session->getId(),
            now()->addMinutes(self::LIFETIME),
            $request->getBaseUrl().'/install',
            secure: $request->isSecure(),
            sameSite: Cookie::SAMESITE_STRICT,
        ));

        return $response;
    }

    private function message(string $message, int $status): Response
    {
        return new BladeResponse(view('install.notice', ['title' => 'Setup paused', 'description' => $message]), $status, ['Cache-Control' => 'no-store']);
    }
}
