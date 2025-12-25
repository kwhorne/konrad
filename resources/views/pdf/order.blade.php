@extends('pdf.layout')

@section('title')
    Ordre {{ $order->order_number }}
@endsection

@section('content')
    <!-- Header -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    @if(!empty($company['logo_url']))
                        <img src="{{ $company['logo_url'] }}" alt="{{ $company['name'] }}" class="company-logo">
                    @endif
                    <div class="company-name">{{ $company['name'] }}</div>
                    <div class="company-info">
                        @if($company['address']){{ $company['address'] }}<br>@endif
                        @if($company['postal_code'] || $company['city']){{ $company['postal_code'] }} {{ $company['city'] }}<br>@endif
                        @if($company['org_number'])Org.nr: {{ $company['org_number'] }}<br>@endif
                        @if($company['email']){{ $company['email'] }}<br>@endif
                        @if($company['phone']){{ $company['phone'] }}@endif
                    </div>
                </td>
                <td class="header-right">
                    <div class="document-type">ORDRE</div>
                    <div class="document-number">{{ $order->order_number }}</div>
                    @if($order->orderStatus)
                        <span class="status-badge status-{{ $order->orderStatus->color ?? 'gray' }}">
                            {{ $order->orderStatus->name }}
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
                <td class="info-cell" style="width: 35%;">
                    <div class="info-label">Kunde</div>
                    <div class="info-value">
                        <strong>{{ $order->customer_name }}</strong><br>
                        @if($order->customer_address){{ $order->customer_address }}<br>@endif
                        @if($order->customer_postal_code || $order->customer_city)
                            {{ $order->customer_postal_code }} {{ $order->customer_city }}<br>
                        @endif
                        @if($order->customer_country && $order->customer_country !== 'Norge')
                            {{ $order->customer_country }}
                        @endif
                    </div>
                </td>
                <td class="info-cell" style="width: 35%;">
                    @if($order->delivery_address && $order->delivery_address !== $order->customer_address)
                        <div class="info-label">Leveringsadresse</div>
                        <div class="info-value">
                            {{ $order->delivery_address }}<br>
                            @if($order->delivery_postal_code || $order->delivery_city)
                                {{ $order->delivery_postal_code }} {{ $order->delivery_city }}
                            @endif
                        </div>
                    @endif
                </td>
                <td class="info-cell" style="width: 30%;">
                    <div class="info-label">Ordredato</div>
                    <div class="info-value">{{ $order->order_date?->format('d.m.Y') ?? '-' }}</div>

                    @if($order->delivery_date)
                        <div class="info-label">Leveringsdato</div>
                        <div class="info-value">{{ $order->delivery_date->format('d.m.Y') }}</div>
                    @endif

                    @if($order->customer_reference)
                        <div class="info-label">Deres referanse</div>
                        <div class="info-value">{{ $order->customer_reference }}</div>
                    @endif

                    @if($order->project)
                        <div class="info-label">Prosjekt</div>
                        <div class="info-value">{{ $order->project->name }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Title and Description -->
    @if($order->title)
        <div class="document-title">
            <div class="document-title-label">Ordretittel</div>
            <div class="document-title-value">{{ $order->title }}</div>
            @if($order->description)
                <div class="document-description">{{ $order->description }}</div>
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
            @foreach($order->lines as $line)
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
                                    <td class="totals-value">{{ number_format($order->subtotal, 2, ',', ' ') }}</td>
                                </tr>
                            </table>
                        </div>
                        @if($order->discount_total > 0)
                            <div class="totals-row">
                                <table class="totals-row-table">
                                    <tr>
                                        <td class="totals-label">Rabatt</td>
                                        <td class="totals-value">- {{ number_format($order->discount_total, 2, ',', ' ') }}</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                        <div class="totals-row">
                            <table class="totals-row-table">
                                <tr>
                                    <td class="totals-label">MVA ({{ $order->lines->first()?->vat_percent ?? 25 }}%)</td>
                                    <td class="totals-value">{{ number_format($order->vat_total, 2, ',', ' ') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="totals-divider"></div>
                        <div class="totals-row grand-total">
                            <table class="totals-row-table">
                                <tr>
                                    <td class="totals-label">Totalt</td>
                                    <td class="totals-value">NOK {{ number_format($order->total, 2, ',', ' ') }}</td>
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
        $vatGroups = $order->lines->groupBy('vat_percent');
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
                    <div class="footer-value">{{ $order->payment_terms_days ?? 14 }} dager</div>
                </td>
                <td class="footer-box">
                    <div class="footer-label">Leveringsdato</div>
                    <div class="footer-value">{{ $order->delivery_date?->format('d.m.Y') ?? '-' }}</div>
                </td>
                <td class="footer-box">
                    <div class="footer-label">Kontaktperson</div>
                    <div class="footer-value">{{ $order->creator?->name ?? '-' }}</div>
                </td>
            </tr>
        </table>

        @if($order->terms_conditions)
            <div class="terms">
                <div class="terms-title">Vilkar og betingelser</div>
                {{ $order->terms_conditions }}
            </div>
        @endif
    </div>
@endsection
