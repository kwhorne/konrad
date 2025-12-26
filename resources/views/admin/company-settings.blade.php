<x-layouts.app title="Firmainnstillinger">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-admin-sidebar current="company-settings" />
        <x-admin-header current="company-settings" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-8">
                <flux:heading size="2xl" level="1" class="text-zinc-900 dark:text-white mb-2">
                    Firmainnstillinger
                </flux:heading>
                <flux:text class="text-lg text-zinc-600 dark:text-zinc-400">
                    Administrer firmaopplysninger som vises på tilbud, ordrer og fakturaer
                </flux:text>
            </div>

            <livewire:company-settings-manager />
        </flux:main>

    </div>
</x-layouts.app>
