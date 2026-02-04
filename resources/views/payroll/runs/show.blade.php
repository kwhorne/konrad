<x-layouts.payroll title="Lønnskjøring">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-payroll-sidebar current="runs" />
        <x-app-header current="runs" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <livewire:payroll.payroll-run-detail :payroll-run-id="$payrollRun" />
        </flux:main>
    </div>
</x-layouts.payroll>
