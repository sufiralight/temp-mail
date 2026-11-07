<x-installation-layout
    title="Restore configuration"
    description="Restore the database connection to access your existing workspace."
>
    @if ($operatorVerified ?? false)
        <x-installation.form :action="$action ?? null" :error="$formError ?? null" submit="Restore Configuration">
            <x-installation.database-fields :values="$values ?? []" :field-errors="$fieldErrors ?? []" />
        </x-installation.form>
    @else
        <x-installation.status>Operator verification is required before restoring configuration.</x-installation.status>
    @endif
</x-installation-layout>
