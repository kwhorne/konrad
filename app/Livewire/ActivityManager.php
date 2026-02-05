<?php

namespace App\Livewire;

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\User;
use App\Rules\ExistsInCompany;
use App\Rules\UserInCompany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityManager extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public $contactId;

    public $showModal = false;

    public $editingId = null;

    public $filter = 'all';

    // Form fields
    public $activity_type_id = '';

    public $subject = '';

    public $description = '';

    public $due_date = '';

    public $assigned_to = '';

    public $is_completed = false;

    protected function rules(): array
    {
        return [
            'activity_type_id' => ['required', new ExistsInCompany('activity_types')],
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'assigned_to' => ['nullable', new UserInCompany],
            'is_completed' => 'boolean',
        ];
    }

    public function mount($contactId): void
    {
        $this->contactId = $contactId;
    }

    public function openModal($id = null): void
    {
        $this->resetForm();

        if ($id) {
            $this->editingId = $id;
            $activity = Activity::findOrFail($id);

            $this->activity_type_id = $activity->activity_type_id;
            $this->subject = $activity->subject;
            $this->description = $activity->description;
            $this->due_date = $activity->due_date?->format('Y-m-d\TH:i');
            $this->assigned_to = $activity->assigned_to;
            $this->is_completed = $activity->is_completed;
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->authorize('create', Activity::class);
        $this->validate();

        $data = [
            'contact_id' => $this->contactId,
            'activity_type_id' => $this->activity_type_id,
            'subject' => $this->subject,
            'description' => $this->description,
            'due_date' => $this->due_date ?: null,
            'assigned_to' => $this->assigned_to ?: null,
            'is_completed' => $this->is_completed,
        ];

        if ($this->editingId) {
            $activity = Activity::findOrFail($this->editingId);

            if ($this->is_completed && ! $activity->is_completed) {
                $data['completed_at'] = now();
            } elseif (! $this->is_completed) {
                $data['completed_at'] = null;
            }

            $activity->update($data);
        } else {
            $data['created_by'] = Auth::id();
            if ($this->is_completed) {
                $data['completed_at'] = now();
            }
            Activity::create($data);
        }

        $this->closeModal();
    }

    public function toggleComplete($id): void
    {
        $activity = Activity::findOrFail($id);
        $this->authorize('update', $activity);

        if ($activity->is_completed) {
            $activity->markAsIncomplete();
        } else {
            $activity->markAsCompleted();
        }
    }

    public function delete($id): void
    {
        $activity = Activity::findOrFail($id);
        $this->authorize('delete', $activity);
        $activity->delete();
    }

    public function setFilter($filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->activity_type_id = '';
        $this->subject = '';
        $this->description = '';
        $this->due_date = '';
        $this->assigned_to = '';
        $this->is_completed = false;
        $this->resetValidation();
    }

    public function render()
    {
        $query = Activity::with(['activityType', 'creator', 'assignee'])
            ->where('contact_id', $this->contactId);

        if ($this->filter === 'pending') {
            $query->pending();
        } elseif ($this->filter === 'completed') {
            $query->completed();
        } elseif ($this->filter === 'overdue') {
            $query->overdue();
        }

        $activities = $query->latest()->paginate(10);

        return view('livewire.activity-manager', [
            'activities' => $activities,
            'activityTypes' => ActivityType::active()->ordered()->get(),
            'users' => User::all(),
            'stats' => [
                'total' => Activity::where('contact_id', $this->contactId)->count(),
                'pending' => Activity::where('contact_id', $this->contactId)->pending()->count(),
                'completed' => Activity::where('contact_id', $this->contactId)->completed()->count(),
                'overdue' => Activity::where('contact_id', $this->contactId)->overdue()->count(),
            ],
        ]);
    }
}
