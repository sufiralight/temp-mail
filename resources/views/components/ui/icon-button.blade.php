@props(['icon', 'label', 'loading' => false])
@php
    if (blank($label)) {
        throw new InvalidArgumentException('An icon button requires an accessible label.');
    }
@endphp
<x-ui.button :attributes="$attributes->except(['aria-label', 'aria-labelledby'])" :aria-label="$label" :leading-icon="$icon" :loading="$loading" icon-only />
