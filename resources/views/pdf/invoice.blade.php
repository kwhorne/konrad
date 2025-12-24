@extends('pdf.layout')

@section('title')
    {{ $invoice->is_credit_note ? 'Kreditnota' : 'Faktura' }} {{ $invoice->invoice_number }}
@endsection

@php
    $isCreditNote = $invoice->is_credit_note;
@endphp

@section('content')
    <!-- Header -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <div class="company-name">{{ $company['name'] }}</div>
                    <div class="company-info">
                        {{ $company['address'] }}<br>
                        {{ $company['postal_code'] }} {{ $company['city'] }}<br>
                        Org.nr: {{ $company['org_number'] }}<br>
                        {{ $company['email'] }}<br>
                        {{ $company['phone'] }}
                    </div>
                </td>
                <td class="header-right">
                    <div class="document-type" @if($isCreditNote) style="color: #7c3aed;" @endif>
                        {{ $isCreditNote ? 'KREDITNOTA' : 'FAKTURA' }}
                    </div>
                    <div class="document-number">{{ $invoice->invoice_number }}</div>
                    @if($invoice->invoiceStatus)
                        <span class="status-badge status-{{ $invoice->invoiceStatus->color ?? 'gray' }}">
                            {{ $invoice->invoiceStatus->name }}
                        </span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="info-cell" style="width: 50%;">
                    <div class="info-label">Faktureres til</div>
                    <div class="info-value">
                        <strong>{{ $invoice->customer_name }}</strong><br>
                        @if($invoice->customer_address){{ $invoice->customer_address }}<br>@endif
                        @if($invoice->customer_postal_code || $invoice->customer_city)
                            {{ $invoice->customer_postal_code }} {{ $invoice->customer_city }}<br>
                        @endif
                        @if($invoice->customer_country && $invoice->customer_country !== 'Norge')
                            {{ $invoice->customer_country }}
                        @endif
                    </div>
                </td>
                <td class="info-cell" style="width: 25%;">
                    <div class="info-label">Fakturadato</div>
                    <div class="info-value">{{ $invoice->invoice_date?->format('d.m.Y') ?? '-' }}</div>

                    <div class="info-label">Forfallsdato</div>
                    <div class="info-value" @if($invoice->is_overdue) style="color: #dc2626; font-weight: bold;" @endif>
                        {{ $invoice->due_date?->format('d.m.Y') ?? '-' }}
                    </div>

                    @if($invoice->customer_reference)
                        <div class="info-label">Deres referanse</div>
                        <div class="info-value">{{ $invoice->customer_reference }}</div>
                    @endif
                </td>
                <td class="info-cell" style="width: 25%;">
                    @if($invoice->our_reference)
                        <div class="info-label">Var referanse</div>
                        <div class="info-value">{{ $invoice->our_reference }}</div>
                    @endif

                    @if($invoice->project)
                        <div class="info-label">Prosjekt</div>
                        <div class="info-value">{{ $invoice->project->name }}</div>
                    @endif

                    @if($isCreditNote && $invoice->originalInvoice)
                        <div class="info-label">Krediterer faktura</div>
                        <div class="info-value" style="color: #7c3aed; font-weight: bold;">{{ $invoice->originalInvoice->invoice_number }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Title and Description -->
    @if($invoice->title)
        <div class="document-title">
            <div class="document-title-label">{{ $isCreditNote ? 'Kreditnotatittel' : 'Fakturatittel' }}</div>
            <div class="document-title-value">{{ $invoice->title }}</div>
            @if($invoice->description)
                <div class="document-description">{{ $invoice->description }}</div>
            @endif
        </div>
    @endif

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45%;">Beskrivelse</th>
                <th class="right" style="width: 10%;">Antall</th>
                <th class="center" style="width: 10%;">Enhet</th>
                <th class="right" style="width: 15%;">Pris</th>
                <th class="right" style="width: 20%;">Sum</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lines as $line)
                <tr>
                    <td>
                        <div class="description">{{ $line->description }}</div>
                        @if($line->discount_percent > 0)
                            <div class="muted">{{ number_format($line->discount_percent, 0) }}% rabatt</div>
                        @endif
                    </td>
                    <td class="right">{{ number_format($line->quantity, 2, ',', ' ') }}</td>
                    <td class="center">{{ $line->unit }}</td>
                    <td class="right">{{ number_format($line->unit_price, 2, ',', ' ') }}</td>
                    <td class="right"><strong>{{ number_format($line->line_total, 2, ',', ' ') }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-wrapper">
        <table class="totals-table">
            <tr>
                <td class="totals-spacer"></td>
                <td class="totals-content">
                    <div class="totals-box">
                        <div class="totals-row">
                            <table class="totals-row-table">
                                <tr>
                                    <td class="totals-label">Sum eks. MVA</td>
                                    <td class="totals-value">{{ number_format($invoice->subtotal, 2, ',', ' ') }}</td>
                                </tr>
                            </table>
                        </div>
                        @if($invoice->discount_total > 0)
                            <div class="totals-row">
                                <table class="totals-row-table">
                                    <tr>
                                        <td class="totals-label">Rabatt</td>
                                        <td class="totals-value">- {{ number_format($invoice->discount_total, 2, ',', ' ') }}</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                        <div class="totals-row">
                            <table class="totals-row-table">
                                <tr>
                                    <td class="totals-label">MVA ({{ $invoice->lines->first()?->vat_percent ?? 25 }}%)</td>
                                    <td class="totals-value">{{ number_format($invoice->vat_total, 2, ',', ' ') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="totals-divider"></div>
                        <div class="totals-row grand-total">
                            <table class="totals-row-table">
                                <tr>
                                    <td class="totals-label">{{ $isCreditNote ? 'Totalt' : 'A betale' }}</td>
                                    <td class="totals-value">NOK {{ number_format($invoice->total, 2, ',', ' ') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- VAT Summary (if multiple rates) -->
    @php
        $vatGroups = $invoice->lines->groupBy('vat_percent');
    @endphp
    @if($vatGroups->count() > 1)
        <div class="vat-summary">
            <div class="vat-summary-title">MVA-spesifikasjon</div>
            @foreach($vatGroups as $rate => $lines)
                <div>
                    {{ $rate }}%: Grunnlag {{ number_format($lines->sum('line_total'), 2, ',', ' ') }} kr,
                    MVA {{ number_format($lines->sum('line_total') * $rate / 100, 2, ',', ' ') }} kr
                </div>
            @endforeach
        </div>
    @endif

    <!-- Payment Status -->
    @if(!$isCreditNote && $invoice->paid_amount > 0)
        <div class="payment-status {{ $invoice->balance <= 0 ? 'paid' : 'partial' }}">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 33%;">
                        <div class="footer-label">Innbetalt</div>
                        <div style="font-weight: bold; font-size: 11pt;">NOK {{ number_format($invoice->paid_amount, 2, ',', ' ') }}</div>
                    </td>
                    <td style="width: 33%;">
                        <div class="footer-label">Restbelop</div>
                        <div style="font-weight: bold; font-size: 11pt;">NOK {{ number_format($invoice->balance, 2, ',', ' ') }}</div>
                    </td>
                    <td style="width: 33%; text-align: right;">
                        @if($invoice->balance <= 0)
                            <span style="background: #22c55e; color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 10pt;">BETALT</span>
                        @else
                            <span style="background: #f59e0b; color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 10pt;">DELVIS BETALT</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        @if(!$isCreditNote)
            <!-- Bank Info -->
            <div class="bank-info">
                <div class="bank-info-title">Betalingsinformasjon</div>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 33%;">
                            <div class="footer-label">Kontonummer</div>
                            <div style="font-weight: bold; font-size: 12pt;">{{ $company['bank_account'] }}</div>
                        </td>
                        <td style="width: 33%;">
                            <div class="footer-label">Forfallsdato</div>
                            <div style="font-weight: bold; font-size: 12pt;">{{ $invoice->due_date?->format('d.m.Y') ?? '-' }}</div>
                        </td>
                        <td style="width: 33%;">
                            <div class="footer-label">A betale</div>
                            <div style="font-weight: bold; font-size: 12pt; color: #4f46e5;">NOK {{ number_format($invoice->balance, 2, ',', ' ') }}</div>
                        </td>
                    </tr>
                </table>
            </div>
        @else
            <!-- Credit Note Info -->
            <div style="padding: 20px; background: #faf5ff; border-radius: 8px; border-left: 4px solid #7c3aed; text-align: center;">
                <div style="font-weight: bold; color: #7c3aed; font-size: 11pt;">
                    Denne kreditnotaen krediterer faktura {{ $invoice->originalInvoice?->invoice_number }}
                </div>
            </div>
        @endif

        @if($invoice->terms_conditions)
            <div class="terms">
                <div class="terms-title">Betalingsbetingelser</div>
                {{ $invoice->terms_conditions }}
            </div>
        @endif
    </div>
@endsection
