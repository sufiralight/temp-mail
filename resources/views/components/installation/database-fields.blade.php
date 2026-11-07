@props(['values' => [], 'fieldErrors' => []])

<div class="installer-database-address">
    <x-installation.field name="database_host" label="Database Host" :value="$values['database_host'] ?? null" :error="$fieldErrors['database_host'] ?? null" autocomplete="off" autocapitalize="none" spellcheck="false" required />
    <x-installation.field name="database_port" label="Port" :value="$values['database_port'] ?? null" :error="$fieldErrors['database_port'] ?? null" inputmode="numeric" autocomplete="off" required />
</div>
<x-installation.field name="database_name" label="Database Name" :value="$values['database_name'] ?? null" :error="$fieldErrors['database_name'] ?? null" autocomplete="off" autocapitalize="none" spellcheck="false" required />
<x-installation.field name="database_username" label="Username" :value="$values['database_username'] ?? null" :error="$fieldErrors['database_username'] ?? null" autocomplete="off" autocapitalize="none" spellcheck="false" required />
<x-installation.field name="database_password" label="Password" type="password" :error="$fieldErrors['database_password'] ?? null" autocomplete="off" />
