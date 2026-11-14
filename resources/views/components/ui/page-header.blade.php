@props(['title', 'description' => null])
<header {{ $attributes->class(['grid min-w-0 gap-3']) }}>
    {{ $breadcrumbs ?? '' }}
    <x-ui.stack direction="responsive" justify="between" wrap class="sm:items-end">
        <div class="min-w-0 wrap-anywhere">
            <h1 class="text-heading">{{ $title }}</h1>
            @if (filled($description))<p class="mt-2 max-w-prose text-foreground-muted">{{ $description }}</p>@endif
        </div>
        @isset($actions)
            <x-ui.stack direction="horizontal" gap="sm" align="center" wrap>{{ $actions }}</x-ui.stack>
        @endisset
    </x-ui.stack>
</header>
