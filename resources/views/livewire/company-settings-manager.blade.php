<div>
    <form wire:submit="save" class="space-y-8">
        {{-- Firmainformasjon --}}
        <flux:card>
            <flux:heading size="lg">Firmainformasjon</flux:heading>
            <flux:text class="mt-1 mb-6">Grunnleggende informasjon om firmaet som vises på dokumenter.</flux:text>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input
                    wire:model="company_name"
                    label="Firmanavn"
                    placeholder="Mitt Firma AS"
                    required
                />

                <flux:input
                    wire:model="organization_number"
                    label="Organisasjonsnummer"
                    placeholder="123 456 789"
                />

                <flux:input
                    wire:model="vat_number"
                    label="MVA-nummer"
                    placeholder="NO123456789MVA"
                />
            </div>
        </flux:card>

        {{-- Adresse --}}
        <flux:card>
            <flux:heading size="lg">Adresse</flux:heading>
            <flux:text class="mt-1 mb-6">Firmaets besøks- eller postadresse.</flux:text>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <flux:input
                        wire:model="address"
                        label="Adresse"
                        placeholder="Storgata 1"
                    />
                </div>

                <flux:input
                    wire:model="postal_code"
                    label="Postnummer"
                    placeholder="0001"
                />

                <flux:input
                    wire:model="city"
                    label="Sted"
                    placeholder="Oslo"
                />

                <flux:input
                    wire:model="country"
                    label="Land"
                    placeholder="Norge"
                />
            </div>
        </flux:card>

        {{-- Kontaktinformasjon --}}
        <flux:card>
            <flux:heading size="lg">Kontaktinformasjon</flux:heading>
            <flux:text class="mt-1 mb-6">Kontaktdetaljer som vises på dokumenter.</flux:text>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input
                    wire:model="phone"
                    label="Telefon"
                    type="tel"
                    placeholder="+47 123 45 678"
                />

                <flux:input
                    wire:model="email"
                    label="E-post"
                    type="email"
                    placeholder="post@mittfirma.no"
                />

                <flux:input
                    wire:model="website"
                    label="Nettside"
                    type="url"
                    placeholder="https://www.mittfirma.no"
                />
            </div>
        </flux:card>

        {{-- Bankinformasjon --}}
        <flux:card>
            <flux:heading size="lg">Bankinformasjon</flux:heading>
            <flux:text class="mt-1 mb-6">Bankdetaljer for betalinger som vises på fakturaer.</flux:text>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input
                    wire:model="bank_name"
                    label="Banknavn"
                    placeholder="DNB"
                />

                <flux:input
                    wire:model="bank_account"
                    label="Kontonummer"
                    placeholder="1234.56.78901"
                />

                <flux:input
                    wire:model="iban"
                    label="IBAN"
                    placeholder="NO12 3456 7890 1234"
                />

                <flux:input
                    wire:model="swift"
                    label="SWIFT/BIC"
                    placeholder="DNBANOKKXXX"
                />
            </div>
        </flux:card>

        {{-- Logo --}}
        <flux:card>
            <flux:heading size="lg">Firmalogo</flux:heading>
            <flux:text class="mt-1 mb-6">Last opp en logo som vises på dokumenter. Anbefalt format: PNG eller JPG, maks 2MB.</flux:text>

            <div class="space-y-4">
                @if($current_logo_path)
                    <div class="flex items-center gap-4">
                        <img src="{{ Storage::url($current_logo_path) }}" alt="Firmalogo" class="h-20 w-auto object-contain border rounded-lg p-2 bg-white">
                        <flux:button variant="danger" size="sm" wire:click="deleteLogo" wire:confirm="Er du sikker på at du vil slette logoen?">
                            Slett logo
                        </flux:button>
                    </div>
                @endif

                <flux:file-upload wire:model="logo" label="{{ $current_logo_path ? 'Bytt logo' : 'Last opp logo' }}">
                    <flux:file-upload.dropzone
                        heading="Slipp fil her eller klikk for å bla"
                        text="PNG, JPG opptil 2MB"
                        inline
                    />
                </flux:file-upload>

                @if($logo)
                    <div class="mt-3">
                        <flux:file-item
                            :heading="$logo->getClientOriginalName()"
                            :image="$logo->temporaryUrl()"
                            :size="$logo->getSize()"
                        />
                    </div>
                @endif

                @error('logo')
                    <flux:text class="text-red-500 text-sm">{{ $message }}</flux:text>
                @enderror
            </div>
        </flux:card>

        {{-- Dokumentinnstillinger --}}
        <flux:card>
            <flux:heading size="lg">Dokumentinnstillinger</flux:heading>
            <flux:text class="mt-1 mb-6">Standardinnstillinger og tekster som brukes på dokumenter.</flux:text>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input
                    wire:model="default_payment_days"
                    label="Standard betalingsfrist (dager)"
                    type="number"
                    min="1"
                    max="365"
                />

                <flux:input
                    wire:model="default_quote_validity_days"
                    label="Standard tilbudsgyldighet (dager)"
                    type="number"
                    min="1"
                    max="365"
                />

                <div class="md:col-span-2">
                    <flux:textarea
                        wire:model="invoice_terms"
                        label="Fakturabetingelser"
                        placeholder="Ved forsinket betaling beregnes forsinkelsesrente..."
                        rows="3"
                    />
                </div>

                <div class="md:col-span-2">
                    <flux:textarea
                        wire:model="quote_terms"
                        label="Tilbudsbetingelser"
                        placeholder="Tilbudet er gyldig i..."
                        rows="3"
                    />
                </div>

                <div class="md:col-span-2">
                    <flux:textarea
                        wire:model="order_terms"
                        label="Ordrebetingelser"
                        placeholder="Leveringstid og vilkår..."
                        rows="3"
                    />
                </div>

                <div class="md:col-span-2">
                    <flux:textarea
                        wire:model="document_footer"
                        label="Bunntekst på dokumenter"
                        placeholder="Takk for handelen!"
                        rows="2"
                    />
                </div>
            </div>
        </flux:card>

        {{-- Lagre-knapp --}}
        <div class="flex justify-end">
            <flux:button type="submit" variant="primary" icon="check">
                Lagre innstillinger
            </flux:button>
        </div>
    </form>
</div>
