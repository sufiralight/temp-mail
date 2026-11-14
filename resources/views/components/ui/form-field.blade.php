@props(['id', 'label' => null, 'description' => null, 'error' => null, 'required' => false])
<div {{ $attributes->class(['ui-field']) }}>
    @if (filled($label))
        <label for="{{ $id }}" class="text-label">
            {{ $label }} @if ($required)<span aria-hidden="true">*</span>@endif
        </label>
    @endif
    {{ $slot }}
    @if (filled($description))
        <p id="{{ $id }}-description" class="text-small text-foreground-muted">{{ $description }}</p>
    @endif
    @if (filled($error))
        <p id="{{ $id }}-error" class="flex gap-2 text-small">
            <x-ui.icon name="circle-alert" :size="16" class="text-error" />
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
