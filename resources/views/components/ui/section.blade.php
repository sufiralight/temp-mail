@props(['spacing' => 'md'])
@php
    $padding = match ($spacing) {
        'none' => 'py-0',
        'sm' => 'py-4 sm:py-6',
        'lg' => 'py-10 sm:py-14',
        default => 'py-6 sm:py-8',
    };
@endphp
<section {{ $attributes->class(['min-w-0', $padding]) }}>
    @if (isset($heading) || isset($description))
        <header class="mb-4 grid gap-2 sm:mb-6">
            {{ $heading ?? '' }}
            @isset($description)<div class="text-foreground-muted">{{ $description }}</div>@endisset
        </header>
    @endif
    {{ $slot }}
</section>
