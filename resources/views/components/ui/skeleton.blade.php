@props(['shape' => 'text', 'size' => 'md'])
@php
    $dimensions = match ($shape) {
        'circle' => match ($size) {
            'sm' => 'size-8',
            'lg' => 'size-16',
            default => 'size-12',
        },
        'rectangle' => match ($size) {
            'sm' => 'h-12 w-full',
            'lg' => 'h-32 w-full',
            default => 'h-20 w-full',
        },
        default => match ($size) {
            'sm' => 'h-3 w-1/2',
            'lg' => 'h-5 w-full',
            default => 'h-4 w-3/4',
        },
    };
@endphp
<span {{ $attributes->except('aria-hidden')->class(['ui-skeleton block max-w-full bg-selected', $dimensions, $shape === 'rectangle' ? 'rounded-md' : 'rounded-full']) }} aria-hidden="true"></span>
