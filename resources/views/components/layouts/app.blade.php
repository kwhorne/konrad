<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

        <title>{{ $title ?? 'Page Title' }}</title>
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @fluxAppearance
    </head>
    <body>
        {{ $slot }}

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>

        <flux:toast />

        @fluxScripts
    </body>
</html>
