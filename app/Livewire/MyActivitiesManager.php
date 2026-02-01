<?php

namespace App\Livewire;

use App\Models\UserNote;
use App\Services\MyActivitiesService;
use Illuminate\Support\Collection;
use Livewire\Component;

class MyActivitiesManager extends Component
{
    public string $activeTab = 'suggestions';

    public bool $isAnalyzing = false;

    public bool $hasSuggestions = false;

    public ?array $suggestions = null;

    public ?array $summary = null;

    public ?string $error = null;

    public ?string $generatedAt = null;

    public Collection $notes;

    public bool $showNoteModal = false;

    public ?int $editingNoteId = null;

    public string $noteTitle = '';

    public string $noteContent = '';

    public function mount(): void
    {
        $this->notes = collect();
        $this->loadNotes();
    }

    public function loadNotes(): void
    {
        $this->notes = UserNote::where('user_id', auth()->id())
            ->ordered()
            ->get();
    }

    public function generateSuggestions(): void
    {
        $this->isAnalyzing = true;
        $this->error = null;

        $user = auth()->user();

        if (! $user->currentCompany) {
            $this->error = 'Ingen bedrift valgt';
            $this->isAnalyzing = false;

            return;
        }

        $service = app(MyActivitiesService::class);
        $result = $service->generateSuggestions($user);

        if ($result['success']) {
            $this->suggestions = $result['suggestions'];
            $this->summary = $result['summary'];
            $this->generatedAt = $result['generated_at'];
            $this->hasSuggestions = true;
        } else {
            $this->error = $result['error'] ?? 'En ukjent feil oppstod';
        }

        $this->isAnalyzing = false;
    }

    public function resetSuggestions(): void
    {
        $this->hasSuggestions = false;
        $this->suggestions = null;
        $this->summary = null;
        $this->error = null;
        $this->generatedAt = null;
    }

    public function openNoteModal(?int $noteId = null): void
    {
        if ($noteId) {
            $note = UserNote::where('user_id', auth()->id())
                ->find($noteId);

            if ($note) {
                $this->editingNoteId = $noteId;
                $this->noteTitle = $note->title ?? '';
                $this->noteContent = $note->content;
            }
        } else {
            $this->editingNoteId = null;
            $this->noteTitle = '';
            $this->noteContent = '';
        }

        $this->showNoteModal = true;
    }

    public function closeNoteModal(): void
    {
        $this->showNoteModal = false;
        $this->editingNoteId = null;
        $this->noteTitle = '';
        $this->noteContent = '';
    }

    public function saveNote(): void
    {
        $this->validate([
            'noteContent' => 'required|string|min:1',
            'noteTitle' => 'nullable|string|max:255',
        ]);

        if ($this->editingNoteId) {
            $note = UserNote::where('user_id', auth()->id())
                ->find($this->editingNoteId);

            if ($note) {
                $note->update([
                    'title' => $this->noteTitle ?: null,
                    'content' => $this->noteContent,
                ]);
            }
        } else {
            UserNote::create([
                'user_id' => auth()->id(),
                'title' => $this->noteTitle ?: null,
                'content' => $this->noteContent,
            ]);
        }

        $this->closeNoteModal();
        $this->loadNotes();
    }

    public function togglePinNote(int $noteId): void
    {
        $note = UserNote::where('user_id', auth()->id())
            ->find($noteId);

        if ($note) {
            $note->update(['is_pinned' => ! $note->is_pinned]);
            $this->loadNotes();
        }
    }

    public function deleteNote(int $noteId): void
    {
        UserNote::where('user_id', auth()->id())
            ->where('id', $noteId)
            ->delete();

        $this->loadNotes();
    }

    public function getPriorityColorProperty(): string
    {
        if (! $this->suggestions) {
            return 'zinc';
        }

        $score = $this->suggestions['priority_score'] ?? 0;

        return match (true) {
            $score >= 80 => 'red',
            $score >= 60 => 'orange',
            $score >= 40 => 'yellow',
            $score >= 20 => 'lime',
            default => 'green',
        };
    }

    public function render()
    {
        return view('livewire.my-activities-manager');
    }
}
