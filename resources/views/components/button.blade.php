@props(['variant' => 'primary', 'href' => null, 'type' => 'submit'])
@if($href)
<a href="{{ $href }}" {{ $attributes->class(['button', 'button-secondary' => $variant === 'secondary']) }}>{{ $slot }}</a>
@else
<button type="{{ $type }}" {{ $attributes->class(['button', 'button-secondary' => $variant === 'secondary']) }}>{{ $slot }}</button>
@endif
