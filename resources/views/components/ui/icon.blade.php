@props(['name', 'size' => 20, 'label' => null])
@php
    $catalog = ['arrow-right', 'check', 'circle-alert', 'circle-check', 'eye', 'eye-off', 'info', 'plus', 'search', 'trash-2', 'triangle-alert', 'x', 'menu', 'sun', 'moon', 'monitor', 'user', 'settings', 'layout-dashboard'];
    if (! in_array($name, $catalog, true) || ! in_array((string) $size, ['16', '20', '24'], true)) {
        throw new InvalidArgumentException('Unknown UI icon or unsupported icon size.');
    }
@endphp
<svg {{ $attributes->except(['aria-hidden', 'aria-label', 'role', 'focusable', 'width', 'height', 'xmlns', 'viewBox', 'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin'])->class(['ui-icon'])->merge(['width' => $size, 'height' => $size]) }}
    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"
    @if (filled($label)) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif>
    @include('components.ui.icons.'.$name)
</svg>
