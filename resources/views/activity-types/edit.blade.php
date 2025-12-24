<x-layouts.app title="Rediger aktivitetstype">
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
                            Rediger aktivitetstype
                        </flux:heading>
                        <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                            {{ $activityType->name }}
                        </flux:text>
                    </div>
                </div>

                <flux:card class="bg-white dark:bg-zinc-900 shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <form action="{{ route('activity-types.update', $activityType) }}" method="POST" class="p-6 space-y-6">
                        @csrf
                        @method('PUT')

                        <flux:field>
                            <flux:label for="name">Navn *</flux:label>
                            <flux:input id="name" name="name" type="text" value="{{ old('name', $activityType->name) }}" required />
                            @error('name')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        <div class="grid grid-cols-2 gap-6">
                            <flux:field>
                                <flux:label for="icon">Ikon *</flux:label>
                                <flux:select id="icon" name="icon" required>
                                    <option value="">Velg ikon</option>
                                    <option value="phone" @selected(old('icon', $activityType->icon) == 'phone')>phone - Telefon</option>
                                    <option value="phone-arrow-down-left" @selected(old('icon', $activityType->icon) == 'phone-arrow-down-left')>phone-arrow-down-left - Telefon inn</option>
                                    <option value="phone-arrow-up-right" @selected(old('icon', $activityType->icon) == 'phone-arrow-up-right')>phone-arrow-up-right - Telefon ut</option>
                                    <option value="envelope" @selected(old('icon', $activityType->icon) == 'envelope')>envelope - E-post</option>
                                    <option value="inbox-arrow-down" @selected(old('icon', $activityType->icon) == 'inbox-arrow-down')>inbox-arrow-down - Innkommende e-post</option>
                                    <option value="paper-airplane" @selected(old('icon', $activityType->icon) == 'paper-airplane')>paper-airplane - Utgående e-post</option>
                                    <option value="calendar" @selected(old('icon', $activityType->icon) == 'calendar')>calendar - Møte</option>
                                    <option value="video-camera" @selected(old('icon', $activityType->icon) == 'video-camera')>video-camera - Videomøte</option>
                                    <option value="chat-bubble-left-right" @selected(old('icon', $activityType->icon) == 'chat-bubble-left-right')>chat-bubble-left-right - Chat</option>
                                    <option value="document-text" @selected(old('icon', $activityType->icon) == 'document-text')>document-text - Dokument</option>
                                    <option value="clipboard-document-check" @selected(old('icon', $activityType->icon) == 'clipboard-document-check')>clipboard-document-check - Oppgave</option>
                                    <option value="map-pin" @selected(old('icon', $activityType->icon) == 'map-pin')>map-pin - Besøk</option>
                                    <option value="gift" @selected(old('icon', $activityType->icon) == 'gift')>gift - Gave</option>
                                    <option value="megaphone" @selected(old('icon', $activityType->icon) == 'megaphone')>megaphone - Kampanje</option>
                                </flux:select>
                                @error('icon')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label for="color">Farge *</flux:label>
                                <flux:select id="color" name="color" required>
                                    <option value="">Velg farge</option>
                                    <option value="blue" @selected(old('color', $activityType->color) == 'blue')>Blå</option>
                                    <option value="green" @selected(old('color', $activityType->color) == 'green')>Grønn</option>
                                    <option value="amber" @selected(old('color', $activityType->color) == 'amber')>Gul/Oransje</option>
                                    <option value="red" @selected(old('color', $activityType->color) == 'red')>Rød</option>
                                    <option value="purple" @selected(old('color', $activityType->color) == 'purple')>Lilla</option>
                                    <option value="pink" @selected(old('color', $activityType->color) == 'pink')>Rosa</option>
                                    <option value="indigo" @selected(old('color', $activityType->color) == 'indigo')>Indigo</option>
                                    <option value="cyan" @selected(old('color', $activityType->color) == 'cyan')>Cyan</option>
                                </flux:select>
                                @error('color')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label for="description">Beskrivelse</flux:label>
                            <flux:textarea id="description" name="description" rows="3">{{ old('description', $activityType->description) }}</flux:textarea>
                            @error('description')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label for="sort_order">Rekkefølge</flux:label>
                            <flux:input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $activityType->sort_order) }}" />
                            <flux:description>Lavere tall vises først</flux:description>
                            @error('sort_order')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        <flux:field>
                            <flux:checkbox id="is_active" name="is_active" value="1" label="Aktiv" :checked="old('is_active', $activityType->is_active)" />
                        </flux:field>

                        <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button href="{{ route('activity-types.index') }}" variant="ghost">
                                Avbryt
                            </flux:button>
                            <flux:button type="submit" variant="primary">
                                Lagre endringer
                            </flux:button>
                        </div>
                    </form>
                </flux:card>
            </div>
        </flux:main>
    </div>
</x-layouts.app>
