@props(['current'])

<p class="installer-eyebrow">Step {{ $current }} of 2</p>
<ol class="installer-steps" aria-label="Installation progress">
    @foreach ([1 => 'Database', 2 => 'Admin account'] as $step => $label)
        <li @if ($step === $current) aria-current="step" @endif>
            <span class="installer-step-number" aria-hidden="true">
                @if ($step < $current)
                    <x-installation.icon name="check" />
                @else
                    0{{ $step }}
                @endif
            </span>
            {{ $label }}
            @if ($step < $current)
                <span class="installer-sr-only">(complete)</span>
            @endif
        </li>
    @endforeach
</ol>
