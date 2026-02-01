<x-layouts.app title="2FA IP-whitelist">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-admin-sidebar current="two-factor-whitelist" />
        <x-admin-header />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <!-- Header Section -->
            <div class="mb-8">
                <flux:heading size="2xl" level="1" class="text-zinc-900 dark:text-white mb-2">
                    2FA IP-whitelist
                </flux:heading>
                <flux:text class="text-lg text-zinc-600 dark:text-zinc-400">
                    Administrer IP-adresser som kan omgå tofaktorautentisering
                </flux:text>
            </div>

            <livewire:two-factor-ip-whitelist-manager />
        </flux:main>
    </div>
</x-layouts.app>
