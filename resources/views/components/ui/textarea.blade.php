@props(['name' => null, 'id' => null, 'label' => null, 'description' => null, 'error' => null, 'required' => false, 'disabled' => false, 'size' => 'md', 'value' => null, 'rows' => 4, 'readonly' => false])
@php
    $id ??= 'field-'.Illuminate\Support\Str::uuid();
    $error ??= ($name && isset($errors)) ? $errors->first($name) : null;
    $describedBy = trim(implode(' ', array_filter([
        $attributes->get('aria-describedby'),
        filled($description) ? $id.'-description' : null,
        filled($error) ? $id.'-error' : null,
    ])));
@endphp
<x-ui.form-field :id="$id" :label="$label" :description="$description" :error="$error" :required="$required">
    <textarea {{ $attributes->except(['aria-describedby', 'aria-invalid'])->class(['ui-control'])->merge(['data-size' => $size]) }} id="{{ $id }}" @if ($name !== null) name="{{ $name }}" @endif
        @required($required) @disabled($disabled)
        @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        @if (filled($error)) aria-invalid="true" @endif
        rows="{{ $rows }}" @readonly($readonly)>{{ "\n" }}{{ $value ?? $slot }}</textarea>
</x-ui.form-field>
