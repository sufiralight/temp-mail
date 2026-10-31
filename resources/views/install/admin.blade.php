<x-installation-layout :title="__('install.titles.administrator')" :step="3">
    <p class="intro">{{ __('install.admin_intro') }}</p>
    <x-validation-summary />
    <form method="POST" action="/install/admin" x-data="submitState" x-on:submit="submit" x-bind:aria-busy="busy">
        @csrf
        <x-card>
            <x-input name="name" :label="__('install.fields.name')" required maxlength="255" autocomplete="name" />
            <x-input name="email" :label="__('install.fields.email')" type="email" required maxlength="255" autocomplete="email" />
            <x-password-field :label="__('install.fields.password')" minlength="12" maxlength="128" :help="__('install.password_help')" />
            <x-password-field name="password_confirmation" :label="__('install.fields.password_confirmation')" minlength="12" maxlength="128" />
        </x-card>
        <div class="actions"><a class="text-link" href="/install/database">{{ __('install.back') }}</a><x-button><x-submit-label :text="__('install.review')" /></x-button></div>
    </form>
</x-installation-layout>
