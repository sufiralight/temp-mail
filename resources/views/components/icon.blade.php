@props(['name'])
<svg {{ $attributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('sun') <circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/> @break
@case('moon') <path d="M20 14a8.5 8.5 0 0 1-10-10 8.5 8.5 0 1 0 10 10Z"/> @break
@case('monitor') <rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8m-4-4v4"/> @break
@case('mail') <rect x="3" y="5" width="18" height="14" rx="3"/><path d="m4 7 8 6 8-6"/> @break
@case('eye') <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/> @break
@case('check') <path d="m5 12 4 4L19 6"/> @break
@case('arrow') <path d="M5 12h14m-6-6 6 6-6 6"/> @break
@default <circle cx="12" cy="12" r="9"/><path d="M12 7v6m0 4h.01"/>
@endswitch
</svg>
