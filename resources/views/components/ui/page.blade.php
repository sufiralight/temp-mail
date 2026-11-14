@props(['title'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <script>{!! file_get_contents(resource_path('js/appearance.js')) !!}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body {{ $attributes->class(['bg-canvas font-sans text-body text-foreground']) }}>
    {{ $slot }}
</body>
</html>
