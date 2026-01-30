<?php

namespace App\Services;

use App\Models\InvoiceLine;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\QuoteLine;
use App\Models\VatRate;
use Illuminate\Database\Eloquent\Model;

class DocumentLineService
{
    public function __construct(
        private DocumentTotalsService $totalsService
    ) {}

    /**
     * Save a line for a document (invoice, order, or quote).
     *
     * @param  Model  $parent  The parent document (Invoice, Order, or Quote)
     * @param  array{product_id?: int|null, description: string, quantity: float|int, unit: string, unit_price: float|int, discount_percent?: float|int|null, vat_rate_id?: int|null, vat_percent: float|int}  $data
     */
    public function saveLine(Model $parent, array $data, ?int $lineId = null): Model
    {
        $lineClass = $this->getLineClass($parent);
        $foreignKey = $this->getForeignKey($parent);

        $lineData = [
            $foreignKey => $parent->id,
            'product_id' => $data['product_id'] ?? null,
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'unit_price' => $data['unit_price'],
            'discount_percent' => $data['discount_percent'] ?? 0,
            'vat_rate_id' => $data['vat_rate_id'] ?? null,
            'vat_percent' => $data['vat_percent'],
        ];

        if ($lineId) {
            $line = $lineClass::findOrFail($lineId);
            $lineData['sort_order'] = $line->sort_order;
            $line->update($lineData);
        } else {
            $lineData['sort_order'] = $lineClass::where($foreignKey, $parent->id)->count();
            $line = $lineClass::create($lineData);
        }

        $this->recalculateParentTotals($parent);

        return $line;
    }

    /**
     * Delete a document line.
     */
    public function deleteLine(Model $line): void
    {
        $parent = $this->getParent($line);
        $line->delete();

        if ($parent) {
            $this->recalculateParentTotals($parent);
        }
    }

    /**
     * Populate line fields from a product.
     *
     * @return array{description: string, unit_price: float|string, unit: string, vat_rate_id: int|null, vat_percent: float}
     */
    public function populateFromProduct(Product $product): array
    {
        // Load productType with vatRate for proper VAT resolution
        $product->load('productType.vatRate');

        $vatRate = $product->productType?->vatRate;

        return [
            'description' => $product->name,
            'unit_price' => $product->price,
            'unit' => $product->unit?->abbreviation ?? 'stk',
            'vat_rate_id' => $vatRate?->id,
            'vat_percent' => (float) ($vatRate?->rate ?? 0),
        ];
    }

    /**
     * Get the VAT percent from a VatRate.
     */
    public function getVatPercent(?VatRate $vatRate): float
    {
        return (float) ($vatRate?->rate ?? 0);
    }

    /**
     * Get the default VAT rate.
     */
    public function getDefaultVatRate(): ?VatRate
    {
        return VatRate::where('is_default', true)->first() ?? VatRate::first();
    }

    /**
     * Get default line values for a new line.
     *
     * @return array{vat_rate_id: int|null, vat_percent: float, quantity: int, unit: string, discount_percent: int}
     */
    public function getDefaultLineValues(): array
    {
        $defaultVatRate = $this->getDefaultVatRate();

        return [
            'vat_rate_id' => $defaultVatRate?->id,
            'vat_percent' => (float) ($defaultVatRate?->rate ?? 25),
            'quantity' => 1,
            'unit' => 'stk',
            'discount_percent' => 0,
        ];
    }

    /**
     * Recalculate the parent document's totals.
     */
    private function recalculateParentTotals(Model $parent): void
    {
        $parent->load('lines');
        $totals = $this->totalsService->calculate($parent->lines);

        $updateData = [
            'subtotal' => $totals['subtotal'],
            'discount_total' => $totals['discount_total'],
            'vat_total' => $totals['vat_total'],
            'total' => $totals['total'],
        ];

        // For invoices, also update balance
        if ($parent instanceof \App\Models\Invoice) {
            $updateData['balance'] = $totals['total'] - ($parent->paid_amount ?? 0);
        }

        $parent->update($updateData);
    }

    /**
     * Get the line class for a parent document.
     *
     * @return class-string<Model>
     */
    private function getLineClass(Model $parent): string
    {
        return match ($parent::class) {
            \App\Models\Invoice::class => InvoiceLine::class,
            \App\Models\Order::class => OrderLine::class,
            \App\Models\Quote::class => QuoteLine::class,
            default => throw new \InvalidArgumentException('Unknown parent type: '.$parent::class),
        };
    }

    /**
     * Get the foreign key for a parent document.
     */
    private function getForeignKey(Model $parent): string
    {
        return match ($parent::class) {
            \App\Models\Invoice::class => 'invoice_id',
            \App\Models\Order::class => 'order_id',
            \App\Models\Quote::class => 'quote_id',
            default => throw new \InvalidArgumentException('Unknown parent type: '.$parent::class),
        };
    }

    /**
     * Get the parent document from a line.
     */
    private function getParent(Model $line): ?Model
    {
        return match ($line::class) {
            InvoiceLine::class => $line->invoice,
            OrderLine::class => $line->order,
            QuoteLine::class => $line->quote,
            default => null,
        };
    }
}
