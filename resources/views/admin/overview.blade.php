<x-layouts.app-shell context="admin" title="Overview" description="Review the installation and the current administrator.">
    <x-ui.grid>
        <x-ui.card>
            <x-slot:header><h2 class="text-lg font-semibold">Installation</h2></x-slot:header>
            <p><x-ui.badge variant="success">Complete</x-ui.badge></p>
            <x-slot:footer><a href="{{ route('admin.system', absolute: false) }}" class="ui-button" data-variant="secondary">System Information</a></x-slot:footer>
        </x-ui.card>
        <x-ui.card>
            <x-slot:header><h2 class="text-lg font-semibold">Current administrator</h2></x-slot:header>
            <div class="wrap-anywhere">
                <p class="text-label">{{ auth()->user()->name }}</p>
                <p class="text-small text-foreground-muted">{{ auth()->user()->email }}</p>
            </div>
        </x-ui.card>
    </x-ui.grid>
</x-layouts.app-shell>
