<?php

namespace App\Livewire;

use App\Models\StockCount;
use App\Models\StockCountLine;
use App\Services\StockCountService;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class StockCountShow extends Component
{
    use AuthorizesRequests;

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
        $this->authorize('view', $this->stockCount);
    }

    public function startCount(StockCountService $service)
    {
        $this->authorize('start', $this->stockCount);

        try {
            $service->start($this->stockCount);
            $this->stockCount->refresh();
            Flux::toast(text: 'Telling startet', variant: 'success');
        } catch (\InvalidArgumentException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
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
            Flux::toast(text: 'Telling registrert', variant: 'success');
        } catch (\InvalidArgumentException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
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
        Flux::toast(text: 'Talt som forventet', variant: 'success');
    }

    public function complete(StockCountService $service)
    {
        $this->authorize('complete', $this->stockCount);

        try {
            $service->complete($this->stockCount);
            $this->stockCount->refresh();
            Flux::toast(text: 'Telling fullført', variant: 'success');
        } catch (\InvalidArgumentException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
        }
    }

    public function post(StockCountService $service)
    {
        $this->authorize('post', $this->stockCount);

        try {
            $service->post($this->stockCount);
            $this->stockCount->refresh();
            Flux::toast(text: 'Telling bokført - lagerjusteringer opprettet', variant: 'success');
        } catch (\InvalidArgumentException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
        }
    }

    public function cancel(StockCountService $service)
    {
        $this->authorize('cancel', $this->stockCount);

        try {
            $service->cancel($this->stockCount);
            $this->stockCount->refresh();
            Flux::toast(text: 'Telling kansellert', variant: 'success');
        } catch (\InvalidArgumentException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
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
