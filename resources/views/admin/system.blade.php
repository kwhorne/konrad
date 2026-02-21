<x-layouts.app title="Admin - System">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-admin-sidebar current="system" />
        <x-admin-header current="system" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">Systemadministrasjon</flux:heading>
            <flux:text class="mb-6 mt-2 text-base text-zinc-600 dark:text-zinc-400">Administrer systeminnstillinger, vedlikehold og konfigurasjon</flux:text>
            <flux:separator variant="subtle" />

            {{-- System Information --}}
            <div class="mt-8">
                <flux:card class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="p-6">
                        <flux:heading size="lg" level="2" class="mb-4">Systeminformasjon</flux:heading>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">
                                    Laravel versjon
                                </flux:text>
                                <flux:text class="text-zinc-900 dark:text-white">
                                    {{ app()->version() }}
                                </flux:text>
                            </div>

                            <div>
                                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">
                                    PHP versjon
                                </flux:text>
                                <flux:text class="text-zinc-900 dark:text-white">
                                    {{ PHP_VERSION }}
                                </flux:text>
                            </div>

                            <div>
                                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">
                                    Miljø
                                </flux:text>
                                <flux:badge variant="{{ app()->environment() === 'production' ? 'danger' : 'warning' }}">
                                    {{ ucfirst(app()->environment()) }}
                                </flux:badge>
                            </div>

                            <div>
                                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">
                                    Debug modus
                                </flux:text>
                                <flux:badge variant="{{ config('app.debug') ? 'warning' : 'success' }}">
                                    {{ config('app.debug') ? 'Aktivert' : 'Deaktivert' }}
                                </flux:badge>
                            </div>
                        </div>
                    </div>
                </flux:card>
            </div>

            {{-- System Tools (Livewire) --}}
            <div class="mt-8">
                <livewire:admin.system-manager />
            </div>
        </flux:main>
    </div>
</x-layouts.app>
