<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountSettingsController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        $request->merge(['email' => is_string($email) ? strtolower(trim($email)) : null]);
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => ['required', 'email:rfc', 'max:254', Rule::unique('users')->ignore($user->id)],
            'current_password' => 'nullable|string|max:1024',
        ]);

        try {
            $changed = DB::transaction(function () use ($user, $validated): bool {
                $account = User::query()->lockForUpdate()->findOrFail($user->id);
                $changed = $account->email !== $validated['email'];
                if ($changed) {
                    if (! password_verify($validated['current_password'] ?? '', $account->password)) {
                        throw ValidationException::withMessages(['current_password' => 'Enter your current password to change your email.']);
                    }

                    DB::table('password_reset_tokens')->whereIn('email', [$account->email, $validated['email']])->delete();
                    $account->forceFill(['email_verified_at' => null, 'remember_token' => Str::random(60)]);
                }

                $account->fill(['name' => $validated['name'], 'email' => $validated['email']])->save();

                return $changed;
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['email' => 'This email address is already registered.']);
        }

        if ($changed) {
            return redirect()->route('verification.notice')->with('status', 'Email updated. Request a verification link for your new address.');
        }

        return redirect()->route('account.settings')->with('status', 'Account settings saved.');
    }
}
