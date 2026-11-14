@props(['columns' => 2, 'gap' => 'lg', 'align' => 'stretch'])
@php
    $spacing = match ($gap) {
        'none' => 'gap-0',
        'sm' => 'gap-2',
        'lg' => 'gap-6',
        'xl' => 'gap-8',
        default => 'gap-4',
    };
    $alignment = match ($align) {
        'start' => 'items-start',
        'center' => 'items-center',
        'end' => 'items-end',
        default => 'items-stretch',
    };
    $tracks = match ($columns) {
        1, '1' => '',
        3, '3' => 'md:grid-cols-2 lg:grid-cols-3',
        4, '4' => 'md:grid-cols-2 lg:grid-cols-4',
        default => 'md:grid-cols-2',
    };
@endphp
<div {{ $attributes->class(['grid min-w-0 grid-cols-1', $tracks, $spacing, $alignment]) }}>{{ $slot }}</div>
