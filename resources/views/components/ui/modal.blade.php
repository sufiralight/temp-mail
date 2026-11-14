@props(['id', 'title', 'dismissOnBackdrop' => false])
<dialog {{ $attributes->except(['open', 'aria-labelledby'])->class(['ui-modal']) }} id="{{ $id }}"
    aria-labelledby="{{ $id }}-title" data-ui-modal @if ($dismissOnBackdrop) data-dismiss-backdrop @endif>
    <div class="flex items-start justify-between gap-4">
        <h2 id="{{ $id }}-title" class="text-lg font-semibold">{{ $title }}</h2>
        <x-ui.icon-button icon="x" :label="__('Close dialog')" variant="ghost" size="sm" data-modal-close autofocus />
    </div>
    <div class="mt-4">{{ $slot }}</div>
    @isset($footer)<div class="mt-6 flex flex-wrap justify-end gap-3">{{ $footer }}</div>@endisset
</dialog>
