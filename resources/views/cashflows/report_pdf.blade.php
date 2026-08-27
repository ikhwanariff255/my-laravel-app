<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cash Flow Monthly Report - {{ $month }}/{{ $year }}</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1f2937; margin: 0; padding: 0; font-size: 11px; line-height: 1.4; }
        
        /* Header Section */
        .header { text-align: center; border-bottom: 2px solid #1f2937; padding-bottom: 12px; margin-bottom: 20px; }
        .logo { max-width: 140px; margin-bottom: 5px; }
        .company-name { font-size: 18px; font-weight: 800; margin: 0; letter-spacing: 0.5px; }
        .company-details { font-size: 10px; margin: 3px 0 0 0; color: #4b5563; }
        
        .report-title { text-align: center; margin-bottom: 25px; }
        .report-title h3 { margin: 0; font-size: 15px; text-transform: uppercase; color: #111827; }
        .report-title p { margin: 4px 0 0 0; font-size: 11px; color: #6b7280; font-weight: bold; }

        /* Section Titles */
        .section-title { font-size: 12px; font-weight: bold; text-transform: uppercase; margin: 20px 0 8px 0; padding: 6px 10px; background: #f3f4f6; border-left: 4px solid; color: #1f2937; page-break-after: avoid; }
        .section-title.in { border-left-color: #10b981; } /* Green for Cash In */
        .section-title.out { border-left-color: #ef4444; } /* Red for Cash Out */

        /* Data Tables */
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table thead { display: table-header-group; }
        table.data-table th { background: #f9fafb; color: #4b5563; font-size: 10px; text-transform: uppercase; padding: 8px; border: 1px solid #e5e7eb; text-align: left; font-weight: bold; }
        table.data-table td { padding: 8px; border: 1px solid #e5e7eb; vertical-align: top; }
        table.data-table tr { page-break-inside: avoid; }
        table.data-table tr:nth-child(even) { background-color: #fbfbfc; }
        
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        
        .text-green { color: #059669; font-weight: bold; }
        .text-red { color: #dc2626; font-weight: bold; }
        
        .ref-no { font-weight: 700; color: #111827; }
        .desc-text { color: #4b5563; font-size: 10px; margin-top: 2px; display: block; }

        /* Table Totals */
        .table-total { font-weight: bold; background-color: #f3f4f6; }

        /* Final Summary Box */
        .summary-wrapper { page-break-inside: avoid; margin-top: 30px; }
        table.summary-box { width: 100%; border-collapse: collapse; border: 2px solid #1f2937; }
        table.summary-box td { padding: 12px; font-size: 13px; }
        table.summary-box td.label { font-weight: bold; background: #f9fafb; border-right: 1px solid #e5e7eb; }
        table.summary-box td.value { text-align: right; font-weight: bold; font-size: 14px; }
        
        .net-balance { border-top: 2px solid #1f2937 !important; background: #f3f4f6; }
        .net-balance .label { font-size: 14px; text-transform: uppercase; }
        .net-balance .value { font-size: 16px; }

        /* Footer */
        .footer { margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 10px; text-align: justify; }
        .footer table { width: 100%; font-size: 9px; color: #6b7280; }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        @if(file_exists(public_path('assets/img/defectguru_logo.png')))
            <img src="{{ public_path('assets/img/defectguru_logo.png') }}" class="logo">
        @else
            <h2 class="company-name">DEFECTGURU</h2>
        @endif
        <p class="company-name">MIYYA HOME INSPECTION</p>
        <p class="company-details">202503339900 (003803966-P) &bull; No 5 Jalan Muhibbah 8, Kajang, Selangor</p>
    </div>

    <!-- TITLE -->
    <div class="report-title">
        <h3>Cash Flow Statement</h3>
        <p>FOR THE PERIOD OF: {{ strtoupper(date("F Y", mktime(0, 0, 0, $month, 10, $year))) }}</p>
    </div>

    <!-- SECTION 1: CASH INFLOWS -->
    <div class="section-title in">Part A: Cash Inflows (Receipts / Sales)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No.</th>
                <th width="15%">Date</th>
                <th width="25%">Reference No.</th>
                <th width="40%">Description</th>
                <th width="15%" class="text-right">Amount (RM)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cashIns as $index => $in)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($in->date)->format('d-M-Y') }}</td>
                <td class="ref-no">{{ $in->reference_no }}</td>
                <td><span class="desc-text">{{ $in->description }}</span></td>
                <td class="text-right text-green">+{{ number_format($in->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="padding: 20px; color: #9ca3af;">No cash inflows recorded for this period.</td>
            </tr>
            @endforelse
            <tr class="table-total">
                <td colspan="4" class="text-right">Total Cash Inflows:</td>
                <td class="text-right text-green">{{ number_format($totalIn, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- SECTION 2: CASH OUTFLOWS -->
    <div class="section-title out">Part B: Cash Outflows (Expenses / Payouts)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No.</th>
                <th width="15%">Date</th>
                <th width="25%">Reference No.</th>
                <th width="40%">Description & Payee</th>
                <th width="15%" class="text-right">Amount (RM)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cashOuts as $index => $out)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($out->date)->format('d-M-Y') }}</td>
                <td class="ref-no">{{ $out->reference_no }}</td>
                <td>
                    <span class="desc-text">
                        {{ $out->description }}
                        @if($out->user) <br><strong>Staff:</strong> {{ $out->user->name }} @endif
                    </span>
                </td>
                <td class="text-right text-red">-{{ number_format($out->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="padding: 20px; color: #9ca3af;">No cash outflows recorded for this period.</td>
            </tr>
            @endforelse
            <tr class="table-total">
                <td colspan="4" class="text-right">Total Cash Outflows:</td>
                <td class="text-right text-red">{{ number_format($totalOut, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- SECTION 3: FINANCIAL SUMMARY -->
    <div class="summary-wrapper">
        <table class="summary-box">
            <tr>
                <td class="label" width="75%">Total Cash Inflows (A)</td>
                <td class="value text-green" width="25%">RM {{ number_format($totalIn, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Total Cash Outflows (B)</td>
                <td class="value text-red">RM {{ number_format($totalOut, 2) }}</td>
            </tr>
            <tr class="net-balance">
                <td class="label">Net Balance (A - B)</td>
                <td class="value" style="color: {{ $netBalance >= 0 ? '#059669' : '#dc2626' }};">
                    RM {{ number_format($netBalance, 2) }}
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER SIGNATURE -->
    <div class="footer">
        <table>
            <tr>
                <td width="50%">
                    <strong>System Generated Document</strong><br>
                    DefexSnap Accounting Module<br>
                    Date Generated: {{ \Carbon\Carbon::now()->format('d-M-Y h:i A') }}
                </td>
                <td width="50%" style="text-align: right;">
                    <br><br>
                    _______________________________________<br>
                    <strong>Authorized Signature</strong>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>