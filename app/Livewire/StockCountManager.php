<?php

namespace App\Livewire;

use App\Models\StockCount;
use App\Models\StockLocation;
use App\Services\StockCountService;
use Livewire\Component;
use Livewire\WithPagination;

class StockCountManager extends Component
{
    use WithPagination;

    public $search = '';

    public $filterStatus = '';

    public $filterLocation = '';

    // Create modal
    public bool $showCreateModal = false;

    public $create_location_id = '';

    public $create_description = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset(['create_location_id', 'create_description']);
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function createCount(StockCountService $service)
    {
        $this->validate([
            'create_location_id' => 'required|exists:stock_locations,id',
        ], [
            'create_location_id.required' => 'Velg en lokasjon.',
        ]);

        $location = StockLocation::findOrFail($this->create_location_id);

        $stockCount = $service->createCount(
            stockLocation: $location,
            description: $this->create_description ?: null
        );

        $this->closeCreateModal();
        $this->dispatch('toast', message: 'Varetelling opprettet', type: 'success');

        return $this->redirect(route('inventory.stock-counts.show', $stockCount), navigate: true);
    }

    public function startCount(int $countId, StockCountService $service)
    {
        $count = StockCount::findOrFail($countId);

        try {
            $service->start($count);
            $this->dispatch('toast', message: 'Telling startet', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function cancelCount(int $countId, StockCountService $service)
    {
        $count = StockCount::findOrFail($countId);

        try {
            $service->cancel($count);
            $this->dispatch('toast', message: 'Telling kansellert', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $companyId = auth()->user()->current_company_id;

        $query = StockCount::where('company_id', $companyId)
            ->with(['stockLocation', 'creator'])
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('count_number', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterLocation, fn ($q) => $q->where('stock_location_id', $this->filterLocation))
            ->ordered();

        $locations = StockLocation::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->get();

        return view('livewire.stock-count-manager', [
            'stockCounts' => $query->paginate(15),
            'locations' => $locations,
        ]);
    }
}
