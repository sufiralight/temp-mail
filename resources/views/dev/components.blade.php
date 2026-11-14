<x-ui.page title="Component preview · Temp Mail SaaS">
    <main class="mx-auto grid max-w-5xl gap-6 px-5 py-10 sm:px-8">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <h1 class="text-heading">Component preview</h1>
            <x-ui.appearance-select id="preview-theme" />
        </header>

        <div class="grid items-start gap-6 md:grid-cols-2">
            <x-ui.card>
                <x-slot:header><h2 class="text-lg font-semibold">Form controls</h2></x-slot:header>
                <div class="grid gap-4">
                    <x-ui.input name="email" type="email" label="Email" value="invalid"
                        description="Use a complete email address." error="Enter a valid email address." required />
                    <x-ui.password-field name="password" label="Password" autocomplete="new-password" />
                    <x-ui.input name="reference" label="Reference" value="UI-001" readonly />
                    <x-ui.textarea name="notes" label="Notes" placeholder="Add a note" :rows="3" />
                    <x-ui.select name="region" label="Region" required>
                        <option value="">Choose a region</option>
                        <option value="eu">Europe</option>
                    </x-ui.select>
                    <x-ui.checkbox name="summary" label="Include summary" description="Adds the summary to the report." checked />
                    <fieldset class="grid gap-2">
                        <legend class="text-label">Delivery</legend>
                        <x-ui.radio name="delivery" value="daily" label="Daily" checked />
                        <x-ui.radio name="delivery" value="weekly" label="Weekly" />
                    </fieldset>
                </div>
            </x-ui.card>

            <div class="grid gap-6">
                <x-ui.card muted>
                    <x-slot:header><h2 class="text-lg font-semibold">Actions</h2></x-slot:header>
                    <div class="flex flex-wrap items-center gap-3">
                        <x-ui.button leading-icon="plus" size="lg">Add item</x-ui.button>
                        <x-ui.button variant="secondary" trailing-icon="arrow-right">Continue</x-ui.button>
                        <x-ui.button variant="ghost" size="sm">Skip</x-ui.button>
                        <x-ui.button variant="danger" leading-icon="trash-2">Remove</x-ui.button>
                        <x-ui.icon-button icon="search" label="Search" variant="secondary" />
                        <x-ui.button loading>Saving</x-ui.button>
                        <x-ui.button disabled>Unavailable</x-ui.button>
                    </div>
                    <x-slot:footer>
                        <x-ui.button variant="secondary" data-modal-open="preview-dialog" aria-haspopup="dialog" disabled>Open dialog</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>

                <x-ui.card>
                    <x-slot:header><h2 class="text-lg font-semibold">Feedback</h2></x-slot:header>
                    <div class="grid gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.badge>Draft</x-ui.badge>
                            <x-ui.badge variant="info">Scheduled</x-ui.badge>
                            <x-ui.badge variant="success">Ready</x-ui.badge>
                            <x-ui.badge variant="warning">Review</x-ui.badge>
                            <x-ui.badge variant="error">Failed</x-ui.badge>
                        </div>
                        <x-ui.alert variant="info">Your changes are local to this preview.</x-ui.alert>
                        <x-ui.alert variant="success">Preferences applied.</x-ui.alert>
                        <x-ui.alert variant="warning">Review the highlighted field.</x-ui.alert>
                        <x-ui.alert variant="error">The email address needs correction.</x-ui.alert>
                        <p class="flex items-center gap-2 text-small"><x-ui.spinner /> Loading preview</p>
                    </div>
                </x-ui.card>
            </div>
        </div>

        <x-ui.modal id="preview-dialog" title="Preview dialog" dismiss-on-backdrop>
            <p>Press Escape to close the dialog and return to its trigger.</p>
            <x-slot:footer><x-ui.button data-modal-close>Done</x-ui.button></x-slot:footer>
        </x-ui.modal>
    </main>
</x-ui.page>
