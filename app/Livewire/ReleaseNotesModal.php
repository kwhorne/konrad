<?php

namespace App\Livewire;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;

class ReleaseNotesModal extends Component
{
    public bool $showModal = false;

    public string $releaseNotes = '';

    public string $currentVersion = '';

    public function mount(): void
    {
        $this->currentVersion = config('app.version');
        $user = auth()->user();

        if ($user && $user->seen_version !== $this->currentVersion) {
            $this->showModal = true;
            $this->loadReleaseNotes();
        }
    }

    public function loadReleaseNotes(): void
    {
        $path = base_path('RELEASENOTES.md');

        if (File::exists($path)) {
            $markdown = File::get($path);
            $this->releaseNotes = Str::markdown($markdown, [
                'html_input' => 'strip',
            ]);
        } else {
            $this->releaseNotes = '<p>Ingen oppdateringer tilgjengelig.</p>';
        }
    }

    public function markAsSeen(): void
    {
        $user = auth()->user();

        if ($user) {
            $user->update(['seen_version' => $this->currentVersion]);
        }

        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.release-notes-modal');
    }
}
