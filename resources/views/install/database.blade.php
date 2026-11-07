<x-installation-layout
    title="Connect your database"
    description="Enter the connection details from your hosting control panel."
    :step="1"
>
    <x-installation.form
        :action="$action ?? null"
        :error="$formError ?? null"
        submit="Test Connection & Continue"
        pending-note="Connection testing will be available once setup actions are enabled."
    >
        <x-installation.database-fields :values="$values ?? []" :field-errors="$fieldErrors ?? []" />

        <details class="installer-help">
            <summary>Where do I find these details?</summary>
            <p>Open the database section of your hosting control panel and copy the host, port, database name, and user credentials. On localhost, use the details from your local database setup.</p>
        </details>
    </x-installation.form>
</x-installation-layout>
