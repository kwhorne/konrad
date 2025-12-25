<x-layouts.app title="Altinn-oversikt">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="altinn" />
        <x-app-header current="altinn" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <flux:icon.cloud-arrow-up class="w-7 h-7 text-white" />
                </div>
                <div>
                    <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                        Altinn
                    </flux:heading>
                    <flux:text class="mt-1 text-base text-zinc-600 dark:text-zinc-400">
                        Frister og elektronisk innsending
                    </flux:text>
                </div>
            </div>

            <livewire:altinn-dashboard />
        </flux:main>
    </div>
</x-layouts.app>
