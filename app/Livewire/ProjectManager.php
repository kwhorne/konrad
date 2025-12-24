<?php

namespace App\Livewire;

use App\Models\Contact;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectLine;
use App\Models\ProjectStatus;
use App\Models\ProjectType;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectManager extends Component
{
    use WithPagination;

    public $showModal = false;

    public $showLineModal = false;

    public $editingId = null;

    public $search = '';

    public $filterType = '';

    public $filterStatus = '';

    public $filterContact = '';

    // Project form fields
    public $name = '';

    public $description = '';

    public $contact_id = '';

    public $project_type_id = '';

    public $project_status_id = '';

    public $start_date = '';

    public $end_date = '';

    public $budget = '';

    public $estimated_hours = '';

    public $is_active = true;

    // Project line form fields
    public $editingLineId = null;

    public $line_product_id = '';

    public $line_description = '';

    public $line_quantity = 1;

    public $line_unit_price = '';

    public $line_discount_percent = 0;

    // Currently editing project for lines
    public $currentProjectId = null;

    public $projectLines = [];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_id' => 'nullable|exists:contacts,id',
            'project_type_id' => 'nullable|exists:project_types,id',
            'project_status_id' => 'nullable|exists:project_statuses,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    protected function lineRules(): array
    {
        return [
            'line_product_id' => 'nullable|exists:products,id',
            'line_description' => 'nullable|string',
            'line_quantity' => 'required|numeric|min:0.01',
            'line_unit_price' => 'required|numeric|min:0',
            'line_discount_percent' => 'nullable|numeric|min:0|max:100',
        ];
    }

    protected $messages = [
        'name.required' => 'Prosjektnavn er påkrevd.',
        'end_date.after_or_equal' => 'Sluttdato må være etter eller lik startdato.',
        'line_quantity.required' => 'Antall er påkrevd.',
        'line_unit_price.required' => 'Enhetspris er påkrevd.',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterContact(): void
    {
        $this->resetPage();
    }

    public function updatedLineProductId($value): void
    {
        if ($value) {
            $product = Product::find($value);
            if ($product) {
                $this->line_description = $product->name;
                $this->line_unit_price = $product->price;
            }
        }
    }

    public function openModal($id = null): void
    {
        $this->resetForm();

        if ($id) {
            $this->editingId = $id;
            $project = Project::with('lines.product')->findOrFail($id);

            $this->name = $project->name;
            $this->description = $project->description;
            $this->contact_id = $project->contact_id ?? '';
            $this->project_type_id = $project->project_type_id ?? '';
            $this->project_status_id = $project->project_status_id ?? '';
            $this->start_date = $project->start_date?->format('Y-m-d') ?? '';
            $this->end_date = $project->end_date?->format('Y-m-d') ?? '';
            $this->budget = $project->budget;
            $this->estimated_hours = $project->estimated_hours;
            $this->is_active = $project->is_active;
            $this->currentProjectId = $id;
            $this->loadProjectLines();
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
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'contact_id' => $this->contact_id ?: null,
            'project_type_id' => $this->project_type_id ?: null,
            'project_status_id' => $this->project_status_id ?: null,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
            'budget' => $this->budget ?: null,
            'estimated_hours' => $this->estimated_hours ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            $project = Project::findOrFail($this->editingId);
            $project->update($data);
            session()->flash('success', 'Prosjektet ble oppdatert.');
        } else {
            $project = Project::create($data);
            $this->editingId = $project->id;
            $this->currentProjectId = $project->id;
            session()->flash('success', 'Prosjektet ble opprettet.');
        }

        $this->closeModal();
    }

    public function delete($id): void
    {
        Project::findOrFail($id)->delete();
        session()->flash('success', 'Prosjektet ble slettet.');
    }

    public function toggleActive($id): void
    {
        $project = Project::findOrFail($id);
        $project->update(['is_active' => ! $project->is_active]);
    }

    // Project Lines Management
    public function openLineModal($lineId = null): void
    {
        $this->resetLineForm();

        if ($lineId) {
            $this->editingLineId = $lineId;
            $line = ProjectLine::findOrFail($lineId);

            $this->line_product_id = $line->product_id ?? '';
            $this->line_description = $line->description;
            $this->line_quantity = $line->quantity;
            $this->line_unit_price = $line->unit_price;
            $this->line_discount_percent = $line->discount_percent;
        }

        $this->showLineModal = true;
    }

    public function closeLineModal(): void
    {
        $this->showLineModal = false;
        $this->resetLineForm();
    }

    public function saveLine(): void
    {
        $this->validate($this->lineRules());

        $data = [
            'project_id' => $this->currentProjectId,
            'product_id' => $this->line_product_id ?: null,
            'description' => $this->line_description,
            'quantity' => $this->line_quantity,
            'unit_price' => $this->line_unit_price,
            'discount_percent' => $this->line_discount_percent ?? 0,
            'sort_order' => $this->editingLineId
                ? ProjectLine::find($this->editingLineId)->sort_order
                : ProjectLine::where('project_id', $this->currentProjectId)->count(),
        ];

        if ($this->editingLineId) {
            ProjectLine::findOrFail($this->editingLineId)->update($data);
        } else {
            ProjectLine::create($data);
        }

        $this->loadProjectLines();
        $this->closeLineModal();
    }

    public function deleteLine($lineId): void
    {
        ProjectLine::findOrFail($lineId)->delete();
        $this->loadProjectLines();
    }

    private function loadProjectLines(): void
    {
        if ($this->currentProjectId) {
            $this->projectLines = ProjectLine::with('product')
                ->where('project_id', $this->currentProjectId)
                ->ordered()
                ->get()
                ->toArray();
        } else {
            $this->projectLines = [];
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->contact_id = '';
        $this->project_type_id = '';
        $this->project_status_id = '';
        $this->start_date = '';
        $this->end_date = '';
        $this->budget = '';
        $this->estimated_hours = '';
        $this->is_active = true;
        $this->currentProjectId = null;
        $this->projectLines = [];
        $this->resetValidation();
    }

    private function resetLineForm(): void
    {
        $this->editingLineId = null;
        $this->line_product_id = '';
        $this->line_description = '';
        $this->line_quantity = 1;
        $this->line_unit_price = '';
        $this->line_discount_percent = 0;
    }

    public function getStatusColorClass($color): string
    {
        return match ($color) {
            'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
            'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
            'green' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
            'red' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
            'gray' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
            default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
        };
    }

    public function render()
    {
        $query = Project::with(['contact', 'projectType', 'projectStatus', 'lines'])
            ->ordered();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('project_number', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterType) {
            $query->where('project_type_id', $this->filterType);
        }

        if ($this->filterStatus) {
            $query->where('project_status_id', $this->filterStatus);
        }

        if ($this->filterContact) {
            $query->where('contact_id', $this->filterContact);
        }

        return view('livewire.project-manager', [
            'projects' => $query->paginate(15),
            'projectTypes' => ProjectType::active()->ordered()->get(),
            'projectStatuses' => ProjectStatus::active()->ordered()->get(),
            'contacts' => Contact::active()->ordered()->get(),
            'products' => Product::active()->ordered()->get(),
        ]);
    }
}
