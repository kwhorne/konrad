<x-layouts.app title="Admin - Brukere">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-admin-sidebar current="users" />
        <x-admin-header current="users" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">Brukerhåndtering</flux:heading>

            <flux:text class="mb-6 mt-2 text-base text-zinc-600 dark:text-zinc-400">Administrer alle registrerte brukere og deres tillatelser</flux:text>

            <flux:separator variant="subtle" class="mb-8" />

            <livewire:user-manager />
        </flux:main>

    </div>
</x-layouts.app>
