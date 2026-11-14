@props(['title', 'description'])
<x-ui.page :title="$title.' · Temp Mail SaaS'">
    <main>
        <x-ui.container size="sm" class="py-8 sm:py-16">
            <x-ui.stack gap="lg">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <a href="/" class="text-label">Temp Mail SaaS</a>
                    <x-ui.appearance-select id="auth-appearance" />
                </div>
                <x-ui.card>
                    <x-slot:header>
                        <h1 class="text-heading">{{ $title }}</h1>
                        <p class="mt-2 text-small text-foreground-muted">{{ $description }}</p>
                    </x-slot:header>
                    <x-ui.stack>
                        @if (session('status'))
                            <x-ui.alert variant="info" live="polite">{{ session('status') }}</x-ui.alert>
                        @endif
                        {{ $slot }}
                    </x-ui.stack>
                    <x-slot:footer>{{ $footer }}</x-slot:footer>
                </x-ui.card>
            </x-ui.stack>
        </x-ui.container>
    </main>
</x-ui.page>
