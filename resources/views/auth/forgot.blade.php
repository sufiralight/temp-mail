<x-auth-panel title="Forgot password" description="Enter the email address associated with your account.">
    <form method="post" action="{{ route('password.email', absolute: false) }}">
        @csrf
        <x-ui.stack>
            <x-ui.input name="email" label="Email address" type="email" :value="old('email')" autocomplete="email" required />
            <x-ui.button type="submit" full-width>Send Reset Link</x-ui.button>
        </x-ui.stack>
    </form>
    <x-slot:footer><a href="{{ route('login', absolute: false) }}" class="text-small underline underline-offset-4">Back to Sign In</a></x-slot:footer>
</x-auth-panel>
