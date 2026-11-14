@props(['size' => 'md', 'label' => null])
<span {{ $attributes->class(['ui-spinner-status']) }} @if (filled($label)) role="status" @else aria-hidden="true" @endif>
    <span class="ui-spinner" data-size="{{ $size }}" aria-hidden="true"></span>
    @if (filled($label))<span class="sr-only">{{ $label }}</span>@endif
</span>
