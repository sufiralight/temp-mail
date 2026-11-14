@props(['items'])
<nav aria-label="Breadcrumb" class="min-w-0">
    <ol class="flex min-w-0 items-center gap-2 text-small text-foreground-muted">
        @foreach ($items as $label => $route)
            <li class="flex min-w-0 items-center gap-2">
                @unless ($loop->first)<span aria-hidden="true">/</span>@endunless
                @if ($loop->last)
                    <span aria-current="page" class="truncate">{{ $label }}</span>
                @else
                    <a href="{{ route($route, absolute: false) }}" class="truncate underline-offset-4 hover:underline">{{ $label }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
