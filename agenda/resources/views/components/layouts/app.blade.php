@props(['title' => null]) 

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Judul tab: pakai $title kalau ada, fallback ke APP_NAME --}}
    <title>{{ $title ?? config('app.name', 'Agenda App') }}</title>

    {{-- Favicon / App icons --}}
    <link rel="icon" type="image/png" href="{{ asset('images/kominfopolos.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/kominfopolos.png') }}">
    <meta name="theme-color" content="#1d4ed8">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>

    @livewireStyles
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        {{-- <nav>...</nav> --}}
        <main>
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
    <script>
        // JS global
    </script>
</body>
</html>
