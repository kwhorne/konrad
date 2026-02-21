@extends('pdf.layout')

@section('title', 'Faktura ' . $invoice->invoice_number)

@section('content')
<table class="header-table">
    <tr>
        <td class="header-left">
            <div class="company-name">{{ $sender['name'] }}</div>
            <div class="company-info">
                {{ $sender['address'] }}<br>
                {{ $sender['postal_code'] }} {{ $sender['city'] }}<br>
                Org.nr: {{ $sender['org_number'] }}<br>
                {{ $sender['email'] }}
            </div>
        </td>
        <td class="header-right">
            <div class="document-type">FAKTURA</div>
            <div class="document-number">{{ $invoice->invoice_number }}</div>
        </td>
    </tr>
</table>

{{-- Info row --}}
<div class="info-section">
    <table class="info-table">
        <tr>
            <td class="info-cell" style="width:30%">
                <div class="info-label">Fakturadato</div>
                <div class="info-value">{{ $invoice->created_at->format('d.m.Y') }}</div>
            </td>
            <td class="info-cell" style="width:30%">
                <div class="info-label">Forfallsdato</div>
                <div class="info-value"><strong>{{ $invoice->due_date->format('d.m.Y') }}</strong></div>
            </td>
            <td class="info-cell" style="width:40%">
                <div class="info-label">Fakturert til</div>
                <div class="info-value">
                    <strong>{{ $company->name }}</strong><br>
                    @if($company->organization_number)
                        Org.nr: {{ $company->formatted_organization_number }}<br>
                    @endif
                    @if($company->full_address)
                        {{ $company->full_address }}
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- Items --}}
<table class="items-table">
    <thead>
        <tr>
            <th style="width:70%">Beskrivelse</th>
            <th class="right">Beløp</th>
        </tr>
    </thead>
    <tbody>
        @if($lines->isNotEmpty())
            @foreach($lines as $line)
                <tr>
                    <td><span class="description">{{ $line['name'] }}</span></td>
                    <td class="right">{{ number_format($line['amount'] / 100, 0, ',', ' ') }} kr</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td><span class="description">{{ $invoice->description }}</span></td>
                <td class="right">{{ $invoice->amount_formatted }}</td>
            </tr>
        @endif
    </tbody>
</table>

{{-- Totals --}}
<div class="totals-wrapper">
    <table class="totals-table">
        <tr>
            <td class="totals-spacer"></td>
            <td class="totals-content">
                <div class="totals-box">
                    <div class="totals-row grand-total">
                        <table class="totals-row-table">
                            <tr>
                                <td class="totals-label">Å betale</td>
                                <td class="totals-value">{{ $invoice->amount_formatted }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- Bank info --}}
<div class="bank-info">
    <div class="bank-info-title">Betalingsinformasjon</div>
    <div class="bank-info-row">
        <span class="bank-info-label">Kontonummer:</span> {{ $sender['bank_account'] }}
    </div>
    <div class="bank-info-row">
        <span class="bank-info-label">Fakturanr. (KID):</span> {{ $invoice->invoice_number }}
    </div>
    <div class="bank-info-row">
        <span class="bank-info-label">Forfallsdato:</span> {{ $invoice->due_date->format('d.m.Y') }}
    </div>
</div>

@if($invoice->notes)
<div class="terms" style="margin-top:12px">
    <div class="terms-title">Notater</div>
    {{ $invoice->notes }}
</div>
@endif
@endsection
