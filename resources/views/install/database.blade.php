<x-installation-layout
    title="Connect your database"
    description="Enter the connection details from your hosting control panel."
    :step="1"
>
    <x-installation.form
        :action="$action ?? null"
        :error="$formError ?? null"
        :csrf-token="$csrfToken ?? null"
        submit="Test Connection & Continue"
        busy-label="Testing connection…"
        pending-note="Add an installation key in private storage to enable setup."
    >
        @if ($needsKey ?? false)
            <x-installation.field name="installation_key" label="Installation Key" type="password" password-label="installation key" :error="$fieldErrors['installation_key'] ?? null" autocomplete="off" required />
        @endif
        <x-installation.database-fields :values="$values ?? []" :field-errors="$fieldErrors ?? []" />

        <details class="installer-help">
            <summary>Where do I find these details?</summary>
            <p>Open the database section of your hosting control panel and copy the host, port, database name, and user credentials. On localhost, use the details from your local database setup.</p>
        </details>
    </x-installation.form>
</x-installation-layout>
