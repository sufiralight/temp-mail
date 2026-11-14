<x-ui.page title="Component preview · Temp Mail SaaS">
    <main>
        <x-ui.container class="py-8 sm:py-12">
            <x-ui.page-header title="Component preview" description="Verify layouts, form controls, and feedback in each appearance.">
                <x-slot:actions><x-ui.appearance-select id="preview-theme" /></x-slot:actions>
            </x-ui.page-header>

            <x-ui.grid align="start" class="mt-8">
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
                        <x-ui.stack direction="horizontal" align="center" gap="sm" wrap>
                            <x-ui.button leading-icon="plus" size="lg">Add item</x-ui.button>
                            <x-ui.button variant="secondary" trailing-icon="arrow-right">Continue</x-ui.button>
                            <x-ui.button variant="ghost" size="sm">Skip</x-ui.button>
                            <x-ui.button variant="danger" leading-icon="trash-2">Remove</x-ui.button>
                            <x-ui.icon-button icon="search" label="Search" variant="secondary" />
                            <x-ui.button loading>Saving</x-ui.button>
                            <x-ui.button disabled>Unavailable</x-ui.button>
                        </x-ui.stack>
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
            </x-ui.grid>

            <x-ui.section aria-labelledby="layout-heading" spacing="lg">
                <x-slot:heading><h2 id="layout-heading" class="text-lg font-semibold">Layout and content states</h2></x-slot:heading>
                <x-slot:description>Containers and grids adapt to the available width.</x-slot:description>
                <x-ui.grid>
                    <x-ui.surface>
                        <x-ui.empty-state title="No results" description="Try another search to find a match." icon="search" heading-level="3">
                            <x-slot:primary><x-ui.button>Clear search</x-ui.button></x-slot:primary>
                            <x-slot:secondary><x-ui.button variant="ghost">Edit filters</x-ui.button></x-slot:secondary>
                        </x-ui.empty-state>
                    </x-ui.surface>
                    <x-ui.surface muted class="p-5 sm:p-6">
                        <h3 class="text-label">Loading results</h3>
                        <x-ui.divider decorative class="my-4" />
                        <x-ui.stack aria-busy="true">
                            <x-ui.sr-only>Results are loading.</x-ui.sr-only>
                            <x-ui.stack direction="horizontal" align="center">
                                <x-ui.skeleton shape="circle" size="sm" class="shrink-0" />
                                <x-ui.skeleton />
                            </x-ui.stack>
                            <x-ui.skeleton shape="rectangle" />
                        </x-ui.stack>
                    </x-ui.surface>
                </x-ui.grid>
                <x-ui.container size="sm" class="mt-6">
                    <x-ui.surface class="p-4 text-center text-small text-foreground-muted">Narrow content container</x-ui.surface>
                </x-ui.container>
            </x-ui.section>

            <x-ui.modal id="preview-dialog" title="Preview dialog" dismiss-on-backdrop>
                <p>Press Escape to close the dialog and return to its trigger.</p>
                <x-slot:footer><x-ui.button data-modal-close>Done</x-ui.button></x-slot:footer>
            </x-ui.modal>
        </x-ui.container>
    </main>
</x-ui.page>
