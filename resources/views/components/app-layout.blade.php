@props(['title'])
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} — {{ __('install.brand') }}</title>
    <script nonce="{{ Illuminate\Support\Facades\Vite::cspNonce() }}">{!! file_get_contents(resource_path('js/theme-bootstrap.js')) !!}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="frame">
    <a class="skip" href="#main">{{ __('install.skip') }}</a>
    <header class="topbar">
        <span class="brand"><span class="brand-mark"><x-icon name="mail" /></span>{{ __('install.brand') }}</span>
        <x-theme-switcher />
    </header>
    <main id="main" class="workspace" tabindex="-1">{{ $slot }}</main>
    <footer class="footer meta">{{ __('install.footer') }}</footer>
</div>
</body>
</html>
