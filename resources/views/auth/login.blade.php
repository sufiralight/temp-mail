<x-auth-panel title="Sign in" description="Access your Temp Mail SaaS account.">
    <form method="post" action="{{ route('login', absolute: false) }}">
        @csrf
        <x-ui.stack>
            <x-ui.input name="email" label="Email address" type="email" :value="old('email')" autocomplete="username" required />
            <x-ui.password-field name="password" label="Password" autocomplete="current-password" required />
            <x-ui.stack direction="horizontal" align="center" justify="between" wrap>
                <x-ui.checkbox name="remember" label="Remember me" />
                <a href="{{ route('password.request', absolute: false) }}" class="text-small underline underline-offset-4">Forgot password?</a>
            </x-ui.stack>
            <x-ui.button type="submit" full-width>Sign In</x-ui.button>
        </x-ui.stack>
    </form>
    <x-slot:footer>
        <p class="text-small">New here? <a href="{{ route('register', absolute: false) }}" class="underline underline-offset-4">Create an account</a></p>
    </x-slot:footer>
</x-auth-panel>
