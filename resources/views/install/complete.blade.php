<x-installation-layout :title="__('install.titles.complete')" :step="4">
    <p class="intro">{{ __('install.complete_intro') }}</p>
    <x-card>
        <dl class="review">
            <div><dt>{{ __('install.fields.name') }}</dt><dd>{{ $name }}</dd></div>
            <div><dt>{{ __('install.fields.email') }}</dt><dd><bdi>{{ $email }}</bdi></dd></div>
            <div><dt>{{ __('install.database_verified') }}</dt><dd>MySQL {{ $capabilities['version'] }} · {{ $capabilities['tls'] ? __('install.encrypted') : __('install.local_unencrypted') }}</dd></div>
        </dl>
    </x-card>
    <x-alert>{{ __('install.migration_notice') }}</x-alert>
    <form method="POST" action="/install/complete" x-data="submitState" x-on:submit="submit" x-bind:aria-busy="busy">
        @csrf
        <div class="actions"><a class="text-link" href="/install/admin">{{ __('install.back') }}</a><x-button><x-submit-label :text="__('install.finish')" /></x-button></div>
    </form>
</x-installation-layout>
