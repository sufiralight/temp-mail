<x-layouts.app-shell title="Overview" description="Manage your Temp Mail SaaS account." >
    <x-ui.card>
        <x-slot:header><h2 class="text-lg font-semibold">Account details</h2></x-slot:header>
        <dl class="grid gap-4 wrap-anywhere sm:grid-cols-2">
            @foreach (['Name' => auth()->user()->name, 'Email address' => auth()->user()->email, 'Account type' => auth()->user()->can('super-admin') ? 'Super Admin' : 'User', 'Email verification' => auth()->user()->hasVerifiedEmail() ? 'Verified' : 'Pending', 'Member since' => auth()->user()->created_at?->toFormattedDateString()] as $label => $value)
                @if ($value !== null)
                    <div><dt class="text-label">{{ $label }}</dt><dd class="text-small text-foreground-muted">{{ $value }}</dd></div>
                @endif
            @endforeach
        </dl>
        <x-slot:footer><a href="{{ route('account.settings', absolute: false) }}" class="ui-button" data-variant="secondary">Account Settings</a></x-slot:footer>
    </x-ui.card>
</x-layouts.app-shell>
