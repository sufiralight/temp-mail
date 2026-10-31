<x-app-layout :title="__('install.installation_complete')">
    <p class="eyebrow">{{ __('install.setup_complete') }}</p>
    <h1>{{ __('install.installation_complete') }}</h1>
    <p class="intro">{{ __('install.login_pending') }}</p>
    <x-alert><x-icon name="check" />{{ __('install.lock_notice') }}</x-alert>
</x-app-layout>
