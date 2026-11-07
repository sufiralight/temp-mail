<?php

namespace App\Http\Controllers;

use App\Installation\InstallationDatabase;
use App\Installation\InstallationException;
use App\Installation\InstallationFiles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class FirstInstallationController extends Controller
{
    public function testDatabase(Request $request, InstallationDatabase $database, InstallationFiles $files): HttpResponse
    {
        $session = $request->session();
        $session->forget('verified_database');
        $values = $this->safeValues($request, ['database_host', 'database_port', 'database_name', 'database_username']);

        if (! $session->get('operator_verified')) {
            $key = $request->input('installation_key');
            $expected = $files->operatorKey();
            if (! is_string($key) || $expected === null || ! hash_equals($expected, $key)) {
                return $this->databasePage($request, $values, ['installation_key' => 'Enter the installation key from your hosting panel.'], 403);
            }

            $session->regenerate(true);
            $session->put('operator_verified', true);
        }

        $validator = Validator::make($request->only([
            'database_host', 'database_port', 'database_name', 'database_username', 'database_password',
        ]), [
            'database_host' => ['nullable', 'string', 'max:253', 'regex:'.InstallationDatabase::HOST_PATTERN],
            'database_port' => ['nullable', 'integer', 'between:1,65535'],
            'database_name' => ['required', 'string', 'max:64', 'regex:/\A[^;\x00-\x1f]+\z/u'],
            'database_username' => ['required', 'string', 'max:255'],
            'database_password' => ['nullable', 'string', 'max:1024', 'regex:/\A[^\x00]*\z/u'],
        ]);

        if ($validator->fails()) {
            return $this->databasePage($request, $values, $this->firstErrors($validator->errors()), 422);
        }

        $settings = $validator->validated();
        $settings['database_host'] ??= $database->defaultHost();
        $settings['database_port'] = (int) ($settings['database_port'] ?? $database->defaultPort());
        $settings['database_password'] ??= '';
        try {
            $database->test($settings);
        } catch (Throwable) {
            return $this->databasePage($request, $values, [], 422, 'Could not connect. Check the host, port, database name, username, and password.');
        }

        $session->put('verified_database', $settings);

        return new RedirectResponse(route('install.admin'), 303);
    }

    public function admin(Request $request): HttpResponse
    {
        if (! $request->session()->get('operator_verified') || ! $request->session()->has('verified_database')) {
            return new RedirectResponse(route('install'));
        }

        return $this->adminPage($request);
    }

    public function finish(Request $request, InstallationDatabase $database): HttpResponse
    {
        $session = $request->session();
        $settings = $session->get('verified_database');
        if (! $session->get('operator_verified') || ! is_array($settings)) {
            return new RedirectResponse(route('install'), 303);
        }

        $values = $this->safeValues($request, ['name', 'email']);
        $validator = Validator::make($request->only(['name', 'email', 'password', 'password_confirmation']), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:12', 'confirmed', function (string $attribute, mixed $value, \Closure $fail): void {
                if (is_string($value) && (strlen($value) > 72 || str_contains($value, "\0"))) {
                    $fail('Use a password of up to 72 bytes without null characters.');
                }
            }],
        ]);

        if ($validator->fails()) {
            return $this->adminPage($request, $values, $this->firstErrors($validator->errors()), 422, 'Review the highlighted fields.');
        }

        $admin = $validator->validated();
        $admin['email'] = strtolower($admin['email']);
        try {
            $database->install($settings, $admin);
        } catch (InstallationException $exception) {
            return $this->adminPage($request, $values, [], 422, $exception->getMessage());
        } catch (Throwable) {
            return $this->adminPage($request, $values, [], 503, 'Setup could not finish. Check database permissions and write access to .env and private storage, then try again.');
        }

        $session->invalidate();

        return new RedirectResponse(route('login', ['installation' => 'complete']), 303);
    }

    public function handoff(Request $request): Response
    {
        $complete = $request->query('installation') === 'complete';

        return new Response(view('install.notice', [
            'title' => $complete ? 'Installation complete' : 'Workspace ready',
            'description' => $complete ? 'Installation complete. Sign in to continue.' : 'Your Temp Mail workspace is ready for sign-in.',
        ]), 200, ['Cache-Control' => 'no-store']);
    }

    /**
     * @param  array<string, string>  $values
     * @param  array<string, string>  $errors
     */
    private function databasePage(Request $request, array $values, array $errors, int $status, ?string $message = null): Response
    {
        return new Response(view('install.database', [
            'action' => route('install.database'),
            'csrfToken' => $request->session()->token(),
            'needsKey' => ! $request->session()->get('operator_verified'),
            'values' => $values,
            'fieldErrors' => $errors,
            'formError' => $message ?? 'Review the highlighted fields.',
        ]), $status);
    }

    /**
     * @param  array<string, string>  $values
     * @param  array<string, string>  $errors
     */
    private function adminPage(Request $request, array $values = [], array $errors = [], int $status = 200, ?string $message = null): Response
    {
        return new Response(view('install.admin', [
            'action' => route('install.finish'),
            'csrfToken' => $request->session()->token(),
            'editDatabaseUrl' => route('install'),
            'values' => $values,
            'fieldErrors' => $errors,
            'formError' => $message,
            'passwordHint' => 'Use at least 12 characters.',
        ]), $status);
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, string>
     */
    private function safeValues(Request $request, array $fields): array
    {
        return array_filter($request->only($fields), is_string(...));
    }

    /** @return array<string, string> */
    private function firstErrors(MessageBag $errors): array
    {
        return array_map(fn (array $messages): string => $messages[0], $errors->getMessages());
    }
}
