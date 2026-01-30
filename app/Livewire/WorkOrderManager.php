<?php

namespace App\Livewire;

use App\Livewire\Traits\HasStatusColorMapping;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use App\Models\WorkOrderPriority;
use App\Models\WorkOrderStatus;
use App\Models\WorkOrderType;
use App\Services\WorkOrderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class WorkOrderManager extends Component
{
    use AuthorizesRequests, HasStatusColorMapping, WithPagination;

    public $showModal = false;

    public $showLineModal = false;

    public $editingId = null;

    public $search = '';

    public $filterStatus = '';

    public $filterPriority = '';

    public $filterType = '';

    public $filterAssigned = '';

    // Work order form fields
    public $title = '';

    public $description = '';

    public $contact_id = '';

    public $project_id = '';

    public $work_order_type_id = '';

    public $work_order_status_id = '';

    public $work_order_priority_id = '';

    public $assigned_to = '';

    public $scheduled_date = '';

    public $due_date = '';

    public $estimated_hours = '';

    public $budget = '';

    public $internal_notes = '';

    public $is_active = true;

    // Line form fields
    public $editingLineId = null;

    public $line_type = 'time';

    public $line_product_id = '';

    public $line_description = '';

    public $line_quantity = 1;

    public $line_unit_price = '';

    public $line_discount_percent = 0;

    public $line_performed_at = '';

    public $line_performed_by = '';

    // Currently editing work order for lines
    public $currentWorkOrderId = null;

    public $workOrderLines = [];

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_id' => 'nullable|exists:contacts,id',
            'project_id' => 'nullable|exists:projects,id',
            'work_order_type_id' => 'nullable|exists:work_order_types,id',
            'work_order_status_id' => 'nullable|exists:work_order_statuses,id',
            'work_order_priority_id' => 'nullable|exists:work_order_priorities,id',
            'assigned_to' => 'nullable|exists:users,id',
            'scheduled_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:scheduled_date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'budget' => 'nullable|numeric|min:0',
            'internal_notes' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    protected function lineRules(): array
    {
        return [
            'line_type' => 'required|in:time,product',
            'line_product_id' => 'nullable|exists:products,id',
            'line_description' => 'nullable|string',
            'line_quantity' => 'required|numeric|min:0.01',
            'line_unit_price' => 'required|numeric|min:0',
            'line_discount_percent' => 'nullable|numeric|min:0|max:100',
            'line_performed_at' => 'nullable|date',
            'line_performed_by' => 'nullable|exists:users,id',
        ];
    }

    protected $messages = [
        'title.required' => 'Tittel er påkrevd.',
        'due_date.after_or_equal' => 'Forfallsdato må være etter eller lik planlagt dato.',
        'line_quantity.required' => 'Antall er påkrevd.',
        'line_unit_price.required' => 'Pris er påkrevd.',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPriority(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterAssigned(): void
    {
        $this->resetPage();
    }

    public function updatedLineProductId($value, WorkOrderService $service): void
    {
        if ($value) {
            $product = Product::find($value);
            if ($product) {
                $data = $service->populateFromProduct($product);
                $this->line_description = $data['description'];
                $this->line_unit_price = $data['unit_price'];
            }
        }
    }

    public function updatedLineType($value, WorkOrderService $service): void
    {
        if ($value === 'time') {
            $defaults = $service->getTimeEntryDefaults();
            $this->line_product_id = '';
            $this->line_performed_at = $defaults['performed_at'];
            $this->line_performed_by = $defaults['performed_by'];
        } else {
            $defaults = $service->getProductEntryDefaults();
            $this->line_performed_at = $defaults['performed_at'] ?? '';
            $this->line_performed_by = $defaults['performed_by'] ?? '';
        }
    }

    public function openModal($id = null): void
    {
        if ($id) {
            $workOrder = WorkOrder::findOrFail($id);
            $this->authorize('view', $workOrder);
        } else {
            $this->authorize('create', WorkOrder::class);
        }

        $this->resetForm();

        if ($id) {
            $this->editingId = $id;
            $workOrder = WorkOrder::with('lines.product')->findOrFail($id);

            $this->title = $workOrder->title;
            $this->description = $workOrder->description;
            $this->contact_id = $workOrder->contact_id ?? '';
            $this->project_id = $workOrder->project_id ?? '';
            $this->work_order_type_id = $workOrder->work_order_type_id ?? '';
            $this->work_order_status_id = $workOrder->work_order_status_id ?? '';
            $this->work_order_priority_id = $workOrder->work_order_priority_id ?? '';
            $this->assigned_to = $workOrder->assigned_to ?? '';
            $this->scheduled_date = $workOrder->scheduled_date?->format('Y-m-d') ?? '';
            $this->due_date = $workOrder->due_date?->format('Y-m-d') ?? '';
            $this->estimated_hours = $workOrder->estimated_hours;
            $this->budget = $workOrder->budget;
            $this->internal_notes = $workOrder->internal_notes;
            $this->is_active = $workOrder->is_active;
            $this->currentWorkOrderId = $id;
            $this->loadWorkOrderLines();
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

        if ($this->editingId) {
            $workOrder = WorkOrder::findOrFail($this->editingId);
            $this->authorize('update', $workOrder);
        } else {
            $this->authorize('create', WorkOrder::class);
        }

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'contact_id' => $this->contact_id ?: null,
            'project_id' => $this->project_id ?: null,
            'work_order_type_id' => $this->work_order_type_id ?: null,
            'work_order_status_id' => $this->work_order_status_id ?: null,
            'work_order_priority_id' => $this->work_order_priority_id ?: null,
            'assigned_to' => $this->assigned_to ?: null,
            'scheduled_date' => $this->scheduled_date ?: null,
            'due_date' => $this->due_date ?: null,
            'estimated_hours' => $this->estimated_hours ?: null,
            'budget' => $this->budget ?: null,
            'internal_notes' => $this->internal_notes,
            'is_active' => $this->is_active,
            'created_by' => $this->editingId ? $workOrder->created_by : auth()->id(),
        ];

        if ($this->editingId) {
            $workOrder->update($data);
            session()->flash('success', 'Arbeidsordren ble oppdatert.');
        } else {
            $workOrder = WorkOrder::create($data);
            $this->editingId = $workOrder->id;
            $this->currentWorkOrderId = $workOrder->id;
            session()->flash('success', 'Arbeidsordren ble opprettet.');
        }

        $this->closeModal();
    }

    public function delete($id): void
    {
        $workOrder = WorkOrder::findOrFail($id);
        $this->authorize('delete', $workOrder);

        $workOrder->delete();
        session()->flash('success', 'Arbeidsordren ble slettet.');
    }

    public function toggleActive($id): void
    {
        $workOrder = WorkOrder::findOrFail($id);
        $this->authorize('update', $workOrder);

        $workOrder->update(['is_active' => ! $workOrder->is_active]);
    }

    // Work Order Lines Management
    public function openLineModal($lineId = null): void
    {
        $this->resetLineForm();

        if ($lineId) {
            $this->editingLineId = $lineId;
            $line = WorkOrderLine::findOrFail($lineId);

            $this->line_type = $line->line_type;
            $this->line_product_id = $line->product_id ?? '';
            $this->line_description = $line->description;
            $this->line_quantity = $line->quantity;
            $this->line_unit_price = $line->unit_price;
            $this->line_discount_percent = $line->discount_percent;
            $this->line_performed_at = $line->performed_at?->format('Y-m-d') ?? '';
            $this->line_performed_by = $line->performed_by ?? '';
        } else {
            $this->line_performed_at = date('Y-m-d');
            $this->line_performed_by = auth()->id();
        }

        $this->showLineModal = true;
    }

    public function closeLineModal(): void
    {
        $this->showLineModal = false;
        $this->resetLineForm();
    }

    public function saveLine(WorkOrderService $service): void
    {
        $this->validate($this->lineRules());

        $workOrder = WorkOrder::findOrFail($this->currentWorkOrderId);
        $this->authorize('update', $workOrder);

        $service->saveLine($workOrder, [
            'line_type' => $this->line_type,
            'product_id' => $this->line_product_id,
            'description' => $this->line_description,
            'quantity' => $this->line_quantity,
            'unit_price' => $this->line_unit_price,
            'discount_percent' => $this->line_discount_percent,
            'performed_at' => $this->line_performed_at,
            'performed_by' => $this->line_performed_by,
        ], $this->editingLineId);

        $this->loadWorkOrderLines();
        $this->closeLineModal();
    }

    public function deleteLine($lineId, WorkOrderService $service): void
    {
        $line = WorkOrderLine::findOrFail($lineId);
        $this->authorize('update', $line->workOrder);

        $service->deleteLine($line);
        $this->loadWorkOrderLines();
    }

    private function loadWorkOrderLines(): void
    {
        if ($this->currentWorkOrderId) {
            $this->workOrderLines = WorkOrderLine::with(['product', 'performedByUser'])
                ->where('work_order_id', $this->currentWorkOrderId)
                ->ordered()
                ->get()
                ->toArray();
        } else {
            $this->workOrderLines = [];
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->description = '';
        $this->contact_id = '';
        $this->project_id = '';
        $this->work_order_type_id = '';
        $this->work_order_status_id = '';
        $this->work_order_priority_id = '';
        $this->assigned_to = '';
        $this->scheduled_date = '';
        $this->due_date = '';
        $this->estimated_hours = '';
        $this->budget = '';
        $this->internal_notes = '';
        $this->is_active = true;
        $this->currentWorkOrderId = null;
        $this->workOrderLines = [];
        $this->resetValidation();
    }

    private function resetLineForm(): void
    {
        $this->editingLineId = null;
        $this->line_type = 'time';
        $this->line_product_id = '';
        $this->line_description = '';
        $this->line_quantity = 1;
        $this->line_unit_price = '';
        $this->line_discount_percent = 0;
        $this->line_performed_at = '';
        $this->line_performed_by = '';
    }

    public function render()
    {
        $query = WorkOrder::with([
            'contact',
            'project',
            'workOrderType',
            'workOrderStatus',
            'workOrderPriority',
            'assignedUser',
            'lines',
        ])->ordered();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('work_order_number', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterStatus) {
            $query->where('work_order_status_id', $this->filterStatus);
        }

        if ($this->filterPriority) {
            $query->where('work_order_priority_id', $this->filterPriority);
        }

        if ($this->filterType) {
            $query->where('work_order_type_id', $this->filterType);
        }

        if ($this->filterAssigned) {
            $query->where('assigned_to', $this->filterAssigned);
        }

        return view('livewire.work-order-manager', [
            'workOrders' => $query->paginate(15),
            'statuses' => WorkOrderStatus::active()->ordered()->get(),
            'priorities' => WorkOrderPriority::active()->ordered()->get(),
            'types' => WorkOrderType::active()->ordered()->get(),
            'contacts' => Contact::active()->ordered()->get(),
            'projects' => Project::active()->ordered()->get(),
            'users' => User::orderBy('name')->get(),
            'products' => Product::active()->ordered()->get(),
        ]);
    }
}
