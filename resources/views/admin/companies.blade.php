<x-layouts.app title="Selskaper">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-admin-sidebar current="companies" />
        <x-admin-header current="companies" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="mb-8">
                <flux:heading size="2xl" level="1" class="text-zinc-900 dark:text-white mb-2">
                    Selskaper
                </flux:heading>
                <flux:text class="text-lg text-zinc-600 dark:text-zinc-400">
                    Oversikt over alle selskaper i systemet
                </flux:text>
            </div>

            <livewire:company-admin-manager />
        </flux:main>

    </div>
</x-layouts.app>
