<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Rezumax</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=rezumax">
        <link rel="icon" type="image/svg+xml" sizes="any" href="{{ asset('images/rezumax-tab.svg') }}?v=rezumax">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-cream text-ink antialiased">
        <div class="min-h-screen">
            {{ $slot ?? '' }}
            @yield('content')
        </div>
        @livewireScriptConfig
    </body>
</html>
