<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Temp Mail SaaS</title>

    <script>{!! file_get_contents(resource_path('js/appearance.js')) !!}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-canvas font-sans text-body text-foreground">
    <main class="mx-auto flex min-h-screen max-w-2xl flex-col justify-center px-5 py-12 sm:px-8">
        <section aria-labelledby="welcome-title" class="rounded-2xl border border-border bg-surface p-6 sm:p-10">
            <h1 id="welcome-title" class="text-heading">Temp Mail SaaS</h1>
            <p class="mt-3 max-w-prose text-foreground-muted">
                Preview the typography and appearance of the application.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-t border-border pt-6">
                <p class="rounded-md bg-surface-muted px-3 py-2 font-mono text-technical">Pure Zinc 16</p>

                <div hidden>
                    <label for="appearance-preview" class="block text-label">Appearance</label>
                    <select id="appearance-preview" class="mt-2 min-h-11 rounded-md bg-primary px-3 text-small text-primary-foreground">
                        <option value="system">System</option>
                        <option value="light">Light</option>
                        <option value="dark">Dark</option>
                    </select>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
