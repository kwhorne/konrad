<?php

namespace App\Livewire;

use App\Models\StockCount;
use App\Models\StockLocation;
use App\Rules\ExistsInCompany;
use App\Services\StockCountService;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class StockCountManager extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public $search = '';

    public $filterStatus = '';

    public $filterLocation = '';

    // Create modal
    public bool $showCreateModal = false;

    public $create_location_id = '';

    public $create_description = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['create_location_id', 'create_description']);
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function createCount(StockCountService $service)
    {
        $this->authorize('create', StockCount::class);

        $this->validate([
            'create_location_id' => ['required', new ExistsInCompany('stock_locations')],
        ], [
            'create_location_id.required' => 'Velg en lokasjon.',
        ]);

        $location = StockLocation::findOrFail($this->create_location_id);

        $stockCount = $service->createCount(
            stockLocation: $location,
            description: $this->create_description ?: null
        );

        $this->closeCreateModal();
        Flux::toast(text: 'Varetelling opprettet', variant: 'success');

        return $this->redirect(route('inventory.stock-counts.show', $stockCount), navigate: true);
    }

    public function startCount(int $countId, StockCountService $service): void
    {
        $count = StockCount::findOrFail($countId);
        $this->authorize('start', $count);

        try {
            $service->start($count);
            Flux::toast(text: 'Telling startet', variant: 'success');
        } catch (\InvalidArgumentException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
        }
    }

    public function cancelCount(int $countId, StockCountService $service): void
    {
        $count = StockCount::findOrFail($countId);
        $this->authorize('cancel', $count);

        try {
            $service->cancel($count);
            Flux::toast(text: 'Telling kansellert', variant: 'success');
        } catch (\InvalidArgumentException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
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
