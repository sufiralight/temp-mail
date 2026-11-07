@props(['action' => null, 'submit', 'pendingNote' => null, 'error' => null])

@if ($action)
    <form class="installer-form" method="post" action="{{ $action }}">
        @csrf
@else
    <div class="installer-form">
@endif

    @if ($error)
        <x-installation.status tone="error">{{ $error }}</x-installation.status>
    @endif

    {{ $slot }}

    <div class="installer-form-actions">
        <button class="installer-primary" type="{{ $action ? 'submit' : 'button' }}" @disabled(! $action)>
            {{ $submit }}
            <x-installation.icon name="arrow" />
        </button>
        @if (! $action && $pendingNote)
            <p class="installer-hint">{{ $pendingNote }}</p>
        @endif
    </div>

@if ($action)
    </form>
@else
    </div>
@endif
