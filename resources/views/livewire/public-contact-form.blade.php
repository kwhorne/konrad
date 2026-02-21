<div>
    @if($submitted)
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                <flux:icon.check class="w-8 h-8 text-green-600 dark:text-green-400" />
            </div>
            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-3">Takk for din henvendelse!</h3>
            <p class="text-zinc-600 dark:text-zinc-400 mb-6">Vi har mottatt meldingen din og svarer innen én virkedag.</p>
            <flux:button wire:click="$set('submitted', false)" variant="ghost" size="sm">Send en ny melding</flux:button>
        </div>
    @else
        <form wire:submit="submit" class="space-y-5">
            <div class="grid sm:grid-cols-2 gap-5">
                <flux:field>
                    <flux:label>Navn <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="name" placeholder="Ole Nordmann" />
                    <flux:error name="name" />
                </flux:field>
                <flux:field>
                    <flux:label>E-post <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="email" type="email" placeholder="ole@bedrift.no" />
                    <flux:error name="email" />
                </flux:field>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <flux:field>
                    <flux:label>Telefon</flux:label>
                    <flux:input wire:model="phone" placeholder="+47 000 00 000" />
                    <flux:error name="phone" />
                </flux:field>
                <flux:field>
                    <flux:label>Bedrift</flux:label>
                    <flux:input wire:model="company" placeholder="Bedrift AS" />
                    <flux:error name="company" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Hva gjelder henvendelsen? <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model="topic">
                    <flux:select.option value="general">Generell henvendelse</flux:select.option>
                    <flux:select.option value="demo">Jeg ønsker en demo</flux:select.option>
                    <flux:select.option value="pricing">Priser og abonnement</flux:select.option>
                    <flux:select.option value="support">Support / teknisk hjelp</flux:select.option>
                    <flux:select.option value="other">Annet</flux:select.option>
                </flux:select>
                <flux:error name="topic" />
            </flux:field>

            <flux:field>
                <flux:label>Melding <span class="text-red-500">*</span></flux:label>
                <flux:textarea wire:model="message" placeholder="Beskriv hva du lurer på..." rows="5" />
                <flux:error name="message" />
            </flux:field>

            <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove>Send melding</span>
                <span wire:loading>Sender...</span>
            </flux:button>
        </form>
    @endif
</div>
