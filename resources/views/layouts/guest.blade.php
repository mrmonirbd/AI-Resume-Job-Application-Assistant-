<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'AI Resume Assistant') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-[#f6f7f9] text-neutral-950 antialiased">
        <div class="min-h-screen">
            {{ $slot ?? '' }}
            @yield('content')
        </div>
        @livewireScripts
    </body>
</html>
