<x-layouts.app title="Opprett konto">
    <div class="min-h-screen bg-white dark:bg-zinc-800 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <a href="{{ route('welcome') }}" class="inline-block mb-6">
                    <img src="{{ asset('images/logo/logo-light.png') }}" alt="Konrad Office" class="h-12 w-auto mx-auto dark:hidden">
                    <img src="{{ asset('images/logo/logo-dark.png') }}" alt="Konrad Office" class="h-12 w-auto mx-auto hidden dark:block">
                </a>
                <flux:heading size="2xl" level="1" class="text-zinc-900 dark:text-white">
                    Opprett konto
                </flux:heading>
                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">
                    Kom i gang med Konrad i dag
                </flux:text>
            </div>

            <flux:card class="mt-8 bg-white dark:bg-zinc-900 shadow-xl border border-zinc-200 dark:border-zinc-700">
                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <div>
                        <flux:field>
                            <flux:label for="name">Fullt navn</flux:label>
                            <flux:input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                placeholder="Ditt fulle navn"
                                class="mt-1"
                            />
                            @error('name')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label for="email">E-postadresse</flux:label>
                            <flux:input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                placeholder="Din e-postadresse"
                                class="mt-1"
                            />
                            @error('email')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label for="password">Passord</flux:label>
                            <flux:input
                                id="password"
                                name="password"
                                type="password"
                                required
                                placeholder="Velg et sterkt passord"
                                class="mt-1"
                            />
                            @error('password')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label for="password_confirmation">Bekreft passord</flux:label>
                            <flux:input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                placeholder="Gjenta passordet"
                                class="mt-1"
                            />
                        </flux:field>
                    </div>

                    <div class="pt-4">
                        <flux:button type="submit" variant="primary" class="w-full">
                            Opprett konto
                        </flux:button>
                    </div>
                </form>

                <flux:separator class="my-6" />

                <div class="text-center">
                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                        Har du allerede en konto?
                        <flux:link href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                            Logg inn
                        </flux:link>
                    </flux:text>
                </div>
            </flux:card>
        </div>
    </div>
</x-layouts.app>
