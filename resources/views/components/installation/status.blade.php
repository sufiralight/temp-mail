@props(['tone' => 'info'])

<div {{ $attributes->class(['installer-status', 'installer-status--'.$tone]) }} role="{{ $tone === 'error' ? 'alert' : 'status' }}">
    {{ $slot }}
</div>
