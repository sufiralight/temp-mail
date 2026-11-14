@props(['orientation' => 'horizontal', 'decorative' => false])
@php($vertical = $orientation === 'vertical')
<hr {{ $attributes->except(['aria-hidden', 'aria-orientation', 'role'])->class([$vertical ? 'h-auto min-h-4 self-stretch border-0 border-s border-border' : 'w-full border-0 border-t border-border']) }}
    @if ($decorative) aria-hidden="true" @elseif ($vertical) role="separator" aria-orientation="vertical" @endif>
