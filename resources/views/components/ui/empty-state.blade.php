@props(['title', 'description' => null, 'icon' => null, 'headingLevel' => 2])
@php($heading = match ((string) $headingLevel) { '3' => 'h3', '4' => 'h4', default => 'h2' })
<div {{ $attributes->class(['grid min-w-0 justify-items-center gap-3 px-4 py-8 text-center wrap-anywhere']) }}>
    @if ($icon)
        <span class="inline-flex rounded-lg bg-surface-muted p-3 text-foreground-muted">
            <x-ui.icon :name="$icon" :size="24" />
        </span>
    @endif
    <{{ $heading }} class="text-lg font-semibold">{{ $title }}</{{ $heading }}>
    @if (filled($description))<p class="max-w-prose text-small text-foreground-muted">{{ $description }}</p>@endif
    @if (isset($primary) || isset($secondary))
        <x-ui.stack direction="horizontal" gap="sm" justify="center" wrap class="mt-1">
            {{ $primary ?? '' }}
            {{ $secondary ?? '' }}
        </x-ui.stack>
    @endif
</div>
