<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->inv_no }}</title>
    <style>
        @page { 
            size: A4 portrait; 
            margin: 12mm 15mm 50mm 15mm; /* Tambah margin bawah kepada 50mm supaya ada ruang selamat */
        }
        body { 
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif; 
            color: #333;
            margin: 0; 
            padding: 0; 
            font-size: 13px;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 3px solid #000;
            padding-bottom: 12px;
        }
        .header-left, .header-right {
            display: table-cell;
            vertical-align: top;
        }
        .header-left { width: 55%; }
        .header-right { width: 45%; text-align: left; }
        
        .logo { max-width: 180px; margin-bottom: 10px; display: block; }
        .company-name { font-size: 16px; font-weight: bold; margin: 0 0 2px 0; color: #000; }
        .company-details { font-size: 13px; color: #000; line-height: 1.3; }

        .invoice-title { font-size: 38px; font-weight: 900; color: #000; letter-spacing: 1px; margin: 0 0 10px 0; text-transform: uppercase; line-height: 1;}
        .invoice-meta p { font-size: 13px; color: #000; margin: 0 0 6px 0; }
        .invoice-meta strong { display: inline-block; width: 90px; text-align: left; font-weight: bold;}

        .billing-section { display: table; width: 100%; margin-bottom: 25px; }
        .bill-to h3 { font-size: 11px; color: #777; text-transform: uppercase; margin: 0 0 6px 0; letter-spacing: 1px; border-bottom: 1px solid #ddd; padding-bottom: 4px; width: 40%;}
        .customer-name { font-size: 15px; font-weight: bold; color: #000; margin: 0 0 4px 0; text-transform: uppercase; }
        .customer-address { font-size: 13px; color: #555; line-height: 1.4; width: 50%; text-transform: uppercase; }

        table.items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { 
            background: #f8f9fa; color: #333; font-weight: bold; text-transform: uppercase; font-size: 11px; 
            padding: 8px; border-top: 1px solid #333; border-bottom: 1px solid #333; text-align: left; 
        }
        .items-table td { padding: 10px 8px; border-bottom: 1px solid #eee; font-size: 13px; vertical-align: top; }
        
        .col-no { width: 6%; text-align: center; }
        .col-desc { width: 54%; line-height: 1.4; }
        .col-qty { width: 10%; text-align: center; }
        .col-price { width: 15%; text-align: right; }
        .col-total { width: 15%; text-align: right; font-weight: bold; color: #000; }

        .totals-wrapper { width: 100%; display: table; margin-bottom: 20px; }
        .totals-spacer { display: table-cell; width: 55%; }
        .totals-content { display: table-cell; width: 45%; }
        
        .totals-table { width: 100%; border-collapse: collapse; }
        .totals-table td { padding: 6px 8px; border: none; font-size: 13px; text-align: right; }
        .totals-table td.label { color: #555; text-align: left; }
        .totals-table tr.grand-total td { 
            font-size: 15px; font-weight: bold; color: #000; 
            border-top: 2px solid #333; border-bottom: 2px double #333; padding: 8px; 
        }

        /* Footer dilaraskan supaya naik ke atas sikit dan terletak kemas */
        .footer { 
            position: fixed; 
            bottom: 10px; /* Naikkan sedikit dari hujung kertas */
            left: 0; 
            right: 0; 
            width: 100%; 
            display: table; 
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .footer-left, .footer-right { display: table-cell; vertical-align: bottom; }
        .footer-left { width: 55%; }
        .footer-right { width: 45%; text-align: center; }

        .contact-info h4 { font-size: 11px; color: #777; text-transform: uppercase; margin: 0 0 3px 0; letter-spacing: 1px;}
        .contact-info p { font-size: 11px; color: #555; line-height: 1.4; margin: 0; }
        
        .signature-box img { max-width: 95px; margin-bottom: 2px; }
        .signature-line { border-top: 1px solid #000; width: 80%; margin: 12px auto 3px auto; }
        .signature-name { font-weight: bold; font-size: 12px; color: #000; text-transform: uppercase; letter-spacing: 1px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            @if(file_exists(public_path('assets/img/defectguru_logo.png')))
                <img src="{{ public_path('assets/img/defectguru_logo.png') }}" class="logo">
            @else
                <h2 style="margin-top:0;">DEFECTGURU</h2>
            @endif
            <p class="company-name">DefectGuru</p>
            <p class="company-details">
                MIYYA HOME INSPECTION<br>
                202503339900<br>
                (003803966-P)
            </p>
        </div>
        <div class="header-right">
            <h1 class="invoice-title">INVOICE</h1>
            <div class="invoice-meta">
                <p><strong>Invoice No:</strong> {{ $invoice->inv_no }}</p>
                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($invoice->date)->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    <div class="billing-section">
        <div class="bill-to">
            <h3>Invoice To:</h3>
            <p class="customer-name">{{ $invoice->cus_name }}</p>
            <p class="customer-address">{!! nl2br(e($invoice->cus_address)) !!}</p>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th class="col-no">NO.</th>
                <th class="col-desc">DESCRIPTION</th>
                <th class="col-qty">QTY</th>
                <th class="col-price">UNIT (RM)</th>
                <th class="col-total">AMOUNT (RM)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->details as $index => $item)
            <tr>
                <td class="col-no">{{ $index + 1 }}</td>
                <td class="col-desc">{!! nl2br(e($item->desc)) !!}</td>
                <td class="col-qty">1</td>
                <td class="col-price">{{ number_format($item->price, 2) }}</td>
                <td class="col-total">{{ number_format($item->price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-wrapper">
        <div class="totals-spacer"></div>
        <div class="totals-content">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal</td>
                    <td>{{ number_format($invoice->grand_total, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Tax (0%)</td>
                    <td>0.00</td>
                </tr>
                <tr class="grand-total">
                    <td class="label">TOTAL (RM)</td>
                    <td>{{ number_format($invoice->grand_total, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        <div class="footer-left">
            <div class="contact-info">
                <h4>Contact Info</h4>
                <p>
                    <strong>Syahmi Suhairi</strong><br>
                    011-39937001<br>
                    No 5 Jalan Muhibbah 8<br>
                    Taman Muhibbah, Kajang, Selangor
                </p>
            </div>
        </div>
        <div class="footer-right">
            <div class="signature-box">
                @if(file_exists(public_path('assets/img/signature.png')))
                    <img src="{{ public_path('assets/img/signature.png') }}" alt="Signature">
                @else
                    <br><br>
                @endif
                <div class="signature-line"></div>
                <p class="signature-name">SYAHMI SUHAIRI</p>
            </div>
        </div>
    </div>
</body>
</html>