<?php

namespace App\Livewire;

use App\Models\StockLocation;
use App\Rules\ExistsInCompany;
use Livewire\Component;
use Livewire\WithPagination;

class StockLocationManager extends Component
{
    use WithPagination;

    public $showModal = false;

    public $editingId = null;

    public $search = '';

    public $filterType = '';

    public $filterActive = '';

    // Form fields
    public $code = '';

    public $name = '';

    public $description = '';

    public $address = '';

    public $location_type = 'warehouse';

    public $parent_id = '';

    public $is_active = true;

    protected function rules(): array
    {
        return [
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'address' => 'nullable|string|max:500',
            'location_type' => 'required|in:warehouse,zone,bin',
            'parent_id' => ['nullable', new ExistsInCompany('stock_locations')],
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'code.required' => 'Kode er pakrevd.',
        'name.required' => 'Navn er pakrevd.',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->reset(['editingId', 'code', 'name', 'description', 'address', 'location_type', 'parent_id', 'is_active']);
        $this->is_active = true;
        $this->location_type = 'warehouse';
        $this->showModal = true;
    }

    public function edit(int $id)
    {
        $location = StockLocation::findOrFail($id);
        $this->editingId = $location->id;
        $this->code = $location->code;
        $this->name = $location->name;
        $this->description = $location->description ?? '';
        $this->address = $location->address ?? '';
        $this->location_type = $location->location_type;
        $this->parent_id = $location->parent_id ?? '';
        $this->is_active = $location->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'company_id' => auth()->user()->current_company_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'address' => $this->address ?: null,
            'location_type' => $this->location_type,
            'parent_id' => $this->parent_id ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            $location = StockLocation::findOrFail($this->editingId);
            $location->update($data);
            $this->dispatch('toast', message: 'Lokasjon oppdatert', type: 'success');
        } else {
            StockLocation::create($data);
            $this->dispatch('toast', message: 'Lokasjon opprettet', type: 'success');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'code', 'name', 'description', 'address', 'location_type', 'parent_id']);
    }

    public function delete(int $id)
    {
        $location = StockLocation::findOrFail($id);

        // Check if location has stock
        if ($location->stockLevels()->where('quantity_on_hand', '>', 0)->exists()) {
            $this->dispatch('toast', message: 'Kan ikke slette lokasjon med beholdning', type: 'error');

            return;
        }

        $location->delete();
        $this->dispatch('toast', message: 'Lokasjon slettet', type: 'success');
    }

    public function render()
    {
        $query = StockLocation::where('company_id', auth()->user()->current_company_id)
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('code', 'like', "%{$this->search}%")
                        ->orWhere('name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterType, fn ($q) => $q->where('location_type', $this->filterType))
            ->when($this->filterActive !== '', fn ($q) => $q->where('is_active', $this->filterActive === '1'))
            ->with('parent')
            ->ordered();

        $parentLocations = StockLocation::where('company_id', auth()->user()->current_company_id)
            ->where('is_active', true)
            ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
            ->ordered()
            ->get();

        return view('livewire.stock-location-manager', [
            'locations' => $query->paginate(20),
            'parentLocations' => $parentLocations,
        ]);
    }
}
