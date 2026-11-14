<?php

namespace App\Http\Controllers;

use App\Http\Middleware\AuthorizeInstallation;
use App\Installation\DatabaseSetup;
use App\Installation\EnvironmentFile;
use App\Installation\State;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class InstallationController extends Controller
{
    public function show(Request $request, State $state): View
    {
        $status = $state->status();
        $complete = $request->session()->get('installation.complete', 0) > now()->timestamp;
        abort_unless($status === 'fresh' || ($status === 'complete' && $complete), 404);

        return view('installation.wizard', [
            'step' => $status === 'complete' ? 3 : ($this->verified($request) && $request->session()->get('installation.admin') ? 2 : 1),
            'fields' => $request->session()->get('installation.fields', []),
            'verified' => $this->verified($request) !== null,
        ]);
    }

    public function testConnection(Request $request, DatabaseSetup $database): RedirectResponse
    {
        $this->verifyToken($request);

        $request->session()->forget(['installation.verified', 'installation.admin']);
        $validator = Validator::make($request->all(), [
            'engine' => 'required|in:mysql,mariadb',
            'host' => ['required', 'string', 'max:253', 'regex:/\A[a-zA-Z0-9.:-]+\z/'],
            'port' => 'required|integer|min:1|max:65535',
            'database' => ['required', 'string', 'max:64', 'regex:/\A[a-zA-Z0-9_-]+\z/'],
            'username' => ['required', 'string', 'max:128', 'regex:/\A[a-zA-Z0-9_.-]+\z/'],
            'database_password' => ['nullable', 'string', 'max:512', 'not_regex:/[\x00-\x1f\x7f]/'],
        ]);
        if ($validator->fails()) {
            return redirect('/install')->withErrors($validator);
        }

        $fields = $validator->validated();
        $request->session()->put('installation.fields', collect($fields)->except('database_password')->all());
        try {
            $database->probe($fields);
            $request->session()->put('installation.verified', [
                'expires' => now()->addMinutes(10)->timestamp,
                'ciphertext' => Crypt::encryptString(json_encode($fields, JSON_THROW_ON_ERROR)),
            ]);
        } catch (Throwable) {
            return redirect('/install')->withErrors(['connection' => 'The connection could not be verified. Check the database settings and permissions.']);
        }

        return redirect('/install')->with('notice', 'Connection verified. You can continue.');
    }

    public function next(Request $request): RedirectResponse
    {
        $this->verifyToken($request);

        if ($request->input('direction') === 'back') {
            $request->session()->forget(['installation.admin', 'installation.verified']);
        } elseif ($this->verified($request)) {
            $request->session()->put('installation.admin', true);
        } else {
            return $this->requireVerification();
        }

        return redirect('/install');
    }

    public function install(Request $request, State $state, DatabaseSetup $database, EnvironmentFile $environment): RedirectResponse
    {
        $this->verifyToken($request);

        $fields = $this->verified($request);
        if (! $fields || ! $request->session()->get('installation.admin')) {
            return $this->requireVerification();
        }

        $request->merge(['email' => is_string($request->input('email')) ? strtolower(trim($request->input('email'))) : null]);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'email' => 'required|email:rfc|max:254',
            'password' => ['required', 'string', 'confirmed', 'min:14', function ($attribute, $value, $fail): void {
                if (is_string($value) && (strlen($value) > 72 || str_contains($value, "\0"))) {
                    $fail('Use a password of at most 72 bytes without null characters.');
                }
            }],
        ]);
        if ($validator->fails()) {
            return redirect('/install')->withErrors($validator);
        }

        try {
            $state->locked(function () use ($request, $fields, $state, $database, $environment, $validator): void {
                if ($state->status() !== 'fresh' || ! app(AuthorizeInstallation::class)->allows($request)) {
                    throw new RuntimeException('Installation unavailable.');
                }

                $updated = $environment->prepare($fields);
                $database->provision(
                    $fields,
                    $validator->validated(),
                    fn () => $state->write($state->path('state'), 'in-progress-v1'),
                    function () use ($state, $updated): void {
                        $state->write(app()->environmentFilePath(), $updated);
                        $state->write($state->path('state'), 'complete-v1');
                    },
                );
            });
        } catch (Throwable) {
            if ($state->status() !== 'fresh') {
                abort(503, 'Installation requires operator recovery. See the deployment guide.');
            }

            return redirect('/install')->withErrors(['installation' => 'Installation could not start. Verify the private configuration, empty database and permissions.']);
        }

        $request->session()->forget(['installation.fields', 'installation.verified', 'installation.admin']);
        $request->session()->regenerate();
        $request->session()->put('installation.complete', now()->addMinutes(10)->timestamp);

        return redirect('/install');
    }

    private function verifyToken(Request $request): void
    {
        $token = $request->input('_token');
        abort_unless(is_string($token) && hash_equals($request->session()->token(), $token), 419);
    }

    private function verified(Request $request): ?array
    {
        $verified = $request->session()->get('installation.verified');
        if (! $verified || $verified['expires'] <= now()->timestamp) {
            return null;
        }

        try {
            return json_decode(Crypt::decryptString($verified['ciphertext']), true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }
    }

    private function requireVerification(): RedirectResponse
    {
        return redirect('/install')->withErrors(['connection' => 'Verify the database connection again before continuing.']);
    }
}
