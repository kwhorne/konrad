<div>
    {{-- Stop trying to control. --}}
    <flux:field>
        <flux:label for="attachments">Vedlegg</flux:label>
        <flux:file-upload wire:model="attachments" multiple>
            <flux:file-upload.dropzone
                heading="Slipp filer her eller klikk for å velge"
                text="PDF, Word, Excel, bilder (maks 10MB per fil)"
                with-progress
                inline
            />
        </flux:file-upload>
        @error('attachments')
            <flux:error>{{ $message }}</flux:error>
        @enderror
    </flux:field>

    <!-- Hidden input to pass attachment data to main form -->
    <input type="hidden" name="livewire_attachments" value='@json(array_map(fn($a) => $a->getFilename(), $attachments))' />

    @if(count($attachments) > 0)
        <div class="mt-4 flex flex-col gap-2">
            @foreach($attachments as $index => $attachment)
                <flux:file-item 
                    :heading="$attachment->getClientOriginalName()" 
                    :size="$attachment->getSize()"
                >
                    <x-slot name="actions">
                        <flux:file-item.remove 
                            wire:click="removeAttachment({{ $index }})" 
                            aria-label="Fjern fil: {{ $attachment->getClientOriginalName() }}"
                        />
                    </x-slot>
                </flux:file-item>
            @endforeach
        </div>
    @endif
</div>
