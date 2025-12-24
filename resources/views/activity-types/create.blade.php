<x-layouts.app title="Ny aktivitetstype">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
        <x-app-sidebar current="contacts" />
        <x-app-header current="contacts" />

        <flux:main class="bg-zinc-50 dark:bg-zinc-800">
            <div class="max-w-2xl mx-auto">
                <div class="flex items-center gap-4 mb-8">
                    <flux:button href="{{ route('activity-types.index') }}" variant="ghost" size="sm">
                        <flux:icon.arrow-left class="w-5 h-5" />
                    </flux:button>
                    <div>
                        <flux:heading size="xl" level="1" class="text-zinc-900 dark:text-white">
                            Ny aktivitetstype
                        </flux:heading>
                        <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                            Opprett en ny aktivitetstype for kontaktoppfølging
                        </flux:text>
                    </div>
                </div>

                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <form action="{{ route('activity-types.store') }}" method="POST" class="p-6 space-y-6">
                        @csrf

                        <flux:field>
                            <flux:label for="name">Navn *</flux:label>
                            <flux:input id="name" name="name" type="text" value="{{ old('name') }}" required />
                            @error('name')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        <div class="grid grid-cols-2 gap-6">
                            <flux:field>
                                <flux:label for="icon">Ikon *</flux:label>
                                <flux:select id="icon" name="icon" required>
                                    <option value="">Velg ikon</option>
                                    <option value="phone" @selected(old('icon') == 'phone')>phone - Telefon</option>
                                    <option value="phone-arrow-down-left" @selected(old('icon') == 'phone-arrow-down-left')>phone-arrow-down-left - Telefon inn</option>
                                    <option value="phone-arrow-up-right" @selected(old('icon') == 'phone-arrow-up-right')>phone-arrow-up-right - Telefon ut</option>
                                    <option value="envelope" @selected(old('icon') == 'envelope')>envelope - E-post</option>
                                    <option value="inbox-arrow-down" @selected(old('icon') == 'inbox-arrow-down')>inbox-arrow-down - Innkommende e-post</option>
                                    <option value="paper-airplane" @selected(old('icon') == 'paper-airplane')>paper-airplane - Utgående e-post</option>
                                    <option value="calendar" @selected(old('icon') == 'calendar')>calendar - Møte</option>
                                    <option value="video-camera" @selected(old('icon') == 'video-camera')>video-camera - Videomøte</option>
                                    <option value="chat-bubble-left-right" @selected(old('icon') == 'chat-bubble-left-right')>chat-bubble-left-right - Chat</option>
                                    <option value="document-text" @selected(old('icon') == 'document-text')>document-text - Dokument</option>
                                    <option value="clipboard-document-check" @selected(old('icon') == 'clipboard-document-check')>clipboard-document-check - Oppgave</option>
                                    <option value="map-pin" @selected(old('icon') == 'map-pin')>map-pin - Besøk</option>
                                    <option value="gift" @selected(old('icon') == 'gift')>gift - Gave</option>
                                    <option value="megaphone" @selected(old('icon') == 'megaphone')>megaphone - Kampanje</option>
                                </flux:select>
                                @error('icon')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label for="color">Farge *</flux:label>
                                <flux:select id="color" name="color" required>
                                    <option value="">Velg farge</option>
                                    <option value="blue" @selected(old('color') == 'blue')>Blå</option>
                                    <option value="green" @selected(old('color') == 'green')>Grønn</option>
                                    <option value="amber" @selected(old('color') == 'amber')>Gul/Oransje</option>
                                    <option value="red" @selected(old('color') == 'red')>Rød</option>
                                    <option value="purple" @selected(old('color') == 'purple')>Lilla</option>
                                    <option value="pink" @selected(old('color') == 'pink')>Rosa</option>
                                    <option value="indigo" @selected(old('color') == 'indigo')>Indigo</option>
                                    <option value="cyan" @selected(old('color') == 'cyan')>Cyan</option>
                                </flux:select>
                                @error('color')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label for="description">Beskrivelse</flux:label>
                            <flux:textarea id="description" name="description" rows="3">{{ old('description') }}</flux:textarea>
                            @error('description')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label for="sort_order">Rekkefølge</flux:label>
                            <flux:input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', 0) }}" />
                            <flux:description>Lavere tall vises først</flux:description>
                            @error('sort_order')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        <flux:field>
                            <flux:checkbox id="is_active" name="is_active" value="1" label="Aktiv" checked="{{ old('is_active', true) }}" />
                        </flux:field>

                        <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button href="{{ route('activity-types.index') }}" variant="ghost">
                                Avbryt
                            </flux:button>
                            <flux:button type="submit" variant="primary">
                                Opprett aktivitetstype
                            </flux:button>
                        </div>
                    </form>
                </flux:card>
            </div>
        </flux:main>
    </div>
</x-layouts.app>
