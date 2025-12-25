<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title')</title>
    <style>
        @page {
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            line-height: 1.5;
            color: #374151;
            background: #fff;
        }
        .page {
            padding: 40px 50px;
            min-height: 100%;
        }

        /* Header with accent bar */
        .header-bar {
            height: 6px;
            background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
            margin: -40px -50px 30px -50px;
        }
        .credit-note .header-bar {
            background: linear-gradient(90deg, #7c3aed 0%, #a855f7 100%);
        }

        /* Header layout */
        .header {
            margin-bottom: 35px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-left {
            vertical-align: top;
            width: 55%;
        }
        .header-right {
            vertical-align: top;
            width: 45%;
            text-align: right;
        }
        .company-logo {
            max-height: 60px;
            max-width: 200px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 20pt;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        .credit-note .company-name {
            color: #7c3aed;
        }
        .company-info {
            font-size: 8.5pt;
            color: #6b7280;
            line-height: 1.6;
        }
        .document-type {
            font-size: 28pt;
            font-weight: bold;
            color: #111827;
            letter-spacing: -1px;
            margin-bottom: 8px;
        }
        .document-number {
            font-size: 13pt;
            font-weight: 600;
            color: #4f46e5;
            margin-bottom: 10px;
        }
        .credit-note .document-number {
            color: #7c3aed;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-blue { background-color: #eff6ff; color: #1d4ed8; }
        .status-green { background-color: #f0fdf4; color: #15803d; }
        .status-yellow { background-color: #fffbeb; color: #b45309; }
        .status-red { background-color: #fef2f2; color: #dc2626; }
        .status-purple { background-color: #faf5ff; color: #7c3aed; }
        .status-gray { background-color: #f3f4f6; color: #4b5563; }

        /* Info section */
        .info-section {
            margin-bottom: 30px;
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-cell {
            vertical-align: top;
            padding-right: 25px;
        }
        .info-cell:last-child {
            padding-right: 0;
        }
        .info-label {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #9ca3af;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 9.5pt;
            color: #111827;
            margin-bottom: 12px;
        }
        .info-value strong {
            font-weight: 600;
        }

        /* Document title */
        .document-title {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        .document-title-label {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #9ca3af;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .document-title-value {
            font-size: 13pt;
            font-weight: 600;
            color: #111827;
        }
        .document-description {
            font-size: 9pt;
            color: #6b7280;
            margin-top: 5px;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items-table th {
            background-color: #111827;
            color: #fff;
            padding: 12px 10px;
            text-align: left;
            font-size: 8pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table th:first-child {
            border-radius: 6px 0 0 0;
        }
        .items-table th:last-child {
            border-radius: 0 6px 0 0;
        }
        .items-table th.right {
            text-align: right;
        }
        .items-table th.center {
            text-align: center;
        }
        .items-table td {
            padding: 14px 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 9pt;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .items-table td.right {
            text-align: right;
        }
        .items-table td.center {
            text-align: center;
        }
        .items-table .description {
            font-weight: 500;
            color: #111827;
        }
        .items-table .muted {
            color: #6b7280;
            font-size: 8.5pt;
        }

        /* Totals */
        .totals-wrapper {
            width: 100%;
            margin-bottom: 30px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-spacer {
            width: 55%;
        }
        .totals-content {
            width: 45%;
            vertical-align: top;
        }
        .totals-box {
            background: #f9fafb;
            border-radius: 8px;
            padding: 15px 20px;
        }
        .totals-row {
            padding: 6px 0;
        }
        .totals-row-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-label {
            text-align: left;
            color: #6b7280;
            font-size: 9pt;
        }
        .totals-value {
            text-align: right;
            font-weight: 600;
            font-size: 9.5pt;
            color: #111827;
        }
        .totals-divider {
            border-top: 2px solid #111827;
            margin: 10px 0;
        }
        .grand-total .totals-label {
            font-size: 11pt;
            font-weight: 600;
            color: #111827;
        }
        .grand-total .totals-value {
            font-size: 14pt;
            font-weight: bold;
            color: #4f46e5;
        }
        .credit-note .grand-total .totals-value {
            color: #7c3aed;
        }

        /* VAT Summary */
        .vat-summary {
            font-size: 8pt;
            color: #6b7280;
            margin-bottom: 25px;
            padding: 12px 15px;
            background: #fafafa;
            border-radius: 6px;
        }
        .vat-summary-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #374151;
        }

        /* Footer info boxes */
        .footer {
            margin-top: 35px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }
        .footer-boxes {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .footer-box {
            width: 33.33%;
            padding: 15px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .footer-box:first-child {
            border-radius: 8px 0 0 8px;
        }
        .footer-box:last-child {
            border-radius: 0 8px 8px 0;
        }
        .footer-label {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #9ca3af;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .footer-value {
            font-size: 11pt;
            font-weight: 600;
            color: #111827;
        }

        /* Bank info for invoices */
        .bank-info {
            margin-top: 20px;
            padding: 20px;
            background: #eff6ff;
            border-radius: 8px;
            border-left: 4px solid #4f46e5;
        }
        .bank-info-title {
            font-size: 9pt;
            font-weight: bold;
            color: #1d4ed8;
            margin-bottom: 10px;
        }
        .bank-info-row {
            margin-bottom: 5px;
        }
        .bank-info-label {
            font-weight: 600;
            color: #374151;
        }

        /* Terms */
        .terms {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 20px;
            padding: 15px;
            background: #fafafa;
            border-radius: 6px;
        }
        .terms-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #374151;
        }

        /* Page break */
        .page-break {
            page-break-after: always;
        }

        /* Payment status for invoices */
        .payment-status {
            margin-top: 15px;
            padding: 15px;
            border-radius: 8px;
        }
        .payment-status.paid {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
        }
        .payment-status.partial {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
        }
        .payment-status.unpaid {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
        }
    </style>
</head>
<body @if(isset($isCreditNote) && $isCreditNote) class="credit-note" @endif>
    <div class="page">
        <div class="header-bar"></div>
        @yield('content')
    </div>
</body>
</html>
