<div class="max-w-xl mx-auto">
    <div class="text-center mb-8">
        <flux:icon.arrow-up-tray class="h-16 w-16 text-indigo-500 mx-auto mb-4" />
        <flux:heading size="lg" class="text-zinc-900 dark:text-white mb-2">
            Last opp kontoutskrift
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">
            Last opp en CSV-fil fra banken din for å starte avstemmingen
        </flux:text>
    </div>

    <div class="space-y-6">
        {{-- Bank account selection --}}
        <flux:field>
            <flux:label>Bankkonto</flux:label>
            <flux:select wire:model="selectedBankAccountId">
                <option value="">Velg bankkonto...</option>
                @foreach($this->bankAccounts as $account)
                    <option value="{{ $account->id }}">{{ $account->account_number }} - {{ $account->name }}</option>
                @endforeach
            </flux:select>
            <flux:description>Velg hvilken bankkonto kontoutskriften tilhører</flux:description>
        </flux:field>

        {{-- Format selection --}}
        <flux:field>
            <flux:label>CSV-format</flux:label>
            <flux:select wire:model="selectedFormatId">
                @foreach($this->formats as $key => $name)
                    <option value="{{ $key }}">{{ $name }}</option>
                @endforeach
            </flux:select>
            <flux:description>Velg bankformat eller la systemet auto-detektere</flux:description>
        </flux:field>

        {{-- File upload --}}
        <flux:field>
            <flux:label>CSV-fil *</flux:label>
            <div class="mt-1">
                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer
                    border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800
                    hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <flux:icon.cloud-arrow-up class="w-10 h-10 mb-3 text-zinc-400" />
                        <p class="mb-2 text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="font-semibold">Klikk for å laste opp</span> eller dra og slipp
                        </p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">CSV eller TXT (maks 10 MB)</p>
                    </div>
                    <input type="file" wire:model="uploadFile" accept=".csv,.txt" class="hidden" />
                </label>
            </div>
            @error('uploadFile')<flux:error>{{ $message }}</flux:error>@enderror

            @if($uploadFile)
                <div class="mt-3 flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <flux:icon.document-check class="w-6 h-6 text-green-500" />
                    <div>
                        <flux:text class="text-sm font-medium text-green-800 dark:text-green-200">
                            {{ $uploadFile->getClientOriginalName() }}
                        </flux:text>
                        <flux:text class="text-xs text-green-600 dark:text-green-400">
                            {{ number_format($uploadFile->getSize() / 1024, 1) }} KB
                        </flux:text>
                    </div>
                </div>
            @endif
        </flux:field>

        {{-- Supported banks info --}}
        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <flux:text class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                Støttede banker
            </flux:text>
            <div class="flex flex-wrap gap-2">
                <flux:badge variant="outline" color="blue">DNB</flux:badge>
                <flux:badge variant="outline" color="blue">Nordea</flux:badge>
                <flux:badge variant="outline" color="blue">SpareBank 1</flux:badge>
                <flux:badge variant="outline" color="blue">Sbanken</flux:badge>
            </div>
        </div>

        {{-- Submit button --}}
        <div class="flex justify-end pt-4">
            <flux:button wire:click="uploadAndParse" variant="primary" wire:loading.attr="disabled" :disabled="!$uploadFile">
                <span wire:loading.remove wire:target="uploadAndParse">
                    <flux:icon.arrow-right class="w-4 h-4 mr-2" />
                    Importer transaksjoner
                </span>
                <span wire:loading wire:target="uploadAndParse">
                    <flux:icon.arrow-path class="w-4 h-4 mr-2 animate-spin" />
                    Importerer...
                </span>
            </flux:button>
        </div>
    </div>
</div>
