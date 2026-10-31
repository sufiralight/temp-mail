<x-installation-layout :title="__('install.titles.requirements')" :step="1">
    <p class="intro">{{ __('install.welcome') }}</p>
    <x-card>
        <h2>{{ __('install.system_checks') }}</h2>
        <ul class="checklist">
            @foreach($checks as $label => $pass)
            <li><span>{{ $label }}</span><span class="status {{ $pass ? 'status-pass' : 'status-fail' }}"><x-icon :name="$pass ? 'check' : 'alert'" />{{ $pass ? __('install.passed') : __('install.action_needed') }}</span></li>
            @endforeach
        </ul>
    </x-card>
    @if(!$passed)<x-alert error="true">{{ __('install.requirements_help') }}</x-alert>@endif
    <div class="actions"><span class="meta">{{ __('install.no_changes') }}</span><x-button href="{{ $passed ? '/install/database' : null }}" :disabled="!$passed">{{ __('install.continue') }}<x-icon name="arrow" /></x-button></div>
    <noscript><p class="field-help">{{ __('install.no_js') }}</p></noscript>
</x-installation-layout>
