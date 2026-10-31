@props(['title', 'step'])
<x-app-layout :title="$title">
    <x-progress :step="$step" />
    <p class="eyebrow">{{ __('install.step', ['number' => str_pad($step, 2, '0', STR_PAD_LEFT)]) }}</p>
    <h1>{{ $title }}</h1>
    {{ $slot }}
</x-app-layout>
