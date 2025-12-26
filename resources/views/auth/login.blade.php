<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <title>Logg inn - Konrad Office</title>
    <meta name="description" content="Logg inn på Konrad Office - ditt komplette forretningssystem">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-white dark:bg-zinc-900 antialiased">
    <div class="flex min-h-screen">
        {{-- Left side - Logo --}}
        <div class="flex-1 p-4 max-lg:hidden">
            <div class="relative rounded-2xl h-full w-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center overflow-hidden">
                <img src="{{ asset('images/logo/logo-dark.png') }}" alt="Konrad Office" class="max-w-md xl:max-w-lg">
            </div>
        </div>

        {{-- Right side - Login form --}}
        <div class="flex-1 flex justify-center items-center p-8">
            <div class="w-full max-w-sm space-y-8">
                {{-- Mobile logo --}}
                <div class="flex justify-center lg:hidden">
                    <a href="{{ route('welcome') }}" class="flex items-center gap-3">
                        <svg class="h-8 w-8" viewBox="0 0 307 265" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M 0.0,139.5 L 0.5,120.0 C 1.0,100.5 2.0,61.5 13.7,41.0 C 25.3,20.5 47.7,18.5 69.2,19.0 C 90.7,19.5 111.3,22.5 132.0,26.0 L 152.7,29.5 L 152.8,66.5 C 152.8,86.8 152.8,127.3 152.8,147.7 L 152.8,188.0 L 132.2,188.0 C 111.5,188.0 70.0,188.0 49.0,188.0 C 28.0,188.0 27.5,188.0 18.3,174.0 C 9.2,160.0 0.8,132.0 0.4,118.0 C 0.0,104.0 0.0,104.0 0.0,139.5 Z" class="fill-[#457ba7] dark:fill-[#6b9bc4]" fill-rule="evenodd"/>
                            <path d="M 152.7,29.5 L 173.3,33.0 C 194.0,36.5 235.3,43.5 256.0,70.0 C 276.7,96.5 277.0,142.5 276.8,165.0 L 276.5,188.0 L 255.5,188.0 C 234.5,188.0 192.5,188.0 171.5,188.0 L 150.5,188.0 L 150.8,147.7 C 151.2,107.3 152.0,86.8 152.3,66.5 C 152.5,46.2 152.5,46.0 152.7,29.5 Z" class="fill-[#87c8b8] dark:fill-[#a8ddd0]" fill-rule="evenodd"/>
                            <path d="M 0.0,139.5 C 0.0,104.0 0.0,104.0 0.4,118.0 C 0.8,132.0 9.2,160.0 18.3,174.0 C 27.5,188.0 28.0,188.0 49.0,188.0 C 70.0,188.0 111.5,188.0 132.2,188.0 L 152.8,188.0 L 152.8,210.0 C 152.8,222.0 152.5,244.0 152.2,258.5 C 151.8,273.0 151.5,282.5 120.0,275.0 C 88.5,267.5 25.8,243.0 12.0,221.8 C -1.8,200.7 0.2,183.0 0.8,165.2 C 1.5,147.5 0.0,139.5 0.0,139.5 Z" class="fill-[#f2a35a] dark:fill-[#f5b97a]" fill-rule="evenodd"/>
                        </svg>
                        <span class="text-xl font-bold text-zinc-900 dark:text-white">Konrad Office</span>
                    </a>
                </div>

                <div class="text-center">
                    <flux:heading size="xl">Velkommen tilbake</flux:heading>
                    <flux:subheading class="mt-2">Logg inn for å fortsette</flux:subheading>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <flux:input
                        name="email"
                        type="email"
                        label="E-postadresse"
                        value="{{ old('email') }}"
                        placeholder="din@epost.no"
                        required
                        autofocus
                    />
                    @error('email')
                        <flux:text class="text-red-500 text-sm">{{ $message }}</flux:text>
                    @enderror

                    <flux:field>
                        <div class="flex justify-between mb-2">
                            <flux:label>Passord</flux:label>
                            <flux:link href="#" variant="subtle" class="text-sm">Glemt passord?</flux:link>
                        </div>
                        <flux:input
                            name="password"
                            type="password"
                            placeholder="Ditt passord"
                            required
                        />
                        @error('password')
                            <flux:text class="text-red-500 text-sm">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    <flux:checkbox name="remember" label="Husk meg i 30 dager" />

                    <flux:button type="submit" variant="primary" class="w-full">
                        Logg inn
                    </flux:button>
                </form>

                <flux:subheading class="text-center">
                    Trenger du tilgang? <flux:link href="mailto:post@konradoffice.no">Kontakt oss</flux:link>
                </flux:subheading>
            </div>
        </div>
    </div>

    @fluxScripts
</body>
</html>
