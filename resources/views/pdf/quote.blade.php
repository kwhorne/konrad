@extends('pdf.layout')

@section('title')
    Tilbud {{ $quote->quote_number }}
@endsection

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
                    <div class="document-type">TILBUD</div>
                    <div class="document-number">{{ $quote->quote_number }}</div>
                    @if($quote->quoteStatus)
                        <span class="status-badge status-{{ $quote->quoteStatus->color ?? 'gray' }}">
                            {{ $quote->quoteStatus->name }}
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
                    <div class="info-label">Kunde</div>
                    <div class="info-value">
                        <strong>{{ $quote->customer_name }}</strong><br>
                        @if($quote->customer_address){{ $quote->customer_address }}<br>@endif
                        @if($quote->customer_postal_code || $quote->customer_city)
                            {{ $quote->customer_postal_code }} {{ $quote->customer_city }}<br>
                        @endif
                        @if($quote->customer_country && $quote->customer_country !== 'Norge')
                            {{ $quote->customer_country }}
                        @endif
                    </div>
                </td>
                <td class="info-cell" style="width: 25%;">
                    <div class="info-label">Tilbudsdato</div>
                    <div class="info-value">{{ $quote->quote_date?->format('d.m.Y') ?? '-' }}</div>

                    <div class="info-label">Gyldig til</div>
                    <div class="info-value">{{ $quote->valid_until?->format('d.m.Y') ?? '-' }}</div>
                </td>
                <td class="info-cell" style="width: 25%;">
                    @if($quote->project)
                        <div class="info-label">Prosjekt</div>
                        <div class="info-value">{{ $quote->project->name }}</div>
                    @endif

                    @if($quote->our_reference)
                        <div class="info-label">Var referanse</div>
                        <div class="info-value">{{ $quote->our_reference }}</div>
                    @endif

                    @if($quote->customer_reference)
                        <div class="info-label">Deres referanse</div>
                        <div class="info-value">{{ $quote->customer_reference }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Title and Description -->
    @if($quote->title)
        <div class="document-title">
            <div class="document-title-label">Tilbudstittel</div>
            <div class="document-title-value">{{ $quote->title }}</div>
            @if($quote->description)
                <div class="document-description">{{ $quote->description }}</div>
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
            @foreach($quote->lines as $line)
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
                                    <td class="totals-value">{{ number_format($quote->subtotal, 2, ',', ' ') }}</td>
                                </tr>
                            </table>
                        </div>
                        @if($quote->discount_total > 0)
                            <div class="totals-row">
                                <table class="totals-row-table">
                                    <tr>
                                        <td class="totals-label">Rabatt</td>
                                        <td class="totals-value">- {{ number_format($quote->discount_total, 2, ',', ' ') }}</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                        <div class="totals-row">
                            <table class="totals-row-table">
                                <tr>
                                    <td class="totals-label">MVA ({{ $quote->lines->first()?->vat_percent ?? 25 }}%)</td>
                                    <td class="totals-value">{{ number_format($quote->vat_total, 2, ',', ' ') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="totals-divider"></div>
                        <div class="totals-row grand-total">
                            <table class="totals-row-table">
                                <tr>
                                    <td class="totals-label">Totalt</td>
                                    <td class="totals-value">NOK {{ number_format($quote->total, 2, ',', ' ') }}</td>
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
        $vatGroups = $quote->lines->groupBy('vat_percent');
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

    <!-- Footer -->
    <div class="footer">
        <table class="footer-boxes">
            <tr>
                <td class="footer-box">
                    <div class="footer-label">Betalingsbetingelser</div>
                    <div class="footer-value">{{ $quote->payment_terms_days ?? 14 }} dager</div>
                </td>
                <td class="footer-box">
                    <div class="footer-label">Gyldig til</div>
                    <div class="footer-value">{{ $quote->valid_until?->format('d.m.Y') ?? '-' }}</div>
                </td>
                <td class="footer-box">
                    <div class="footer-label">Kontaktperson</div>
                    <div class="footer-value">{{ $quote->creator?->name ?? '-' }}</div>
                </td>
            </tr>
        </table>

        @if($quote->terms_conditions)
            <div class="terms">
                <div class="terms-title">Vilkar og betingelser</div>
                {{ $quote->terms_conditions }}
            </div>
        @endif
    </div>
@endsection
