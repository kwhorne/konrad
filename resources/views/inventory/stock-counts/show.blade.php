<x-layouts.app title="Varetelling">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="stock-counts" />
        <x-app-header current="stock-counts" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <livewire:stock-count-show :stock-count-id="$stockCount" />
        </flux:main>
    </div>
</x-layouts.app>
