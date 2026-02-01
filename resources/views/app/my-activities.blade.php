<x-layouts.app title="Mine aktiviteter">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="my-activities" />
        <x-app-header current="my-activities" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <livewire:my-activities-manager />
        </flux:main>
    </div>
</x-layouts.app>
