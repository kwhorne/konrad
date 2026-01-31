<div>
    @if($companies->count() > 1)
        <flux:dropdown>
            <flux:button variant="ghost" class="w-full justify-start">
                <flux:icon.building-office class="w-4 h-4 mr-2 text-zinc-500" />
                <span class="truncate">{{ $currentCompany?->name ?? 'Velg selskap' }}</span>
                <flux:icon.chevron-down class="w-4 h-4 ml-auto text-zinc-400" />
            </flux:button>
            <flux:menu>
                @foreach($companies as $company)
                    <flux:menu.item
                        wire:click="switchCompany({{ $company->id }})"
                        :active="$currentCompany?->id === $company->id"
                    >
                        <div class="flex items-center gap-2">
                            @if($currentCompany?->id === $company->id)
                                <flux:icon.check class="w-4 h-4 text-green-500" />
                            @else
                                <div class="w-4 h-4"></div>
                            @endif
                            <span class="truncate">{{ $company->name }}</span>
                        </div>
                    </flux:menu.item>
                @endforeach
            </flux:menu>
        </flux:dropdown>
    @elseif($currentCompany)
        <div class="flex items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 dark:text-zinc-400">
            <flux:icon.building-office class="w-4 h-4 text-zinc-500" />
            <span class="truncate">{{ $currentCompany->name }}</span>
        </div>
    @endif
</div>
