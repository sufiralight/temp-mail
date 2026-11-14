@props(['muted' => false])
<div {{ $attributes->class(['min-w-0 rounded-xl border border-border', $muted ? 'bg-surface-muted' : 'bg-surface']) }}>{{ $slot }}</div>
