<div class="relative">
    <div class="flex gap-2">
        <div class="relative flex-1">
            <flux:input
                wire:model="query"
                wire:keydown.enter="search"
                type="text"
                placeholder="Søk etter bedriftsnavn eller org.nummer..."
                icon="magnifying-glass"
            />
            @if($query)
                <button wire:click="clearSearch" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600">
                    <flux:icon.x-mark class="w-4 h-4" />
                </button>
            @endif
        </div>
        <flux:button wire:click="search" variant="primary" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="search">Søk i Brønnøysund</span>
            <span wire:loading wire:target="search">Søker...</span>
        </flux:button>
    </div>

    @if($error)
        <div class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <flux:text class="text-red-700 dark:text-red-300 text-sm">{{ $error }}</flux:text>
        </div>
    @endif

    @if($showResults)
        <div class="absolute z-50 w-full mt-2 bg-white dark:bg-zinc-900 rounded-lg shadow-xl border border-zinc-200 dark:border-zinc-700 max-h-96 overflow-y-auto">
            @if($isSearching)
                <div class="p-4 text-center">
                    <flux:icon.arrow-path class="w-6 h-6 text-zinc-400 animate-spin mx-auto mb-2" />
                    <flux:text class="text-zinc-500">Søker i Brønnøysundregistrene...</flux:text>
                </div>
            @elseif(count($results) > 0)
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($results as $index => $company)
                        <button
                            wire:click="selectCompany({{ $index }})"
                            type="button"
                            class="w-full text-left p-4 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $company['navn'] }}</span>
                                        <flux:badge variant="outline" size="sm">{{ $company['organisasjonsform'] }}</flux:badge>
                                    </div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400 space-y-0.5">
                                        <div>Org.nr: {{ $company['organisasjonsnummer'] }}</div>
                                        @if($company['adresse'] || $company['poststed'])
                                            <div>{{ $company['adresse'] }}{{ $company['adresse'] && $company['poststed'] ? ', ' : '' }}{{ $company['postnummer'] }} {{ $company['poststed'] }}</div>
                                        @endif
                                        @if($company['naeringskode'])
                                            <div class="text-xs">{{ $company['naeringskode'] }}</div>
                                        @endif
                                    </div>
                                </div>
                                <flux:icon.plus-circle class="w-5 h-5 text-blue-500 flex-shrink-0 mt-1" />
                            </div>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="p-4 text-center">
                    <flux:icon.building-office class="w-8 h-8 text-zinc-400 mx-auto mb-2" />
                    <flux:text class="text-zinc-500">Ingen bedrifter funnet for "{{ $query }}"</flux:text>
                    <flux:text class="text-zinc-400 text-sm mt-1">Prøv et annet søkeord eller org.nummer</flux:text>
                </div>
            @endif
        </div>
    @endif
</div>
