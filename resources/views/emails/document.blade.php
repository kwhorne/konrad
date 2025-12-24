<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ucfirst($documentType) }} {{ $documentNumber }}</title>
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
        .document-info {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .document-number {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
        }
        .footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            margin-top: 30px;
            font-size: 14px;
            color: #6b7280;
        }
        .footer-company {
            margin-bottom: 10px;
        }
        .footer-contact {
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="company-name">{{ $companyName }}</p>
    </div>

    <div class="content">
        <p>Hei {{ $customerName }},</p>

        <p>Vedlagt finner du {{ $documentType }} <strong>{{ $documentNumber }}</strong> fra {{ $companyName }}.</p>

        <div class="document-info">
            <p class="document-number">{{ ucfirst($documentType) }}: {{ $documentNumber }}</p>
            <p>Se vedlagt PDF for detaljer.</p>
        </div>

        @if($documentType === 'faktura')
            <p>Vennligst betal fakturaen innen forfallsdatoen angitt i dokumentet.</p>
        @elseif($documentType === 'tilbud')
            <p>Ta gjerne kontakt hvis du har sporsmul eller onsker a akseptere tilbudet.</p>
        @elseif($documentType === 'ordre')
            <p>Ta kontakt hvis du har sporsmul angaende ordren.</p>
        @endif

        <p>Med vennlig hilsen,<br>{{ $companyName }}</p>
    </div>

    <div class="footer">
        <div class="footer-company">
            <strong>{{ $company['name'] }}</strong>
        </div>
        <div class="footer-contact">
            {{ $company['address'] }}, {{ $company['postal_code'] }} {{ $company['city'] }}<br>
            Org.nr: {{ $company['org_number'] }}<br>
            E-post: {{ $company['email'] }} | Tlf: {{ $company['phone'] }}
        </div>
    </div>
</body>
</html>
