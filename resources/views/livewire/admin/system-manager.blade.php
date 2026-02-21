<div class="space-y-8">
    {{-- Cache Actions --}}
    <flux:card class="bg-white dark:bg-zinc-900">
        <flux:heading size="lg" level="2" class="mb-4">Cache</flux:heading>
        <div class="flex items-center gap-4">
            <flux:button
                wire:click="clearCache"
                wire:loading.attr="disabled"
                variant="outline"
                icon="arrow-path"
            >
                <span wire:loading.remove wire:target="clearCache">Tøm cache</span>
                <span wire:loading wire:target="clearCache">Tømmer...</span>
            </flux:button>
            <flux:text class="text-sm text-zinc-500">Tømmer application, view og config cache</flux:text>
        </div>
    </flux:card>

    {{-- Queue Status --}}
    <flux:card class="bg-white dark:bg-zinc-900">
        <flux:heading size="lg" level="2" class="mb-4">Køstatus</flux:heading>
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
                <flux:text class="text-sm text-zinc-500">Ventende jobber:</flux:text>
                <flux:badge color="{{ $pendingJobs > 0 ? 'yellow' : 'green' }}">{{ $pendingJobs }}</flux:badge>
            </div>
            <div class="flex items-center gap-2">
                <flux:text class="text-sm text-zinc-500">Mislykkede jobber:</flux:text>
                <flux:badge color="{{ $failedJobs > 0 ? 'red' : 'green' }}">{{ $failedJobs }}</flux:badge>
            </div>
        </div>
    </flux:card>

    {{-- Logs --}}
    <flux:card class="bg-white dark:bg-zinc-900">
        <div class="flex items-center justify-between mb-4">
            <flux:heading size="lg" level="2">Applikasjonslogg</flux:heading>
            @if($showLogs)
                <flux:button wire:click="hideLogs" variant="ghost" size="sm" icon="x-mark">
                    Skjul
                </flux:button>
            @else
                <flux:button wire:click="loadLogs" variant="outline" size="sm" icon="document-text">
                    <span wire:loading.remove wire:target="loadLogs">Vis siste 50 linjer</span>
                    <span wire:loading wire:target="loadLogs">Laster...</span>
                </flux:button>
            @endif
        </div>

        @if($showLogs)
            <div class="bg-zinc-950 rounded-lg p-4 overflow-auto max-h-96">
                <pre class="text-xs text-green-400 whitespace-pre-wrap font-mono">{{ $logContent ?: 'Tom loggfil.' }}</pre>
            </div>
        @endif
    </flux:card>
</div>
