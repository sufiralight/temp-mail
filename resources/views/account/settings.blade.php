<x-layouts.app-shell title="Account Settings" description="Update the name and email address used by your account." :breadcrumbs="['Overview' => 'account', 'Account Settings' => null]">
    <x-ui.card class="max-w-xl">
        <form method="post" action="{{ route('account.settings.update', absolute: false) }}">
            @csrf
            @method('PATCH')
            <x-ui.stack>
                <x-ui.input name="name" label="Full name" :value="old('name', auth()->user()->name)" autocomplete="name" required />
                <x-ui.input name="email" type="email" label="Email address" :value="old('email', auth()->user()->email)" autocomplete="email" required />
                <p class="text-small text-foreground-muted">{{ auth()->user()->hasVerifiedEmail() ? 'Email verified' : 'Email verification pending' }}</p>
                <x-ui.password-field name="current_password" label="Current password" description="Required only when changing your email address." autocomplete="current-password" />
                <div><x-ui.button type="submit">Save Changes</x-ui.button></div>
            </x-ui.stack>
        </form>
    </x-ui.card>
</x-layouts.app-shell>
