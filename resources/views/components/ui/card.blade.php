@props(['muted' => false])
<div {{ $attributes->class(['ui-card', $muted ? 'bg-surface-muted' : 'bg-surface']) }}>
    @isset($header)<div class="border-b border-border pb-4">{{ $header }}</div>@endisset
    <div>{{ $slot }}</div>
    @isset($footer)<div class="border-t border-border pt-4">{{ $footer }}</div>@endisset
</div>
