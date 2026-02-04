<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lønnsslipp {{ $periodLabel }}</title>
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
        .content {
            margin-bottom: 30px;
        }
        .payslip-info {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .payslip-period {
            font-size: 18px;
            font-weight: 600;
            color: #166534;
            margin-bottom: 10px;
        }
        .payslip-amount {
            font-size: 24px;
            font-weight: bold;
            color: #15803d;
        }
        .payslip-date {
            font-size: 14px;
            color: #6b7280;
            margin-top: 10px;
        }
        .password-info {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .password-info-title {
            font-weight: 600;
            color: #92400e;
            margin-bottom: 5px;
        }
        .password-info-text {
            font-size: 14px;
            color: #78350f;
        }
        .footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            margin-top: 30px;
            font-size: 14px;
            color: #6b7280;
        }
        .confidential {
            font-size: 12px;
            color: #9ca3af;
            font-style: italic;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="company-name">{{ $companyName }}</p>
    </div>

    <div class="content">
        <p>Hei {{ $employeeName }},</p>

        <p>Vedlagt finner du din lønnsslipp for {{ $periodLabel }}.</p>

        <div class="payslip-info">
            <p class="payslip-period">Lønnsslipp: {{ $periodLabel }}</p>
            <p class="payslip-amount">Netto utbetalt: {{ $nettolonn }}</p>
            <p class="payslip-date">Utbetalingsdato: {{ $paymentDate }}</p>
        </div>

        @if($hasPassword)
        <div class="password-info">
            <p class="password-info-title">🔒 PDF-en er passordbeskyttet</p>
            <p class="password-info-text">
                For å åpne lønnsslippen, bruk de 5 siste sifrene i ditt personnummer som passord.
            </p>
        </div>
        @endif

        <p>Ta kontakt med lønnsavdelingen hvis du har spørsmål.</p>

        <p>Med vennlig hilsen,<br>{{ $companyName }}</p>
    </div>

    <div class="footer">
        <p class="confidential">
            Denne e-posten inneholder konfidensiell informasjon og er kun ment for mottakeren.
            Hvis du har mottatt denne e-posten ved en feil, vennligst slett den og gi beskjed til avsender.
        </p>
    </div>
</body>
</html>
