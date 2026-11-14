<x-layouts.app-shell context="admin" title="System" description="View the application runtime and installation status."
    :breadcrumbs="['Overview' => 'admin.overview', 'System' => null]">
    <x-ui.card>
        <x-slot:header><h2 class="text-lg font-semibold">Runtime information</h2></x-slot:header>
        <dl class="grid gap-4 sm:grid-cols-2">
            @foreach (['Laravel' => app()->version(), 'PHP' => PHP_VERSION, 'Installation' => 'Complete', 'Environment' => app()->environment()] as $label => $value)
                <div class="wrap-anywhere"><dt class="text-label">{{ $label }}</dt><dd class="font-mono text-technical text-foreground-muted">{{ $value }}</dd></div>
            @endforeach
        </dl>
        <x-slot:footer><p class="text-small text-foreground-muted">The shell uses PHP-rendered pages and precompiled assets.</p></x-slot:footer>
    </x-ui.card>
</x-layouts.app-shell>
