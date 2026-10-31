@props(['error' => false])
<div {{ $attributes->class(['notice', 'notice-error' => $error]) }} role="{{ $error ? 'alert' : 'status' }}">{{ $slot }}</div>
