@props(['title'])

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} | Temp Mail</title>
    </head>
    <body>
        <main aria-labelledby="installation-title">
            <h1 id="installation-title">{{ $title }}</h1>
            {{ $slot }}
        </main>
    </body>
</html>
