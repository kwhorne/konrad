<div>
    @if($showModal)
        <flux:modal wire:model="showModal" name="release-notes" variant="flyout" class="w-full max-w-xl" :dismissible="false">
            <div class="space-y-6">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900">
                            <flux:icon.sparkles class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <flux:heading size="lg">Hva er nytt?</flux:heading>
                            <flux:text class="text-sm text-zinc-500">Versjon {{ $currentVersion }}</flux:text>
                        </div>
                    </div>
                </div>

                <flux:separator />

                <div class="release-notes max-h-[60vh] overflow-y-auto pr-2">
                    {!! $releaseNotes !!}
                </div>

                <flux:separator />

                <div class="flex justify-end">
                    <flux:button wire:click="markAsSeen" variant="primary">
                        <flux:icon.check class="w-4 h-4 mr-2" />
                        Supert, jeg har lest dette!
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
