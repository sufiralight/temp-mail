<x-auth-panel title="Your account" description="You are signed in to Temp Mail SaaS.">
    <dl class="grid min-w-0 gap-2 wrap-anywhere">
        <dt class="text-label">Email address</dt>
        <dd class="text-small text-foreground-muted">{{ auth()->user()->email }}</dd>
        <dt class="text-label">Account type</dt>
        <dd class="text-small text-foreground-muted">{{ auth()->user()->can('super-admin') ? 'Super Admin' : 'User' }}</dd>
    </dl>
    <x-slot:footer>
        <form method="post" action="{{ route('logout', absolute: false) }}">
            @csrf
            <x-ui.button type="submit" variant="secondary">Sign Out</x-ui.button>
        </form>
    </x-slot:footer>
</x-auth-panel>
