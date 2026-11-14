@props(['context'])
<nav aria-label="{{ $context === 'admin' ? 'Administration' : 'Account' }}">
    <ul class="grid gap-1">
        @foreach (config('navigation.'.$context, []) as $item)
            @php($active = request()->routeIs($item['route']))
            <li>
                <a href="{{ route($item['route'], absolute: false) }}"
                    @class(['flex min-h-11 items-center gap-3 rounded-md px-3 text-small', 'bg-selected font-semibold' => $active, 'text-foreground-muted hover:bg-hover' => ! $active])
                    @if ($active) aria-current="page" @endif>
                    <x-ui.icon :name="$item['icon']" :size="16" />
                    {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
