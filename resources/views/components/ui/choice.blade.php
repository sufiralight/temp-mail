@props(['name' => null, 'id' => null, 'label' => null, 'description' => null, 'error' => null, 'required' => false, 'disabled' => false, 'type', 'value', 'checked'])
@php
    $id ??= 'field-'.Illuminate\Support\Str::uuid();
    $error ??= ($name && isset($errors)) ? $errors->first($name) : null;
    $describedBy = trim(implode(' ', array_filter([
        $attributes->get('aria-describedby'),
        filled($description) ? $id.'-description' : null,
        filled($error) ? $id.'-error' : null,
    ])));
@endphp
<div class="ui-field">
    <label for="{{ $id }}" class="ui-choice">
        <input {{ $attributes->except(['aria-describedby', 'aria-invalid'])->class(['ui-choice-input']) }}
            type="{{ $type === 'radio' ? 'radio' : 'checkbox' }}" id="{{ $id }}" @if ($name !== null) name="{{ $name }}" @endif
        @required($required) @disabled($disabled)
        @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        @if (filled($error)) aria-invalid="true" @endif
            value="{{ $value }}" @checked($checked)>
        <span>{{ $label }} @if ($required)<span aria-hidden="true">*</span>@endif</span>
    </label>
    @if (filled($description))
        <p id="{{ $id }}-description" class="text-small text-foreground-muted">{{ $description }}</p>
    @endif
    @if (filled($error))
        <p id="{{ $id }}-error" class="text-small">{{ $error }}</p>
    @endif
</div>
