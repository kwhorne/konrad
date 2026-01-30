<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\OrderStatus;
use App\Models\Quote;
use App\Models\QuoteStatus;

class DocumentConversionService
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {}

    /**
     * Convert a quote to an order.
     *
     * @throws \InvalidArgumentException If the quote cannot be converted
     */
    public function convertQuoteToOrder(Quote $quote): Order
    {
        if (! $quote->can_convert) {
            throw new \InvalidArgumentException('Tilbudet kan ikke konverteres til ordre.');
        }

        $order = Order::create([
            'title' => $quote->title,
            'description' => $quote->description,
            'contact_id' => $quote->contact_id,
            'project_id' => $quote->project_id,
            'quote_id' => $quote->id,
            'created_by' => auth()->id(),
            'order_date' => now(),
            'payment_terms_days' => $quote->payment_terms_days,
            'terms_conditions' => $quote->terms_conditions,
            'customer_name' => $quote->customer_name,
            'customer_address' => $quote->customer_address,
            'customer_postal_code' => $quote->customer_postal_code,
            'customer_city' => $quote->customer_city,
            'customer_country' => $quote->customer_country,
            'subtotal' => $quote->subtotal,
            'discount_total' => $quote->discount_total,
            'vat_total' => $quote->vat_total,
            'total' => $quote->total,
        ]);

        foreach ($quote->lines as $line) {
            OrderLine::create([
                'order_id' => $order->id,
                'quote_line_id' => $line->id,
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit' => $line->unit,
                'unit_price' => $line->unit_price,
                'discount_percent' => $line->discount_percent,
                'vat_rate_id' => $line->vat_rate_id,
                'vat_percent' => $line->vat_percent,
                'sort_order' => $line->sort_order,
            ]);
        }

        // Update quote status to converted
        $convertedStatus = QuoteStatus::where('code', 'converted')->first();
        if ($convertedStatus) {
            $quote->update(['quote_status_id' => $convertedStatus->id]);
        }

        return $order;
    }

    /**
     * Convert an order to an invoice.
     *
     * @throws \InvalidArgumentException If the order cannot be converted
     */
    public function convertOrderToInvoice(Order $order): Invoice
    {
        if (! $order->can_convert) {
            throw new \InvalidArgumentException('Ordren kan ikke konverteres til faktura.');
        }

        $paymentTermsDays = $order->payment_terms_days ?? InvoiceService::DEFAULT_PAYMENT_TERMS_DAYS;
        $reminderDays = InvoiceService::DEFAULT_REMINDER_DAYS;
        $invoiceDate = now();
        $dates = $this->invoiceService->calculateInvoiceDates($invoiceDate, $paymentTermsDays, $reminderDays);

        $invoice = Invoice::create([
            'invoice_type' => 'invoice',
            'title' => $order->title,
            'description' => $order->description,
            'contact_id' => $order->contact_id,
            'project_id' => $order->project_id,
            'order_id' => $order->id,
            'created_by' => auth()->id(),
            'invoice_date' => $invoiceDate,
            'due_date' => $dates['due_date'],
            'payment_terms_days' => $paymentTermsDays,
            'reminder_days' => $reminderDays,
            'reminder_date' => $dates['reminder_date'],
            'terms_conditions' => $order->terms_conditions,
            'customer_name' => $order->customer_name,
            'customer_address' => $order->customer_address,
            'customer_postal_code' => $order->customer_postal_code,
            'customer_city' => $order->customer_city,
            'customer_country' => $order->customer_country,
            'customer_reference' => $order->customer_reference,
            'subtotal' => $order->subtotal,
            'discount_total' => $order->discount_total,
            'vat_total' => $order->vat_total,
            'total' => $order->total,
            'balance' => $order->total,
        ]);

        foreach ($order->lines as $line) {
            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'order_line_id' => $line->id,
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit' => $line->unit,
                'unit_price' => $line->unit_price,
                'discount_percent' => $line->discount_percent,
                'vat_rate_id' => $line->vat_rate_id,
                'vat_percent' => $line->vat_percent,
                'sort_order' => $line->sort_order,
            ]);
        }

        // Update order status to invoiced
        $invoicedStatus = OrderStatus::where('code', 'invoiced')->first();
        if ($invoicedStatus) {
            $order->update(['order_status_id' => $invoicedStatus->id]);
        }

        return $invoice;
    }

    /**
     * Check if a quote can be converted to an order.
     */
    public function canConvertQuote(Quote $quote): bool
    {
        return $quote->can_convert;
    }

    /**
     * Check if an order can be converted to an invoice.
     */
    public function canConvertOrder(Order $order): bool
    {
        return $order->can_convert;
    }
}
