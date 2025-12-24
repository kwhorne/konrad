<x-layouts.app title="Dashboard">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="dashboard" />
        <x-app-header current="dashboard" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-8">
                <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                    God {{ $greeting }}, {{ auth()->user()->name }}
                </flux:heading>
                <flux:text class="mt-2 text-base text-zinc-600 dark:text-zinc-400">
                    Velkommen til Konrad
                </flux:text>
            </div>

            <flux:separator variant="subtle" class="my-6" />

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-8">
                        <div class="flex items-start justify-between">
                            <div>
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-2">
                                    Kom i gang
                                </flux:heading>
                                <flux:text class="text-zinc-600 dark:text-zinc-400 mb-4">
                                    Utforsk funksjonene som er tilgjengelige for deg
                                </flux:text>
                            </div>
                            <flux:icon.building-2 class="h-10 w-10 text-indigo-600" />
                        </div>
                    </div>
                </flux:card>

                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-8">
                        <div class="flex items-start justify-between">
                            <div>
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-2">
                                    Profil
                                </flux:heading>
                                <flux:text class="text-zinc-600 dark:text-zinc-400 mb-4">
                                    Administrer kontoinnstillingene dine
                                </flux:text>
                                <flux:button href="{{ route('settings') }}" variant="ghost" size="sm">
                                    Gå til innstillinger
                                </flux:button>
                            </div>
                            <flux:icon.user-circle class="h-10 w-10 text-green-600" />
                        </div>
                    </div>
                </flux:card>

                @if(auth()->user()->is_admin)
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm hover:shadow-md transition-shadow border-2 border-indigo-200 dark:border-indigo-800">
                    <div class="p-8">
                        <div class="flex items-start justify-between">
                            <div>
                                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-2">
                                    Administrasjon
                                </flux:heading>
                                <flux:text class="text-zinc-600 dark:text-zinc-400 mb-4">
                                    Administrer brukere og systeminnstillinger
                                </flux:text>
                                <flux:button href="{{ route('admin.users') }}" variant="ghost" size="sm">
                                    Gå til admin
                                </flux:button>
                            </div>
                            <flux:icon.shield-check class="h-10 w-10 text-indigo-600" />
                        </div>
                    </div>
                </flux:card>
                @endif
            </div>

            <div class="mt-12">
                <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-6">
                    Funksjoner
                </flux:heading>
                
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm">
                    <div class="p-12 text-center">
                        <flux:icon.cube class="h-16 w-16 text-zinc-400 mx-auto mb-4" />
                        <flux:heading size="lg" level="3" class="text-zinc-900 dark:text-white mb-2">
                            Ingen funksjoner ennå
                        </flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                            Nye funksjoner vil bli lagt til her etter hvert som de utvikles
                        </flux:text>
                    </div>
                </flux:card>
            </div>
        </flux:main>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</x-layouts.app>
