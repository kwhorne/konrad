<x-layouts.public title="Bestill - Konrad Office">
    <!-- Order Header -->
    <section class="py-16 lg:py-24 bg-gradient-to-br from-indigo-50 via-white to-orange-50 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-4xl sm:text-5xl font-bold text-zinc-900 dark:text-white mb-6">
                    Kom i gang med Konrad Office
                </h1>
                <p class="text-xl text-zinc-600 dark:text-zinc-400">
                    Fyll ut skjemaet under, så tar vi kontakt for å sette opp din konto.
                </p>
            </div>
        </div>
    </section>

    <!-- Order Form Section -->
    <section class="py-16 lg:py-24 bg-white dark:bg-zinc-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-12 lg:gap-16">
                <!-- Form -->
                <div class="lg:col-span-3">
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-8 shadow-sm">
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-6">
                            Bestillingsskjema
                        </h2>

                        <form action="#" method="POST" class="space-y-6">
                            @csrf

                            <!-- Plan Selection -->
                            <flux:field>
                                <flux:label>Velg plan</flux:label>
                                <flux:select name="plan" required>
                                    <flux:select.option value="" disabled :selected="!request('plan')">Velg en plan</flux:select.option>
                                    <flux:select.option value="basis" :selected="request('plan') === 'basis'">Basis - 380,- / mnd</flux:select.option>
                                    <flux:select.option value="pro" :selected="request('plan') === 'pro'">Pro - 890,- / mnd</flux:select.option>
                                    <flux:select.option value="premium" :selected="request('plan') === 'premium'">Premium - 1 890,- / mnd</flux:select.option>
                                </flux:select>
                            </flux:field>

                            <flux:separator />

                            <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Firmainformasjon</h3>

                            <!-- Company Name -->
                            <flux:input
                                name="company_name"
                                label="Firmanavn"
                                placeholder="Ditt firma AS"
                                required
                            />

                            <!-- Organization Number -->
                            <flux:input
                                name="org_number"
                                label="Organisasjonsnummer"
                                placeholder="123 456 789"
                                required
                            />

                            <!-- Address -->
                            <flux:input
                                name="address"
                                label="Adresse"
                                placeholder="Gateadresse 123"
                                required
                            />

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Postal Code -->
                                <flux:input
                                    name="postal_code"
                                    label="Postnummer"
                                    placeholder="0123"
                                    required
                                />

                                <!-- City -->
                                <flux:input
                                    name="city"
                                    label="Poststed"
                                    placeholder="Oslo"
                                    required
                                />
                            </div>

                            <flux:separator />

                            <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Kontaktperson</h3>

                            <!-- Contact Person -->
                            <flux:input
                                name="contact_name"
                                label="Navn"
                                placeholder="Ola Nordmann"
                                required
                            />

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Email -->
                                <flux:input
                                    name="email"
                                    type="email"
                                    label="E-post"
                                    placeholder="ola@firma.no"
                                    required
                                />

                                <!-- Phone -->
                                <flux:input
                                    name="phone"
                                    type="tel"
                                    label="Telefon"
                                    placeholder="+47 123 45 678"
                                    required
                                />
                            </div>

                            <flux:separator />

                            <!-- Comments -->
                            <flux:textarea
                                name="comments"
                                label="Kommentarer (valgfritt)"
                                placeholder="Har du spesielle ønsker eller spørsmål? Skriv dem her..."
                                rows="3"
                            />

                            <!-- Terms -->
                            <div class="flex items-start gap-3">
                                <flux:checkbox name="terms" required />
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">
                                    Jeg godtar <button type="button" x-data x-on:click="$flux.modal('terms-modal').show()" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">vilkårene</button> og <button type="button" x-data x-on:click="$flux.modal('privacy-modal').show()" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">personvernerklæringen</button>
                                </p>
                            </div>

                            <flux:button type="submit" variant="primary" class="w-full">
                                Send bestilling
                            </flux:button>
                        </form>
                    </div>
                </div>

                <!-- How it works -->
                <div class="lg:col-span-2">
                    <div class="sticky top-24">
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-6">
                            Slik fungerer det
                        </h2>

                        <div class="space-y-8">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">1</span>
                                </div>
                                <div>
                                    <h3 class="font-medium text-zinc-900 dark:text-white mb-1">Send bestilling</h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Fyll ut skjemaet med informasjon om din bedrift og ønsket plan.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">2</span>
                                </div>
                                <div>
                                    <h3 class="font-medium text-zinc-900 dark:text-white mb-1">Vi tar kontakt</h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        En av våre rådgivere kontakter deg innen 1-2 virkedager for å gå gjennom dine behov.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">3</span>
                                </div>
                                <div>
                                    <h3 class="font-medium text-zinc-900 dark:text-white mb-1">Oppsett av konto</h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Vi setter opp kontoen din og sender deg innloggingsinformasjon via e-post.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center">
                                    <flux:icon.check class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <h3 class="font-medium text-zinc-900 dark:text-white mb-1">Kom i gang</h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Logg inn og begynn å bruke Konrad Office. Vi er tilgjengelige for support om du trenger hjelp.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Contact info -->
                        <div class="mt-10 p-6 bg-zinc-50 dark:bg-zinc-800 rounded-xl">
                            <h3 class="font-medium text-zinc-900 dark:text-white mb-3">Har du spørsmål?</h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
                                Ta gjerne kontakt med oss før du bestiller.
                            </p>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center gap-2 text-zinc-600 dark:text-zinc-400">
                                    <flux:icon.phone class="w-4 h-4" />
                                    <span>+47 55 61 20 50</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <flux:icon.envelope class="w-4 h-4 text-zinc-600 dark:text-zinc-400" />
                                    <a href="mailto:post@konradoffice.no" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                        post@konradoffice.no
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
