@props(['title', 'description' => null, 'context' => 'account', 'breadcrumbs' => []])
@php($user = auth()->user())
<x-ui.page :title="$title.' · Temp Mail SaaS'">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-20 focus:m-3 focus:rounded-md focus:bg-primary focus:p-3 focus:text-primary-foreground">Skip to content</a>
    <div class="min-h-screen lg:grid lg:grid-cols-[15rem_minmax(0,1fr)]">
        <aside class="hidden border-e border-border bg-surface lg:block">
            <div class="sticky top-0 grid gap-6 p-4">
                <a href="{{ route($context === 'admin' ? 'admin.overview' : 'account', absolute: false) }}" class="px-3 pt-2 text-base font-semibold">Temp Mail SaaS</a>
                <x-layouts.navigation :$context />
            </div>
        </aside>

        <div class="min-w-0">
            <header class="border-b border-border bg-surface">
                <x-ui.container size="xl">
                    <div class="flex min-h-16 flex-wrap items-center gap-2 py-2">
                        <x-ui.icon-button icon="menu" label="Open navigation" variant="secondary" class="lg:hidden"
                            data-modal-open="shell-navigation" aria-controls="shell-navigation" aria-haspopup="dialog" disabled />
                        <span class="me-auto min-w-0 text-label">{{ $context === 'admin' ? 'Administration' : 'Account' }}</span>
                        <div data-shell-theme hidden role="group" aria-label="Appearance" class="flex rounded-md border border-border p-0.5">
                            @foreach (['light' => 'sun', 'dark' => 'moon', 'system' => 'monitor'] as $mode => $icon)
                                <x-ui.icon-button :$icon :label="ucfirst($mode).' appearance'" :title="ucfirst($mode)"
                                    variant="ghost" size="sm" :data-theme-choice="$mode" aria-pressed="false" />
                            @endforeach
                        </div>
                        <details data-account-menu class="relative">
                            <summary class="ui-button cursor-pointer list-none" data-variant="secondary">
                                <x-ui.icon name="user" :size="16" />
                                <span class="sr-only">Account menu</span>
                            </summary>
                            <div class="absolute end-0 z-10 mt-2 grid w-56 max-w-[calc(100vw-2rem)] gap-2 rounded-xl border border-border bg-surface p-3 shadow-sm">
                                <div class="min-w-0 wrap-anywhere">
                                    <p class="text-label">{{ $user->name }}</p>
                                    <p class="text-small text-foreground-muted">{{ $user->email }}</p>
                                </div>
                                <x-ui.divider decorative />
                                <a class="ui-button" data-variant="ghost" href="{{ route('account.settings', absolute: false) }}">Account Settings</a>
                                @can('super-admin')
                                    @if ($context === 'admin')
                                        <a class="ui-button" data-variant="ghost" href="{{ route('account', absolute: false) }}">Customer application</a>
                                    @else
                                        <a class="ui-button" data-variant="ghost" href="{{ route('admin.overview', absolute: false) }}">Administration</a>
                                    @endif
                                @endcan
                                <form method="post" action="{{ route('logout', absolute: false) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="ghost" full-width>Sign Out</x-ui.button>
                                </form>
                            </div>
                        </details>
                    </div>
                </x-ui.container>
            </header>
            <main id="main-content" tabindex="-1">
                <x-ui.container size="xl" class="py-8 sm:py-10">
                    <x-ui.stack gap="lg">
                        <x-ui.page-header :$title :$description>
                            <x-slot:breadcrumbs>
                                @if ($breadcrumbs)<x-layouts.breadcrumbs :items="$breadcrumbs" />@endif
                            </x-slot:breadcrumbs>
                            <x-slot:actions>{{ $actions ?? '' }}</x-slot:actions>
                        </x-ui.page-header>
                        @if (session('status'))<x-ui.alert variant="success" live="polite">{{ session('status') }}</x-ui.alert>@endif
                        {{ $slot }}
                    </x-ui.stack>
                </x-ui.container>
            </main>
        </div>
    </div>
    <x-ui.modal id="shell-navigation" title="Navigation" class="shell-drawer" dismiss-on-backdrop>
        <x-layouts.navigation :$context />
    </x-ui.modal>
</x-ui.page>
