<?php

namespace App\Services;

use Illuminate\Support\Collection;

class DocumentTotalsService
{
    /**
     * Calculate totals from a collection of document lines.
     *
     * @param  Collection<int, object>  $lines  Collection of line objects with quantity, unit_price, discount_percent, vat_percent
     * @return array{subtotal: float, discount_total: float, vat_total: float, total: float}
     */
    public function calculate(Collection $lines): array
    {
        $subtotal = 0;
        $discountTotal = 0;
        $vatTotal = 0;

        foreach ($lines as $line) {
            $lineSubtotal = (float) $line->quantity * (float) $line->unit_price;
            $lineDiscount = $lineSubtotal * ((float) ($line->discount_percent ?? 0) / 100);
            $lineNet = $lineSubtotal - $lineDiscount;
            $lineVat = $lineNet * ((float) ($line->vat_percent ?? 0) / 100);

            $subtotal += $lineSubtotal;
            $discountTotal += $lineDiscount;
            $vatTotal += $lineVat;
        }

        $total = $subtotal - $discountTotal + $vatTotal;

        return [
            'subtotal' => round($subtotal, 2),
            'discount_total' => round($discountTotal, 2),
            'vat_total' => round($vatTotal, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Calculate totals from an array of line data.
     *
     * @param  array<int, array{quantity: float|int, unit_price: float|int, discount_percent?: float|int|null, vat_percent?: float|int|null}>  $lines
     * @return array{subtotal: float, discount_total: float, vat_total: float, total: float}
     */
    public function calculateFromArray(array $lines): array
    {
        $subtotal = 0;
        $discountTotal = 0;
        $vatTotal = 0;

        foreach ($lines as $line) {
            $lineSubtotal = (float) $line['quantity'] * (float) $line['unit_price'];
            $lineDiscount = $lineSubtotal * ((float) ($line['discount_percent'] ?? 0) / 100);
            $lineNet = $lineSubtotal - $lineDiscount;
            $lineVat = $lineNet * ((float) ($line['vat_percent'] ?? 0) / 100);

            $subtotal += $lineSubtotal;
            $discountTotal += $lineDiscount;
            $vatTotal += $lineVat;
        }

        $total = $subtotal - $discountTotal + $vatTotal;

        return [
            'subtotal' => round($subtotal, 2),
            'discount_total' => round($discountTotal, 2),
            'vat_total' => round($vatTotal, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Get VAT breakdown grouped by rate.
     *
     * @param  Collection<int, object>  $lines
     * @return array<string, array{rate: float, base: float, vat: float}>
     */
    public function getVatBreakdown(Collection $lines): array
    {
        $breakdown = [];

        foreach ($lines as $line) {
            $vatPercent = (float) ($line->vat_percent ?? 0);
            $key = (string) $vatPercent;

            if (! isset($breakdown[$key])) {
                $breakdown[$key] = [
                    'rate' => $vatPercent,
                    'base' => 0,
                    'vat' => 0,
                ];
            }

            $lineSubtotal = (float) $line->quantity * (float) $line->unit_price;
            $lineDiscount = $lineSubtotal * ((float) ($line->discount_percent ?? 0) / 100);
            $lineNet = $lineSubtotal - $lineDiscount;
            $lineVat = $lineNet * ($vatPercent / 100);

            $breakdown[$key]['base'] += round($lineNet, 2);
            $breakdown[$key]['vat'] += round($lineVat, 2);
        }

        // Sort by rate descending
        uasort($breakdown, fn ($a, $b) => $b['rate'] <=> $a['rate']);

        return $breakdown;
    }
}
