<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $cashflow->reference_no }}</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 0; padding: 0; font-size: 13px; }
        
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 15px; }
        .logo { max-width: 180px; margin-bottom: 5px; }
        .company-name { font-size: 18px; font-weight: bold; margin: 0; }
        .company-details { font-size: 12px; margin: 2px 0 0 0; }
        
        .doc-title { text-align: left; font-size: 20px; font-weight: bold; text-transform: uppercase; margin-bottom: 15px; color: #333;}
        
        /* Box Layout exactly like the PO image */
        table.info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; border: 1px solid #ccc; }
        table.info-table td { border: 1px solid #ccc; padding: 8px 10px; vertical-align: top; }
        
        .label { font-weight: bold; background-color: #f9f9f9; width: 25%; }
        .value { width: 75%; }

        /* Line Items Table */
        .section-title { font-size: 15px; font-weight: bold; color: #4a5568; margin-bottom: 5px; margin-top: 10px; }
        .currency-note { text-align: center; color: #777; font-size: 14px; font-weight: bold; margin-bottom: 10px; }
        
        table.line-items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.line-items th { border-top: 2px solid #ccc; border-bottom: 2px solid #ccc; padding: 8px; text-align: left; font-size: 12px; }
        table.line-items td { padding: 10px 8px; border-bottom: 1px solid #eee; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Totals */
        table.totals { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.totals td { padding: 6px 8px; text-align: right; }
        table.totals td.total-label { font-weight: bold; }
        table.totals tr.final-total td { font-weight: bold; border-top: 1px solid #000; border-bottom: 2px double #000; font-size: 15px; }
        
        /* Footer Notes */
        table.notes-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.notes-table td { border: 1px solid #ccc; padding: 8px 10px; }
        
        .prepared-by { font-size: 11px; font-style: italic; color: #555; }
    </style>
</head>
<body>

    <div class="header">
        @if(file_exists(public_path('assets/img/defectguru_logo.png')))
            <img src="{{ public_path('assets/img/defectguru_logo.png') }}" class="logo">
        @else
            <h2 class="company-name">DEFECTGURU</h2>
        @endif
        <p class="company-name">MIYYA HOME INSPECTION</p>
        <p class="company-details">202503339900 (003803966-P) | 011-39937001 | No 5 Jalan Muhibbah 8, Kajang, Selangor</p>
    </div>

    <div class="doc-title">
        {{ $cashflow->type == 'in' ? 'OFFICIAL RECEIPT' : 'PAYMENT VOUCHER' }}
    </div>

    <!-- Info Box matches the provided image style -->
    <table class="info-table">
        <tr>
            <td class="label">Ref No.</td>
            <td class="value"><strong>{{ $cashflow->reference_no }}</strong></td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td class="value">{{ \Carbon\Carbon::parse($cashflow->date)->format('d-M-Y') }}</td>
        </tr>
        <tr>
            <td class="label">Category</td>
            <td class="value">{{ strtoupper($cashflow->category) }}</td>
        </tr>
        @if($cashflow->user)
        <tr>
            <td class="label">{{ $cashflow->type == 'in' ? 'Received From' : 'Paid To (Staff)' }}</td>
            <td class="value">{{ strtoupper($cashflow->user->name) }}</td>
        </tr>
        @endif
    </table>

    <div class="currency-note">All currencies are in RM</div>

    <div class="section-title">Line Items</div>
    <table class="line-items">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="50%">Description</th>
                <th width="10%" class="text-center">Qty</th>
                <th width="15%" class="text-right">U/Price</th>
                <th width="20%" class="text-right">Total Price</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>{!! nl2br(e($cashflow->description)) !!}</td>
                <td class="text-center">1 EA</td>
                <td class="text-right">{{ number_format($cashflow->amount, 2) }}</td>
                <td class="text-right">{{ number_format($cashflow->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td width="70%"></td>
            <td width="15%" class="total-label">Sub-Total</td>
            <td width="15%">{{ number_format($cashflow->amount, 2) }}</td>
        </tr>
        <tr class="final-total">
            <td></td>
            <td class="total-label">Grand Total</td>
            <td>{{ number_format($cashflow->amount, 2) }}</td>
        </tr>
    </table>

    <table class="notes-table">
        <tr>
            <td width="15%" class="label" style="background-color: transparent;">Notes / Status</td>
            <td width="85%">
                {{ $cashflow->type == 'in' ? 'Payment Received Successfully.' : 'Payment Issued / Expensed.' }}
            </td>
        </tr>
    </table>

    <div class="prepared-by">
        Prepared by System Admin<br>
        Generated on {{ \Carbon\Carbon::now()->format('d-M-Y h:i A') }}
    </div>

</body>
</html>