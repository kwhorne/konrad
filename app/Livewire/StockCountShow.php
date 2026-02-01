<?php

namespace App\Livewire;

use App\Models\StockCount;
use App\Models\StockCountLine;
use App\Services\StockCountService;
use Livewire\Component;

class StockCountShow extends Component
{
    public StockCount $stockCount;

    public $search = '';

    public $filterStatus = ''; // all, counted, not_counted, variance

    // Count modal
    public bool $showCountModal = false;

    public ?int $countingLineId = null;

    public $counted_quantity = '';

    public $variance_reason = '';

    public function mount(int $stockCountId): void
    {
        $this->stockCount = StockCount::with([
            'stockLocation', 'creator', 'completer', 'poster',
            'lines.product',
        ])->findOrFail($stockCountId);
    }

    public function startCount(StockCountService $service)
    {
        try {
            $service->start($this->stockCount);
            $this->stockCount->refresh();
            $this->dispatch('toast', message: 'Telling startet', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function openCountModal(int $lineId)
    {
        $line = $this->stockCount->lines->find($lineId);
        if (! $line) {
            return;
        }

        $this->countingLineId = $lineId;
        $this->counted_quantity = $line->is_counted ? $line->counted_quantity : '';
        $this->variance_reason = $line->variance_reason ?? '';
        $this->showCountModal = true;
    }

    public function closeCountModal()
    {
        $this->showCountModal = false;
        $this->reset(['countingLineId', 'counted_quantity', 'variance_reason']);
    }

    public function saveCount(StockCountService $service)
    {
        $this->validate([
            'counted_quantity' => 'required|numeric|min:0',
        ], [
            'counted_quantity.required' => 'Antall er påkrevd.',
            'counted_quantity.numeric' => 'Antall må være et tall.',
            'counted_quantity.min' => 'Antall kan ikke være negativt.',
        ]);

        $line = StockCountLine::findOrFail($this->countingLineId);

        try {
            $service->recordLineCount(
                line: $line,
                countedQuantity: (float) $this->counted_quantity,
                varianceReason: $this->variance_reason ?: null
            );

            $this->stockCount->refresh();
            $this->closeCountModal();
            $this->dispatch('toast', message: 'Telling registrert', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function quickCount(int $lineId)
    {
        $line = StockCountLine::findOrFail($lineId);

        if (! $this->stockCount->can_edit) {
            return;
        }

        // Quick count = expected quantity matches
        app(StockCountService::class)->recordLineCount(
            line: $line,
            countedQuantity: (float) $line->expected_quantity,
            varianceReason: null
        );

        $this->stockCount->refresh();
        $this->dispatch('toast', message: 'Talt som forventet', type: 'success');
    }

    public function complete(StockCountService $service)
    {
        try {
            $service->complete($this->stockCount);
            $this->stockCount->refresh();
            $this->dispatch('toast', message: 'Telling fullført', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function post(StockCountService $service)
    {
        try {
            $service->post($this->stockCount);
            $this->stockCount->refresh();
            $this->dispatch('toast', message: 'Telling bokført - lagerjusteringer opprettet', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function cancel(StockCountService $service)
    {
        try {
            $service->cancel($this->stockCount);
            $this->stockCount->refresh();
            $this->dispatch('toast', message: 'Telling kansellert', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function getFilteredLinesProperty()
    {
        $lines = $this->stockCount->lines;

        if ($this->search) {
            $search = strtolower($this->search);
            $lines = $lines->filter(function ($line) use ($search) {
                return str_contains(strtolower($line->product->name), $search) ||
                       str_contains(strtolower($line->product->sku ?? ''), $search);
            });
        }

        return match ($this->filterStatus) {
            'counted' => $lines->where('is_counted', true),
            'not_counted' => $lines->where('is_counted', false),
            'variance' => $lines->where('is_counted', true)->where('variance_quantity', '!=', 0),
            default => $lines,
        };
    }

    public function getSummaryProperty(): array
    {
        return app(StockCountService::class)->getSummary($this->stockCount);
    }

    public function render()
    {
        return view('livewire.stock-count-show', [
            'lines' => $this->filteredLines,
            'summary' => $this->summary,
        ]);
    }
}
