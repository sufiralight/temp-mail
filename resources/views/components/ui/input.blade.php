@props(['name' => null, 'id' => null, 'label' => null, 'description' => null, 'error' => null, 'required' => false, 'disabled' => false, 'size' => 'md', 'type' => 'text', 'value' => null, 'readonly' => false, 'passwordToggle' => false])
@php
    $id ??= 'field-'.Illuminate\Support\Str::uuid();
    $error ??= ($name && isset($errors)) ? $errors->first($name) : null;
    $describedBy = trim(implode(' ', array_filter([
        $attributes->get('aria-describedby'),
        filled($description) ? $id.'-description' : null,
        filled($error) ? $id.'-error' : null,
    ])));
@endphp
@php
    $type = in_array($type, ['text', 'email', 'password', 'url', 'search', 'number', 'tel', 'date', 'time', 'datetime-local', 'month', 'week'], true) ? $type : 'text';
    $passwordToggle = $passwordToggle && $type === 'password';
@endphp
<x-ui.form-field :id="$id" :label="$label" :description="$description" :error="$error" :required="$required">
    @if ($passwordToggle)<div class="ui-password">@endif
    <input {{ $attributes->except(['aria-describedby', 'aria-invalid'])->class(['ui-control'])->merge(['data-size' => $size]) }} id="{{ $id }}" @if ($name !== null) name="{{ $name }}" @endif
        @required($required) @disabled($disabled)
        @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        @if (filled($error)) aria-invalid="true" @endif
        type="{{ $type }}" @readonly($readonly)
        @if ($value !== null && $type !== 'password') value="{{ $value }}" @endif>
    @if ($passwordToggle)
        <x-ui.icon-button icon="eye" :label="__('Show password')" variant="ghost"
            :aria-controls="$id" aria-pressed="false" data-password-toggle hidden :disabled="$disabled || $readonly" />
    </div>
    @endif
</x-ui.form-field>
