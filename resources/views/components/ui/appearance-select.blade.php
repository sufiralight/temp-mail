@props(['id'])
<div hidden>
    <x-ui.select :$id :label="__('Appearance')" data-appearance>
        <option value="system">{{ __('System') }}</option>
        <option value="light">{{ __('Light') }}</option>
        <option value="dark">{{ __('Dark') }}</option>
    </x-ui.select>
</div>
