@props(['title', 'description', 'step' => null, 'focusTitle' => false])

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} | Temp Mail</title>
        <script src="{{ asset('js/installer.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('css/installer.css') }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    </head>
    <body class="installer">
        <header class="installer-header">
            <div class="installer-brand">
                <span class="installer-brand-mark"><x-installation.icon name="mail" /></span>
                <span>Temp Mail</span>
            </div>
            <x-installation.theme-toggle />
        </header>

        <main class="installer-main" aria-labelledby="installation-title">
            <div class="installer-card">
                @if ($step !== null)
                    <x-installation.steps :current="$step" />
                @else
                    <p class="installer-eyebrow">Workspace setup</p>
                @endif

                <div class="installer-intro">
                    <h1 id="installation-title" @if ($focusTitle) tabindex="-1" data-focus-on-load @endif>{{ $title }}</h1>
                    <p>{{ $description }}</p>
                </div>

                {{ $slot }}
            </div>
        </main>
    </body>
</html>
