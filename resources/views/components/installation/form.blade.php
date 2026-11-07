@props(['action' => null, 'submit', 'error' => null, 'csrfToken' => null, 'busyLabel' => null])

@if ($action)
    <form class="installer-form" method="post" action="{{ $action }}" @if ($busyLabel) data-busy-label="{{ $busyLabel }}" @endif>
        @if ($csrfToken)
            <input type="hidden" name="_token" value="{{ $csrfToken }}">
        @else
            @csrf
        @endif
@else
    <div class="installer-form">
@endif

    @if ($error)
        <x-installation.status tone="error">{{ $error }}</x-installation.status>
    @endif

    {{ $slot }}

    <div class="installer-form-actions">
        <button class="installer-primary" type="{{ $action ? 'submit' : 'button' }}" @disabled(! $action)>
            <span data-submit-label>{{ $submit }}</span>
            <x-installation.icon name="arrow" />
        </button>
    </div>

@if ($action)
    </form>
@else
    </div>
@endif
