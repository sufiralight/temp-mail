<x-auth-panel title="Reset password" description="Choose a new password for your account.">
    <form method="post" action="{{ route('password.update', absolute: false) }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-ui.stack>
            <x-ui.input name="email" label="Email address" type="email" :value="$email ?? ''" autocomplete="email" :error="$errors->first('email') ?: $errors->first('token')" required />
            <x-ui.password-field name="password" label="New password" :error="$errors->first('password')" description="Use at least 14 characters and at most 72 bytes." autocomplete="new-password" required />
            <x-ui.password-field name="password_confirmation" label="Confirm new password" autocomplete="new-password" required />
            <x-ui.button type="submit" full-width>Update Password</x-ui.button>
        </x-ui.stack>
    </form>
    <x-slot:footer><a href="{{ route('password.request', absolute: false) }}" class="text-small underline underline-offset-4">Request a New Link</a></x-slot:footer>
</x-auth-panel>
