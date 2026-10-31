@props(['name' => 'password', 'label', 'help' => null, 'required' => true])
<div class="field" x-data="passwordField" data-show="{{ __('install.show_password') }}" data-hide="{{ __('install.hide_password') }}">
    <x-label :for="$name">{{ $label }}</x-label>
    <div class="password-wrap">
        <input x-ref="password" class="input" id="{{ $name }}" name="{{ $name }}" type="password" autocomplete="new-password" @required($required) {{ $attributes }} @if($errors->has($name)) aria-invalid="true" @endif aria-describedby="{{ $help ? $name.'-help ' : '' }}{{ $errors->has($name) ? $name.'-error' : '' }}">
        <button class="password-toggle enhancement" type="button" x-on:click="toggle" x-bind:aria-label="label" x-bind:aria-pressed="visible" aria-label="{{ __('install.show_password') }}" aria-pressed="false"><x-icon name="eye" /></button>
    </div>
    @if($help)<p id="{{ $name }}-help" class="field-help">{{ $help }}</p>@endif
    <x-validation-message :name="$name" />
</div>
