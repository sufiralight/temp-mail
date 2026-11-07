@props(['name'])

<svg {{ $attributes->class(['installer-icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="3" />
            <path d="m4 7 8 6 8-6" />
            @break
        @case('sun')
            <circle cx="12" cy="12" r="4" />
            <path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" />
            @break
        @case('moon')
            <path d="M20.7 14.2A8.8 8.8 0 0 1 9.8 3.3a9 9 0 1 0 10.9 10.9Z" />
            @break
        @case('arrow')
            <path d="M4 12h15m-6-6 6 6-6 6" />
            @break
        @case('check')
            <path d="m5 12 4 4L19 6" />
            @break
        @case('eye')
            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z" />
            <circle cx="12" cy="12" r="2.5" />
            @break
        @case('eye-off')
            <path d="m3 3 18 18M10 6.2a11.2 11.2 0 0 1 2-.2c6.5 0 10 6 10 6a14.1 14.1 0 0 1-3.2 3.5M6 7.8A14.6 14.6 0 0 0 2 12s3.5 6 10 6a11 11 0 0 0 4.1-.8" />
            @break
    @endswitch
</svg>
