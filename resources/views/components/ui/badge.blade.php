@props(['variant' => 'neutral'])
@php
    $tone = match ($variant) {
        'success' => 'bg-success',
        'warning' => 'bg-warning',
        'error' => 'bg-error',
        'info' => 'bg-info',
        default => null,
    };
@endphp
<span {{ $attributes->class(['ui-badge']) }}>
    @if ($tone)<span aria-hidden="true" class="size-2 rounded-full {{ $tone }}"></span>@endif
    {{ $slot }}
</span>
