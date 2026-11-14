@props(['variant' => 'info', 'title' => null, 'live' => null])
@php
    [$icon, $tone, $status] = match ($variant) {
        'success' => ['circle-check', 'text-success', __('Success')],
        'warning' => ['triangle-alert', 'text-warning', __('Warning')],
        'error' => ['circle-alert', 'text-error', __('Error')],
        default => ['info', 'text-info', __('Information')],
    };
@endphp
<div {{ $attributes->class(['ui-alert'])->merge(['role' => match ($live) { 'polite' => 'status', 'assertive' => 'alert', default => null }]) }}>
    <x-ui.icon :name="$icon" :class="$tone" />
    <div>
        <p class="text-label">{{ $title ?? $status }}</p>
        @if (filled($title))<span class="sr-only">{{ $status }}</span>@endif
        <div class="text-small text-foreground-muted">{{ $slot }}</div>
    </div>
</div>
