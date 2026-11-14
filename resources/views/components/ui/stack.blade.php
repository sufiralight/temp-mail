@props(['direction' => 'vertical', 'gap' => 'md', 'align' => 'stretch', 'justify' => 'start', 'wrap' => false])
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
    $flow = match ($direction) {
        'horizontal' => 'flex-row',
        'responsive' => 'flex-col sm:flex-row',
        default => 'flex-col',
    };
    $distribution = match ($justify) {
        'center' => 'justify-center',
        'end' => 'justify-end',
        'between' => 'justify-between',
        default => 'justify-start',
    };
@endphp
<div {{ $attributes->class(['flex min-w-0', $flow, $spacing, $alignment, $distribution, 'flex-wrap' => $wrap && $direction === 'horizontal', 'sm:flex-wrap' => $wrap && $direction === 'responsive']) }}>{{ $slot }}</div>
