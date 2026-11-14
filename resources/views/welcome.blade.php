<x-ui.page title="Temp Mail SaaS">
    <main class="mx-auto flex min-h-screen max-w-2xl flex-col justify-center px-5 py-12 sm:px-8">
        <x-ui.card aria-labelledby="welcome-title" class="sm:p-10">
            <h1 id="welcome-title" class="text-heading">Temp Mail SaaS</h1>
            <p class="mt-3 max-w-prose text-foreground-muted">
                Preview the typography and appearance of the application.
            </p>

            <x-slot:footer>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <x-ui.badge class="font-mono">Pure Zinc 16</x-ui.badge>
                    <x-ui.appearance-select id="appearance-preview" />
                </div>
            </x-slot:footer>
        </x-ui.card>
    </main>
</x-ui.page>
