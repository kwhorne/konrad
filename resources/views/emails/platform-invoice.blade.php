<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktura {{ $invoiceNumber }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
            margin: 0;
        }
        .invoice-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #4f46e5;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .invoice-number {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .invoice-amount {
            font-size: 28px;
            font-weight: bold;
            color: #1d4ed8;
            margin-bottom: 8px;
        }
        .invoice-due {
            font-size: 14px;
            color: #374151;
        }
        .invoice-due strong {
            color: #111827;
        }
        .bank-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 16px 0;
            font-size: 14px;
        }
        .bank-box-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        .bank-row {
            display: flex;
            margin-bottom: 4px;
        }
        .bank-label {
            color: #6b7280;
            min-width: 130px;
        }
        .bank-value {
            font-weight: 600;
            color: #111827;
        }
        .footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            margin-top: 30px;
            font-size: 13px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="company-name">Konrad Office AS</p>
    </div>

    <p>Hei {{ $companyName }},</p>

    <p>Vedlagt finner du faktura for bruk av Konrad Office-plattformen. Vennligst betal innen forfallsdato.</p>

    <div class="invoice-box">
        <div class="invoice-number">Faktura {{ $invoiceNumber }}</div>
        <div class="invoice-amount">{{ $amountFormatted }}</div>
        <div class="invoice-due">Forfallsdato: <strong>{{ $dueDateFormatted }}</strong></div>
    </div>

    <div class="bank-box">
        <div class="bank-box-title">Betalingsinformasjon</div>
        <div class="bank-row">
            <span class="bank-label">Kontonummer:</span>
            <span class="bank-value">{{ config('company.bank_account') }}</span>
        </div>
        <div class="bank-row">
            <span class="bank-label">Fakturanr. (KID):</span>
            <span class="bank-value">{{ $invoiceNumber }}</span>
        </div>
        <div class="bank-row">
            <span class="bank-label">Beløp:</span>
            <span class="bank-value">{{ $amountFormatted }}</span>
        </div>
    </div>

    <p>Fakturaen er vedlagt som PDF. Ta kontakt på <a href="mailto:{{ config('company.email') }}">{{ config('company.email') }}</a> hvis du har spørsmål.</p>

    <p>Med vennlig hilsen,<br>
    <strong>Konrad Office AS</strong></p>

    <div class="footer">
        <p>Konrad Office AS &bull; Org.nr {{ config('company.org_number') }} &bull; {{ config('company.address') }}, {{ config('company.postal_code') }} {{ config('company.city') }}</p>
    </div>
</body>
</html>
