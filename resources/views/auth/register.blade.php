<x-auth-panel title="Create an account" description="Enter your details to get started.">
    <form method="post" action="{{ route('register', absolute: false) }}">
        @csrf
        <x-ui.stack>
            <x-ui.input name="name" label="Full name" :value="old('name')" autocomplete="name" required />
            <x-ui.input name="email" label="Email address" type="email" :value="old('email')" autocomplete="email" required />
            <x-ui.password-field name="password" label="Password" description="Use at least 14 characters and at most 72 bytes." autocomplete="new-password" required />
            <x-ui.password-field name="password_confirmation" label="Confirm password" autocomplete="new-password" required />
            <x-ui.button type="submit" full-width>Create Account</x-ui.button>
        </x-ui.stack>
    </form>
    <x-slot:footer>
        <p class="text-small">Already registered? <a href="{{ route('login', absolute: false) }}" class="underline underline-offset-4">Sign in</a></p>
    </x-slot:footer>
</x-auth-panel>
