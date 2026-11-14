@props(['size' => 'lg'])
@php
    $width = match ($size) {
        'sm' => 'max-w-md',
        'md' => 'max-w-2xl',
        'xl' => 'max-w-7xl',
        'full' => 'max-w-none',
        default => 'max-w-5xl',
    };
@endphp
<div {{ $attributes->class(['mx-auto w-full min-w-0 px-4 sm:px-6 lg:px-8', $width]) }}>{{ $slot }}</div>
