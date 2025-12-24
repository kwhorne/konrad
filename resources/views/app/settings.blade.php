<x-layouts.app title="Innstillinger">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="settings" />
        <x-app-header current="settings" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <!-- Header Section -->
            <div class="mb-8">
                <flux:heading size="2xl" level="1" class="text-zinc-900 dark:text-white mb-2">
                    Innstillinger
                </flux:heading>
                <flux:text class="text-lg text-zinc-600 dark:text-zinc-400">
                    Administrer kontoen din, preferanser og applikasjonsinnstillinger
                </flux:text>
            </div>

            <!-- Settings Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Left Column - Main Settings -->
                <div class="lg:col-span-3 space-y-8">
                    <!-- Profile Section -->
                    <section>
                        <div class="mb-6">
                            <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-2">
                                Profilinformasjon
                            </flux:heading>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                Oppdater profilinformasjon og e-postadresse.
                            </flux:text>
                        </div>
                        
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                            <div class="p-8">
                                <!-- Profile Avatar -->
                                <div class="flex items-center mb-8">
                                    <div class="h-20 w-20 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mr-6">
                                        <span class="text-2xl font-bold text-white">
                                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-1">
                                            {{ auth()->user()->name }}
                                        </flux:heading>
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ auth()->user()->email }}
                                        </flux:text>
                                        @if(auth()->user()->is_admin)
                                            <flux:badge variant="primary" class="mt-2">Administrator</flux:badge>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <flux:field>
                                            <flux:label for="name" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                                Fullt navn
                                            </flux:label>
                                            <flux:input 
                                                id="name" 
                                                name="name" 
                                                type="text" 
                                                value="{{ auth()->user()->name }}" 
                                                class="mt-2"
                                                readonly
                                            />
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                                Kontakt support for å endre navn
                                            </flux:text>
                                        </flux:field>
                                    </div>
                                    
                                    <div>
                                        <flux:field>
                                            <flux:label for="email" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                                E-postadresse
                                            </flux:label>
                                            <flux:input 
                                                id="email" 
                                                name="email" 
                                                type="email" 
                                                value="{{ auth()->user()->email }}" 
                                                class="mt-2"
                                                readonly
                                            />
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                                Kontakt support for å endre e-post
                                            </flux:text>
                                        </flux:field>
                                    </div>
                                </div>
                            </div>
                        </flux:card>
                    </section>

                    <!-- Security Section -->
                    <section>
                        <div class="mb-6">
                            <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-2">
                                Sikkerhet
                            </flux:heading>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                Sørg for at kontoen din bruker et langt, tilfeldig passord for å holde den sikker.
                            </flux:text>
                        </div>
                        
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                            <div class="p-8">
                                @if(session('success'))
                                    <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl flex items-center">
                                        <flux:icon.check-circle class="h-5 w-5 text-green-600 dark:text-green-400 mr-3" />
                                        <flux:text class="text-green-800 dark:text-green-200 font-medium">
                                            {{ session('success') }}
                                        </flux:text>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('settings.password') }}" class="space-y-6">
                                    @csrf
                                    
                                    <div>
                                        <flux:field>
                                            <flux:label for="current_password" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                                Nåværende passord
                                            </flux:label>
                                            <flux:input 
                                                id="current_password" 
                                                name="current_password" 
                                                type="password" 
                                                required
                                                placeholder="Skriv inn nåværende passord"
                                                class="mt-2"
                                            />
                                            @error('current_password')
                                                <flux:error class="mt-1">{{ $message }}</flux:error>
                                            @enderror
                                        </flux:field>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <flux:field>
                                                <flux:label for="password" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                                    Nytt passord
                                                </flux:label>
                                                <flux:input 
                                                    id="password" 
                                                    name="password" 
                                                    type="password" 
                                                    required
                                                    placeholder="Skriv inn nytt passord"
                                                    class="mt-2"
                                                />
                                                @error('password')
                                                    <flux:error class="mt-1">{{ $message }}</flux:error>
                                                @enderror
                                            </flux:field>
                                        </div>
                                        
                                        <div>
                                            <flux:field>
                                                <flux:label for="password_confirmation" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                                    Bekreft passord
                                                </flux:label>
                                                <flux:input 
                                                    id="password_confirmation" 
                                                    name="password_confirmation" 
                                                    type="password" 
                                                    required
                                                    placeholder="Bekreft nytt passord"
                                                    class="mt-2"
                                                />
                                            </flux:field>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                            Bruk et sterkt passord med minst 8 tegn
                                        </flux:text>
                                        <flux:button type="submit" variant="primary" class="px-6">
                                            <flux:icon.key class="h-4 w-4 mr-2" />
                                            Oppdater passord
                                        </flux:button>
                                    </div>
                                </form>
                            </div>
                        </flux:card>
                    </section>
                </div>

                <!-- Right Column - Preferences -->
                <div class="space-y-8">
                    <!-- Appearance Settings -->
                    <section>
                        <div class="mb-6">
                            <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-2">
                                Utseende
                            </flux:heading>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                Tilpass hvordan applikasjonen ser ut på enheten din.
                            </flux:text>
                        </div>
                        
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                            <div class="p-6">
                                <div class="mb-4">
                                    <flux:label class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-3 block">
                                        Tema
                                    </flux:label>
                                    <flux:text class="text-xs text-zinc-600 dark:text-zinc-400 mb-4">
                                        Velg tema eller synkroniser med systeminnstillingene dine.
                                    </flux:text>
                                </div>
                                
                                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                                    <flux:radio value="light" icon="sun">Lys</flux:radio>
                                    <flux:radio value="dark" icon="moon">Mørk</flux:radio>
                                    <flux:radio value="system" icon="computer-desktop">Auto</flux:radio>
                                </flux:radio.group>
                            </div>
                        </flux:card>
                    </section>

                    <!-- Notifications Settings -->
                    <section>
                        <div class="mb-6">
                            <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-2">
                                Varsler
                            </flux:heading>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                Administrer hvordan du mottar varsler.
                            </flux:text>
                        </div>
                        
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                            <div class="p-6 space-y-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <flux:text class="text-sm font-medium text-zinc-900 dark:text-white">
                                            E-postvarsler
                                        </flux:text>
                                        <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">
                                            Motta oppdateringer via e-post
                                        </flux:text>
                                    </div>
                                    <flux:checkbox id="email_notifications" name="email_notifications" checked />
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div>
                                        <flux:text class="text-sm font-medium text-zinc-900 dark:text-white">
                                            Push-varsler
                                        </flux:text>
                                        <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">
                                            Nettleser push-varsler
                                        </flux:text>
                                    </div>
                                    <flux:checkbox id="push_notifications" name="push_notifications" />
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div>
                                        <flux:text class="text-sm font-medium text-zinc-900 dark:text-white">
                                            Markedsførings-e-post
                                        </flux:text>
                                        <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">
                                            Produktoppdateringer og tips
                                        </flux:text>
                                    </div>
                                    <flux:checkbox id="marketing_emails" name="marketing_emails" />
                                </div>
                            </div>
                        </flux:card>
                    </section>

                    <!-- Account Actions -->
                    <section>
                        <div class="mb-6">
                            <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-2">
                                Kontohandlinger
                            </flux:heading>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                Administrer kontoinnstillingene dine.
                            </flux:text>
                        </div>
                        
                        <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                            <div class="p-6 space-y-4">
                                <div class="text-center">
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
                                        Medlem siden {{ auth()->user()->created_at->format('M Y') }}
                                    </flux:text>
                                    
                                    <flux:button variant="ghost" class="w-full text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <flux:icon.exclamation-triangle class="h-4 w-4 mr-2" />
                                        Slett konto
                                    </flux:button>
                                </div>
                            </div>
                        </flux:card>
                    </section>
                </div>
            </div>
        </flux:main>

        <!-- Hidden logout form -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</x-layouts.app>
