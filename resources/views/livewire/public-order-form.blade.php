<div>
    @if($submitted)
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                <flux:icon.check class="w-8 h-8 text-green-600 dark:text-green-400" />
            </div>
            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-3">Bestilling mottatt!</h3>
            <p class="text-zinc-600 dark:text-zinc-400 mb-6">Vi har mottatt bestillingen din og tar kontakt innen én virkedag for å sette opp kontoen.</p>
            <flux:button wire:click="$set('submitted', false)" variant="ghost" size="sm">Send en ny bestilling</flux:button>
        </div>
    @else
        <form wire:submit="submit" class="space-y-6">

            <!-- Plan Selection -->
            <flux:field>
                <flux:label>Velg plan <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model="plan">
                    <flux:select.option value="" disabled>Velg en plan</flux:select.option>
                    <flux:select.option value="basis">Basis — 380 kr / mnd</flux:select.option>
                    <flux:select.option value="pro">Pro — 890 kr / mnd</flux:select.option>
                    <flux:select.option value="premium">Premium — 1 890 kr / mnd</flux:select.option>
                </flux:select>
                <flux:error name="plan" />
            </flux:field>

            <flux:separator />

            <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Firmainformasjon</p>

            <div class="grid sm:grid-cols-2 gap-5">
                <flux:field>
                    <flux:label>Firmanavn <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="companyName" placeholder="Ditt firma AS" />
                    <flux:error name="companyName" />
                </flux:field>
                <flux:field>
                    <flux:label>Organisasjonsnummer <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="orgNumber" placeholder="123 456 789" />
                    <flux:error name="orgNumber" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Adresse <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="address" placeholder="Gateadresse 123" />
                <flux:error name="address" />
            </flux:field>

            <div class="grid sm:grid-cols-2 gap-5">
                <flux:field>
                    <flux:label>Postnummer <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="postalCode" placeholder="0123" />
                    <flux:error name="postalCode" />
                </flux:field>
                <flux:field>
                    <flux:label>Poststed <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="city" placeholder="Oslo" />
                    <flux:error name="city" />
                </flux:field>
            </div>

            <flux:separator />

            <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Kontaktperson</p>

            <flux:field>
                <flux:label>Navn <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="contactName" placeholder="Ola Nordmann" />
                <flux:error name="contactName" />
            </flux:field>

            <div class="grid sm:grid-cols-2 gap-5">
                <flux:field>
                    <flux:label>E-post <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="email" type="email" placeholder="ola@firma.no" />
                    <flux:error name="email" />
                </flux:field>
                <flux:field>
                    <flux:label>Telefon <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="phone" type="tel" placeholder="+47 123 45 678" />
                    <flux:error name="phone" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Kommentarer</flux:label>
                <flux:textarea wire:model="comments" placeholder="Har du spesielle ønsker eller spørsmål?" rows="3" />
                <flux:error name="comments" />
            </flux:field>

            <!-- Terms -->
            <div class="flex items-start gap-3">
                <flux:checkbox wire:model="terms" />
                <p class="text-sm text-zinc-600 dark:text-zinc-300 pt-0.5">
                    Jeg godtar
                    <button type="button" x-data x-on:click="$flux.modal('terms-modal').show()" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">vilkårene</button>
                    og
                    <button type="button" x-data x-on:click="$flux.modal('privacy-modal').show()" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">personvernerklæringen</button>
                </p>
            </div>
            <flux:error name="terms" />

            <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove>Send bestilling</span>
                <span wire:loading>Sender...</span>
            </flux:button>
        </form>
    @endif
</div>
