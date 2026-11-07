<x-installation-layout
    title="Connect your database"
    description="Enter the connection details from your hosting control panel."
    :step="1"
>
    @empty($action)
        <x-installation.status>
            Add the installation key in private storage, then refresh this page to enable the connection test.
        </x-installation.status>
    @endempty

    <x-installation.form
        :action="$action ?? null"
        :error="$formError ?? null"
        :csrf-token="$csrfToken ?? null"
        submit="Test Connection & Continue"
        busy-label="Testing connection…"
    >
        @if ($needsKey ?? false)
            <x-installation.field name="installation_key" label="Installation Key" type="password" password-label="installation key" :error="$fieldErrors['installation_key'] ?? null" autocomplete="off" required />
        @endif
        <x-installation.database-fields :values="$values ?? []" :field-errors="$fieldErrors ?? []" />

        <details class="installer-help">
            <summary>Where do I find these details?</summary>
            <p>Host and port are filled in from the application settings. Use the details from your hosting control panel if they differ, then enter the database name and user credentials.</p>
        </details>
    </x-installation.form>
</x-installation-layout>
