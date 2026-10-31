@props(['name', 'label', 'type' => 'text', 'value' => '', 'help' => null])
<div class="field">
    <x-label :for="$name">{{ $label }}</x-label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" {{ $attributes->class('input') }} @if($errors->has($name)) aria-invalid="true" @endif aria-describedby="{{ $help ? $name.'-help ' : '' }}{{ $errors->has($name) ? $name.'-error' : '' }}">
    @if($help)<p id="{{ $name }}-help" class="field-help">{{ $help }}</p>@endif
    <x-validation-message :name="$name" />
</div>
