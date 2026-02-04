<?php

namespace App\Livewire;

use App\Exceptions\TimesheetValidationException;
use App\Models\Project;
use App\Models\Timesheet;
use App\Models\WorkOrder;
use App\Services\TimesheetService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class TimesheetManager extends Component
{
    use AuthorizesRequests;

    public ?Timesheet $timesheet = null;

    public Carbon $currentWeek;

    public array $entries = [];

    public array $rows = [];

    public bool $showAddRowModal = false;

    public ?string $newRowType = null;

    public ?int $newRowProjectId = null;

    public ?int $newRowWorkOrderId = null;

    public ?string $newRowDescription = null;

    public bool $showSubmitModal = false;

    public string $notes = '';

    // Quick entry modal
    public bool $showQuickEntryModal = false;

    public ?string $quickEntryType = 'project';

    public ?int $quickEntryProjectId = null;

    public ?int $quickEntryWorkOrderId = null;

    public ?string $quickEntryDescription = null;

    public ?string $quickEntryDate = null;

    public ?float $quickEntryHours = null;

    public ?string $quickEntryNote = null;

    protected $listeners = ['refreshTimesheet' => '$refresh'];

    public function mount(): void
    {
        $this->currentWeek = Carbon::now();
        $this->loadTimesheet();
    }

    public function loadTimesheet(): void
    {
        $service = app(TimesheetService::class);
        $this->timesheet = $service->getOrCreateTimesheet(auth()->user(), $this->currentWeek);
        $this->notes = $this->timesheet->notes ?? '';
        $this->buildRows();
    }

    protected function buildRows(): void
    {
        $this->rows = [];

        if (! $this->timesheet) {
            return;
        }

        $entries = $this->timesheet->entries()->with(['project', 'workOrder'])->get();

        // Group by unique project/work order/description combination
        $grouped = $entries->groupBy(function ($entry) {
            if ($entry->work_order_id) {
                return 'wo_'.$entry->work_order_id;
            }
            if ($entry->project_id) {
                return 'proj_'.$entry->project_id;
            }

            return 'desc_'.($entry->description ?? 'unspecified');
        });

        foreach ($grouped as $key => $groupEntries) {
            $first = $groupEntries->first();

            $row = [
                'key' => $key,
                'type' => $first->target_type,
                'project_id' => $first->project_id,
                'work_order_id' => $first->work_order_id,
                'description' => $first->description,
                'label' => $first->target_label,
                'is_billable' => $first->is_billable,
                'hours' => [],
                'entry_ids' => [],
            ];

            // Fill in hours for each day
            $current = $this->timesheet->week_start->copy();
            while ($current <= $this->timesheet->week_end) {
                $dateKey = $current->format('Y-m-d');
                $dayEntry = $groupEntries->first(fn ($e) => $e->entry_date->format('Y-m-d') === $dateKey);
                $row['hours'][$dateKey] = $dayEntry ? (float) $dayEntry->hours : null;
                $row['entry_ids'][$dateKey] = $dayEntry?->id;
                $current->addDay();
            }

            $this->rows[] = $row;
        }
    }

    public function previousWeek(): void
    {
        $this->currentWeek = $this->currentWeek->copy()->subWeek();
        $this->loadTimesheet();
    }

    public function nextWeek(): void
    {
        $this->currentWeek = $this->currentWeek->copy()->addWeek();
        $this->loadTimesheet();
    }

    public function goToCurrentWeek(): void
    {
        $this->currentWeek = Carbon::now();
        $this->loadTimesheet();
    }

    public function updateHours(int $rowIndex, string $date, $value): void
    {
        if (! $this->timesheet?->is_editable) {
            return;
        }

        $row = $this->rows[$rowIndex] ?? null;
        if (! $row) {
            return;
        }

        $hours = $value !== '' && $value !== null ? (float) $value : null;

        $entryId = $row['entry_ids'][$date] ?? null;

        if ($hours === null || $hours === 0.0) {
            // Delete entry if exists - use scoped query to prevent IDOR
            if ($entryId) {
                $this->timesheet->entries()->where('id', $entryId)->delete();
            }
        } else {
            try {
                $service = app(TimesheetService::class);
                $service->saveEntry($this->timesheet, [
                    'entry_date' => $date,
                    'hours' => $hours,
                    'project_id' => $row['project_id'],
                    'work_order_id' => $row['work_order_id'],
                    'description' => $row['description'],
                    'is_billable' => $row['is_billable'] ?? true,
                ], $entryId);
            } catch (TimesheetValidationException $e) {
                session()->flash('error', $e->getMessage());

                return;
            }
        }

        $this->timesheet->refresh();
        $this->buildRows();
    }

    public function openAddRowModal(): void
    {
        $this->newRowType = 'project';
        $this->newRowProjectId = null;
        $this->newRowWorkOrderId = null;
        $this->newRowDescription = null;
        $this->showAddRowModal = true;
    }

    public function closeAddRowModal(): void
    {
        $this->showAddRowModal = false;
        $this->resetAddRowForm();
    }

    public function addRow(): void
    {
        if (! $this->timesheet?->is_editable) {
            return;
        }

        try {
            // Create an entry for the first day with minimum hours to establish the row
            $service = app(TimesheetService::class);
            $service->saveEntry($this->timesheet, [
                'entry_date' => $this->timesheet->week_start->format('Y-m-d'),
                'hours' => TimesheetService::MIN_HOURS_PER_ENTRY,
                'project_id' => $this->newRowType === 'project' ? $this->newRowProjectId : null,
                'work_order_id' => $this->newRowType === 'workorder' ? $this->newRowWorkOrderId : null,
                'description' => $this->newRowType === 'other' ? $this->newRowDescription : null,
                'is_billable' => true,
            ]);

            $this->timesheet->refresh();
            $this->buildRows();
            $this->closeAddRowModal();
        } catch (TimesheetValidationException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function deleteRow(int $rowIndex): void
    {
        if (! $this->timesheet?->is_editable) {
            return;
        }

        $row = $this->rows[$rowIndex] ?? null;
        if (! $row) {
            return;
        }

        // Delete all entries for this row - use scoped query to prevent IDOR
        $entryIds = array_filter($row['entry_ids']);
        if (! empty($entryIds)) {
            $this->timesheet->entries()->whereIn('id', $entryIds)->delete();
        }

        $this->timesheet->refresh();
        $this->buildRows();
    }

    public function openSubmitModal(): void
    {
        $this->showSubmitModal = true;
    }

    public function closeSubmitModal(): void
    {
        $this->showSubmitModal = false;
    }

    public function openQuickEntryModal(?string $date = null, ?int $rowIndex = null): void
    {
        // Default values
        $this->quickEntryType = 'project';
        $this->quickEntryProjectId = null;
        $this->quickEntryWorkOrderId = null;
        $this->quickEntryDescription = null;
        $this->quickEntryDate = $date ?? $this->timesheet?->week_start?->format('Y-m-d');
        $this->quickEntryHours = null;
        $this->quickEntryNote = null;

        // Pre-populate from row if provided
        if ($rowIndex !== null && isset($this->rows[$rowIndex])) {
            $row = $this->rows[$rowIndex];

            if ($row['work_order_id']) {
                $this->quickEntryType = 'workorder';
                $this->quickEntryWorkOrderId = $row['work_order_id'];
            } elseif ($row['project_id']) {
                $this->quickEntryType = 'project';
                $this->quickEntryProjectId = $row['project_id'];
            } elseif ($row['description']) {
                $this->quickEntryType = 'other';
                $this->quickEntryDescription = $row['description'];
            }
        }

        $this->showQuickEntryModal = true;
    }

    public function closeQuickEntryModal(): void
    {
        $this->showQuickEntryModal = false;
        $this->resetQuickEntryForm();
    }

    public function saveQuickEntry(): void
    {
        if (! $this->timesheet?->is_editable) {
            return;
        }

        $this->validate([
            'quickEntryDate' => 'required|date',
            'quickEntryHours' => 'required|numeric|min:0.5|max:24',
            'quickEntryType' => 'required|in:project,workorder,other',
            'quickEntryProjectId' => 'required_if:quickEntryType,project',
            'quickEntryWorkOrderId' => 'required_if:quickEntryType,workorder',
            'quickEntryDescription' => 'required_if:quickEntryType,other',
        ], [
            'quickEntryDate.required' => 'Dato er påkrevd.',
            'quickEntryHours.required' => 'Timer er påkrevd.',
            'quickEntryHours.min' => 'Timer må være minst 0.5.',
            'quickEntryHours.max' => 'Timer kan ikke overstige 24.',
            'quickEntryProjectId.required_if' => 'Velg et prosjekt.',
            'quickEntryWorkOrderId.required_if' => 'Velg en arbeidsordre.',
            'quickEntryDescription.required_if' => 'Beskrivelse er påkrevd.',
        ]);

        try {
            $service = app(TimesheetService::class);

            // Build description with note if provided
            $description = match ($this->quickEntryType) {
                'project', 'workorder' => $this->quickEntryNote,
                'other' => $this->quickEntryDescription.($this->quickEntryNote ? ' - '.$this->quickEntryNote : ''),
            };

            $service->saveEntry($this->timesheet, [
                'entry_date' => $this->quickEntryDate,
                'hours' => $this->quickEntryHours,
                'project_id' => $this->quickEntryType === 'project' ? $this->quickEntryProjectId : null,
                'work_order_id' => $this->quickEntryType === 'workorder' ? $this->quickEntryWorkOrderId : null,
                'description' => $description,
                'is_billable' => true,
            ]);

            $this->timesheet->refresh();
            $this->buildRows();
            $this->closeQuickEntryModal();

            session()->flash('success', 'Timer ble registrert.');
        } catch (TimesheetValidationException $e) {
            $this->addError('quickEntryHours', $e->getMessage());
        }
    }

    protected function resetQuickEntryForm(): void
    {
        $this->quickEntryType = 'project';
        $this->quickEntryProjectId = null;
        $this->quickEntryWorkOrderId = null;
        $this->quickEntryDescription = null;
        $this->quickEntryDate = null;
        $this->quickEntryHours = null;
        $this->quickEntryNote = null;
    }

    public function submit(): void
    {
        if (! $this->timesheet) {
            return;
        }

        $this->authorize('submit', $this->timesheet);

        if ($this->notes) {
            $this->timesheet->update(['notes' => $this->notes]);
        }

        $service = app(TimesheetService::class);
        if ($service->submit($this->timesheet, auth()->user())) {
            session()->flash('success', 'Timeseddelen er sendt til godkjenning.');
        } else {
            session()->flash('error', 'Kunne ikke sende timeseddelen.');
        }

        $this->timesheet->refresh();
        $this->closeSubmitModal();
    }

    protected function resetAddRowForm(): void
    {
        $this->newRowType = null;
        $this->newRowProjectId = null;
        $this->newRowWorkOrderId = null;
        $this->newRowDescription = null;
    }

    public function getDaysProperty(): array
    {
        if (! $this->timesheet) {
            return [];
        }

        return $this->timesheet->getDaysOfWeek();
    }

    public function getTotalHoursForDay(string $date): float
    {
        $total = 0;
        foreach ($this->rows as $row) {
            $total += $row['hours'][$date] ?? 0;
        }

        return $total;
    }

    public function getRowTotal(int $rowIndex): float
    {
        $row = $this->rows[$rowIndex] ?? null;
        if (! $row) {
            return 0;
        }

        return array_sum(array_filter($row['hours'], fn ($h) => $h !== null));
    }

    public function render()
    {
        return view('livewire.timesheet-manager', [
            'projects' => Project::active()->ordered()->get(),
            'workOrders' => WorkOrder::active()->ordered()->get(),
        ]);
    }
}
