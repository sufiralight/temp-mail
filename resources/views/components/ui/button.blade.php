@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button', 'disabled' => false, 'loading' => false, 'leadingIcon' => null, 'trailingIcon' => null, 'fullWidth' => false, 'iconOnly' => false])
@php
    $variant = in_array($variant, ['primary', 'secondary', 'ghost', 'danger'], true) ? $variant : 'primary';
    $size = in_array($size, ['sm', 'md', 'lg'], true) ? $size : 'md';
    $type = in_array($type, ['button', 'submit', 'reset'], true) ? $type : 'button';
@endphp
<button {{ $attributes->class(['ui-button', 'w-full' => $fullWidth])->merge(['type' => $type, 'data-variant' => $variant, 'data-size' => $size, 'data-icon-only' => $iconOnly ? 'true' : null, 'aria-busy' => $loading ? 'true' : null]) }} @disabled($disabled || $loading)>
    @if ($loading)
        <x-ui.spinner size="sm" />
    @elseif ($leadingIcon)
        <x-ui.icon :name="$leadingIcon" :size="$size === 'sm' ? 16 : 20" />
    @endif
    {{ $slot }}
    @if ($trailingIcon)
        <x-ui.icon :name="$trailingIcon" :size="$size === 'sm' ? 16 : 20" />
    @endif
</button>
