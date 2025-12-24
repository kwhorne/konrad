<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-flux-appearance>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Konrad - Innovative digitale løsninger' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @fluxAppearance
</head>
<body class="min-h-screen bg-white dark:bg-zinc-900 font-sans antialiased">
    <!-- Navigation -->
    <nav class="border-b border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('welcome') }}" class="flex items-center space-x-2">
                        <flux:icon.building-2 class="w-8 h-8 text-indigo-600 dark:text-indigo-400" />
                        <span class="text-xl font-bold text-zinc-900 dark:text-white">Konrad</span>
                    </a>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                        Tjenester
                    </a>
                    <a href="#components" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                        Teknologi
                    </a>
                </div>
                
                <!-- Auth Links -->
                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" variant="subtle" square class="group" aria-label="Toggle dark mode">
                        <flux:icon.sun x-show="$flux.dark" variant="mini" class="text-zinc-500 dark:text-white" />
                        <flux:icon.moon x-show="! $flux.dark" variant="mini" class="text-zinc-500 dark:text-white" />
                    </flux:button>
                    
                    @auth
                        <flux:button href="{{ route('dashboard') }}" variant="ghost" size="sm">
                            Dashboard
                        </flux:button>
                    @else
                        <flux:button href="{{ route('login') }}" variant="ghost" size="sm">
                            Logg inn
                        </flux:button>
                        <flux:button href="{{ route('register') }}" variant="primary" size="sm">
                            Kom i gang
                        </flux:button>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main>
        {{ $slot }}
    </main>
    
    <!-- Footer -->
    @include('components.footer')
    
    @fluxScripts
</body>
</html>
