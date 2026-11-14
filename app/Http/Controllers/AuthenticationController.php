<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AuthenticationController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);
        $credentials = $this->validateFor($request, 'login', ['email' => 'required|email:rfc|max:254', 'password' => 'required|string|max:1024']);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return redirect()->route('login')->withErrors(['email' => 'The email or password is incorrect.'])->withInput($request->only('email'));
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return redirect()->route('account');
    }

    public function register(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);
        $data = $this->validateFor($request, 'register', [
            'name' => 'required|string|max:120',
            'email' => 'required|email:rfc|max:254|unique:users,email',
            'password' => $this->passwordRules(),
        ]);

        try {
            $user = User::create($data);
        } catch (UniqueConstraintViolationException) {
            return redirect()->route('register')->withErrors(['email' => 'This email address is already registered.'])->withInput($request->only('name', 'email'));
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return redirect()->route('verification.notice')->with('status', $this->sendVerification($user));
    }

    public function forgot(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);
        $data = $this->validateFor($request, 'password.request', ['email' => 'required|email:rfc|max:254']);

        if ($this->mailEnabled()) {
            try {
                Password::sendResetLink($data);
            } catch (TransportExceptionInterface) {
                // Keep the response identical for unknown addresses and delivery failures.
            }
        }

        return redirect()->route('password.request')->with('status', 'If this address can receive a reset link, check your inbox. If no message arrives, try again later.');
    }

    public function reset(Request $request): RedirectResponse|Response
    {
        $this->normalizeEmail($request);
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:rfc|max:254',
            'token' => 'required|string|size:64|alpha_num:ascii',
            'password' => $this->passwordRules(),
        ]);

        if ($validator->fails()) {
            return $this->resetErrors($request, $validator->errors());
        }

        $data = $validator->validated();
        $lock = Cache::store('file')->lock('password-reset:'.hash('sha256', $data['email']), 30);
        $status = $lock->get(function () use ($data) {
            return Password::reset($data, function (User $user, #[\SensitiveParameter] string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            });
        });

        if ($status !== Password::PasswordReset) {
            return $this->resetErrors($request, new MessageBag(['email' => 'This reset link is invalid or expired. Request a new link.']));
        }

        return redirect()->route('login')->with('status', 'Password updated. Sign in with your new password.');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('account');
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('account');
        }

        return redirect()->route('verification.notice')->with('status', $this->sendVerification($request->user()));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function resetErrors(Request $request, MessageBag $messages): Response
    {
        $token = $request->input('token');
        $email = $request->input('email');
        $errors = (new ViewErrorBag)->put('default', $messages);

        return response()->view('auth.reset', [
            'token' => is_string($token) && strlen($token) <= 128 ? $token : '',
            'email' => is_string($email) && strlen($email) <= 254 ? $email : '',
            'errors' => $errors,
        ], 422);
    }

    private function validateFor(Request $request, string $route, array $rules): array
    {
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            throw (new ValidationException($validator))->redirectTo(route($route));
        }

        return $validator->validated();
    }

    private function normalizeEmail(Request $request): void
    {
        $value = $request->input('email');
        $request->merge(['email' => is_string($value) ? strtolower(trim($value)) : null]);
    }

    private function passwordRules(): array
    {
        return ['required', 'string', 'confirmed', 'min:14', function ($attribute, $value, $fail): void {
            if (is_string($value) && (strlen($value) > 72 || str_contains($value, "\0"))) {
                $fail('Use a password of at most 72 bytes without null characters.');
            }
        }];
    }

    private function mailEnabled(): bool
    {
        return config('mail.default') === 'smtp' || (app()->environment('local', 'testing') && config('mail.default') === 'array');
    }

    private function sendVerification(User $user): string
    {
        if ($this->mailEnabled()) {
            try {
                $user->sendEmailVerificationNotification();

                return config('mail.default') === 'array'
                    ? 'Verification email is available in the local test transport only.'
                    : 'Check your inbox for a verification link.';
            } catch (TransportExceptionInterface) {
                // Transport diagnostics can contain private configuration.
            }
        }

        return 'Verification email is unavailable right now. Try resending later.';
    }
}
