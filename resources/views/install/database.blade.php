<x-installation-layout :title="__('install.titles.database')" :step="2">
    <p class="intro">{{ __('install.database_intro') }}</p>
    <x-validation-summary />
    <form method="POST" action="/install/database" x-data="submitState" x-on:submit="submit" x-bind:aria-busy="busy">
        @csrf
        <x-card>
            <div class="field-row"><x-input name="db_host" :label="__('install.fields.db_host')" value="127.0.0.1" required autocomplete="off" /><x-input name="db_port" :label="__('install.fields.db_port')" type="number" :value="config('installation.database_port')" required min="1" max="65535" /></div>
            <x-input name="db_name" :label="__('install.fields.db_name')" required maxlength="64" autocomplete="off" />
            <x-input name="db_user" :label="__('install.fields.db_user')" required maxlength="128" autocomplete="off" />
            <x-password-field name="db_password" :label="__('install.fields.db_password')" :required="false" maxlength="1024" :help="__('install.secret_help')" />
            <p class="field-help">{{ __('install.tls_help') }}</p>
        </x-card>
        <div class="actions"><a class="text-link" href="/install">{{ __('install.back') }}</a><x-button><x-submit-label :text="__('install.test_connection')" /></x-button></div>
    </form>
</x-installation-layout>
