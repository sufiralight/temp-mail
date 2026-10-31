<x-app-layout :title="__('install.unavailable')">
    <p class="eyebrow">{{ __('install.brand') }}</p>
    <h1>{{ $reason === 'locked' ? __('install.locked') : __('install.unavailable') }}</h1>
    <p class="intro">{{ __('install.errors.'.$reason) !== 'install.errors.'.$reason ? __('install.errors.'.$reason) : __('install.errors.unavailable') }}</p>
    @if($reason === 'locked')<div class="actions"><x-button href="/login">{{ __('install.login_destination') }}</x-button></div>@endif
</x-app-layout>
