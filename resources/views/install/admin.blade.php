<x-installation-layout
    title="Create your admin account"
    description="Set up the first administrator for your Temp Mail workspace."
    :step="2"
    :focus-title="true"
>
    <x-installation.status tone="success">
        <span><x-installation.icon name="check" /> Database connected</span>
        @isset($editDatabaseUrl)
            <a href="{{ $editDatabaseUrl }}" aria-label="Edit database settings">Edit</a>
        @endisset
    </x-installation.status>

    <x-installation.form :action="$action ?? null" :error="$formError ?? null" submit="Finish Installation">
        <x-installation.field name="name" label="Full Name" :value="$values['name'] ?? null" :error="$fieldErrors['name'] ?? null" autocomplete="name" required />
        <x-installation.field name="email" label="Email Address" type="email" :value="$values['email'] ?? null" :error="$fieldErrors['email'] ?? null" autocomplete="email" required />
        <x-installation.field name="password" label="Password" type="password" :hint="$passwordHint ?? 'Use a unique password for this administrator account.'" :error="$fieldErrors['password'] ?? null" autocomplete="new-password" required />
        <x-installation.field name="password_confirmation" label="Confirm Password" type="password" password-label="password confirmation" :error="$fieldErrors['password_confirmation'] ?? null" autocomplete="new-password" required />
    </x-installation.form>
</x-installation-layout>
