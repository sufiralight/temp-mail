<x-auth-panel title="Verify your email" description="Open the verification link sent to your email address to finish setting up your account.">
    <form method="post" action="{{ route('verification.send', absolute: false) }}">
        @csrf
        <x-ui.button type="submit" full-width>Resend Verification Email</x-ui.button>
    </form>
    <x-slot:footer>
        <form method="post" action="{{ route('logout', absolute: false) }}">
            @csrf
            <x-ui.button type="submit" variant="ghost">Sign Out</x-ui.button>
        </form>
    </x-slot:footer>
</x-auth-panel>
