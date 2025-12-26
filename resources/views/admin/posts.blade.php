<x-layouts.app title="Admin - Bloggartikler">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-admin-sidebar current="posts" />
        <x-admin-header current="posts" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">Bloggartikler</flux:heading>

            <flux:text class="mb-6 mt-2 text-base text-zinc-600 dark:text-zinc-400">Administrer artikler for Innsikt-bloggen</flux:text>

            <flux:separator variant="subtle" class="mb-8" />

            <livewire:admin.post-manager />
        </flux:main>

    </div>
</x-layouts.app>
