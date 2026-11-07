@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'error' => null, 'passwordLabel' => 'password'])

<div class="installer-field">
    <label for="{{ $name }}">{{ $label }}</label>
    <div @class(['installer-input-wrap', 'installer-input-wrap--password' => $type === 'password'])>
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if ($type !== 'password' && $value !== null) value="{{ $value }}" @endif
            @if ($hint || $error) aria-describedby="{{ implode(' ', array_filter([$hint ? $name.'-hint' : null, $error ? $name.'-error' : null])) }}" @endif
            @if ($error) aria-invalid="true" @endif
            {{ $attributes->class(['installer-input']) }}
        >
        @if ($type === 'password')
            <button class="installer-password-toggle" type="button" aria-controls="{{ $name }}" aria-label="Show {{ $passwordLabel }}" title="Show {{ $passwordLabel }}" data-password-toggle="{{ $passwordLabel }}" hidden>
                <x-installation.icon name="eye" data-password-show />
                <x-installation.icon name="eye-off" data-password-hide hidden />
            </button>
            <span class="installer-sr-only" role="status" data-password-status></span>
        @endif
    </div>
    @if ($hint)
        <p class="installer-hint" id="{{ $name }}-hint">{{ $hint }}</p>
    @endif
    @if ($error)
        <p class="installer-error" id="{{ $name }}-error">{{ $error }}</p>
    @endif
</div>
