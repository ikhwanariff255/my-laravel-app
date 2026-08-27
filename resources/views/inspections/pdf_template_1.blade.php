<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inspection Report - {{ $inspection->title }}</title>
    <style>
        @page { size: A4 portrait; margin: 30px 40px; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11pt; color: #000; line-height: 1.4; }
        
        .page-break { page-break-after: always; }
        
        /* Muka Depan / Cover Page */
        .cover-container { text-align: center; padding-top: 60px; }
        .company-logo { max-width: 200px; max-height: 120px; margin-bottom: 30px; object-fit: contain; }
        .report-title { font-size: 24pt; font-weight: bold; margin-bottom: 40px; text-decoration: underline; text-transform: uppercase; }
        .cover-info-table { width: 85%; margin: 0 auto; text-align: left; font-size: 12pt; border-collapse: collapse; }
        .cover-info-table td { padding: 10px 5px; vertical-align: top; }
        .cover-info-table td.label { font-weight: bold; width: 35%; }
        .cover-info-table td.separator { width: 5%; text-align: center; font-weight: bold; }
        
        /* Pelan / Plans */
        .section-header { background-color: #2d3748; color: #fff; padding: 8px 12px; font-size: 13pt; font-weight: bold; text-transform: uppercase; margin-bottom: 15px; }
        .plan-box { text-align: center; border: 1px solid #cbd5e0; padding: 10px; background: #fff; margin-bottom: 15px; }
        .plan-img { max-width: 100%; max-height: 550px; object-fit: contain; }

        /* Kotak Defect / Defect Box */
        .location-heading { font-size: 12pt; font-weight: bold; color: #1a202c; border-bottom: 2px solid #2d3748; padding-bottom: 4px; margin-top: 20px; margin-bottom: 10px; text-transform: uppercase; }
        
        .defect-table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 15px; page-break-inside: avoid; }
        .defect-table td { padding: 6px 10px; vertical-align: top; border: 1px solid #000; font-size: 10.5pt; }
        .defect-header-row { background-color: #edf2f7; font-weight: bold; }
        .col-title { font-weight: bold; width: 22%; background-color: #f7fafc; }
        
        /* Tetapan Imej Peta Mini & Bukti / Mini Map & Evidence Image Settings */
        .map-img { width: 130px; height: 130px; object-fit: contain; border: 1px solid #ccc; background: #fff; display: block; margin-left: auto; }
        
        .evidence-table { width: 100%; border-collapse: collapse; border: none; margin-top: 5px; }
        .evidence-table td { border: none; padding: 0 4px; text-align: center; }
        .evidence-img { width: 140px; height: 110px; object-fit: cover; border: 1px solid #ccc; border-radius: 3px; }
    </style>
</head>
<body>

    <!-- ================= MUKA DEPAN ================= -->
    <div class="cover-container">
        @if($settings && $settings->logo_path)
            <img src="{{ public_path('storage/' . $settings->logo_path) }}" class="company-logo">
        @else
            <h2 style="margin-bottom: 30px;">{{ $settings->company_name ?? 'BENAMORA SDN BHD' }}</h2>
        @endif

        <div class="report-title">DEFECT INSPECTION REPORT</div>

        <table class="cover-info-table">
            <tr>
                <td class="label">PROJECT NAME</td><td class="separator">:</td>
                <td><strong>{{ strtoupper($inspection->title) }}</strong></td>
            </tr>
            <tr>
                <td class="label">CLIENT NAME</td><td class="separator">:</td>
                <td>{{ strtoupper($inspection->clientname) }}</td>
            </tr>
            <tr>
                <td class="label">ADDRESS</td><td class="separator">:</td>
                <td>{{ strtoupper($inspection->address) }}, {{ $inspection->state }}</td>
            </tr>
            <tr>
                <td class="label">PROPERTY TYPE</td><td class="separator">:</td>
                <td>{{ strtoupper($inspection->type) }}</td>
            </tr>
            <tr>
                <td class="label">INSPECTION OFFICER</td><td class="separator">:</td>
                <td>{{ strtoupper($inspection->user->name ?? '-') }}</td>
            </tr>
            <tr>
                <td class="label">REPORT DATE</td><td class="separator">:</td>
                <td>{{ strtoupper(date('d F Y')) }}</td>
            </tr>
            <tr>
                <td class="label">SSM / CIDB NO.</td><td class="separator">:</td>
                <td>{{ $settings->ssm ?? '-' }} / {{ $settings->cidb ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- ================= 1. FLOOR PLAN ================= -->
    <div class="section-header">Floor Plan</div>
    @if($inspection->layout_img)
        <div class="plan-box">
            <img src="{{ public_path('storage/' . $inspection->layout_img) }}" class="plan-img">
        </div>
    @endif

    <div class="page-break"></div>

    <!-- ================= 2. INDICATED LAYOUT PLAN ================= -->
    <div class="section-header">Indicated Layout Plan</div>
    @if(isset($indicatedPath) && file_exists(public_path('storage/' . $indicatedPath)))
        <div class="plan-box">
            <img src="{{ public_path('storage/' . $indicatedPath) }}" class="plan-img">
        </div>
    @elseif($inspection->layout_img)
        <div class="plan-box">
            <img src="{{ public_path('storage/' . $inspection->layout_img) }}" class="plan-img">
        </div>
    @endif

    <div class="page-break"></div>

    <!-- ================= SENARAI DEFECT ================= -->
    <div class="section-header">List of Defects by Location</div>

    @php
        $groupedDefects = $inspection->defects->groupBy(function($item) {
            return !empty($item->location) ? strtoupper(trim($item->location)) : 'UNSPECIFIED LOCATION';
        });
        
        $globalCounter = 1;
        $countInPage = 0;
        $lastLocation = "";
    @endphp

    @forelse($groupedDefects as $location => $defects)
        
        @foreach($defects as $defect)
            @php
                $shouldBreak = false;
                if ($location != $lastLocation && $globalCounter > 1) {
                    $shouldBreak = true;
                    $countInPage = 1;
                } else if ($countInPage >= 2) {
                    $shouldBreak = true;
                    $countInPage = 1;
                } else {
                    $countInPage++;
                }
            @endphp

            @if($shouldBreak)
                <div class="page-break"></div>
            @endif

            @if($location != $lastLocation)
                <div class="location-heading">Area / Location: {{ $location }}</div>
            @endif

            <table class="defect-table">
                <tr class="defect-header-row">
                    <td colspan="2">DEFECT NO. {{ $globalCounter }}</td>
                </tr>
                <tr>
                    <td class="col-title">SPECIFIC LOCATION</td>
                    <td>
                        <table style="width:100%; border:none;">
                            <tr>
                                <td style="border:none; width:65%; vertical-align: middle;">
                                    <strong>{{ $defect->location }}</strong>
                                </td>
                                <td style="border:none; width:35%; text-align:right;">
                                    @if(isset($defect->single_map_path) && file_exists(public_path('storage/' . $defect->single_map_path)))
                                        <img src="{{ public_path('storage/' . $defect->single_map_path) }}" class="map-img" title="Marker Map">
                                    @elseif($inspection->layout_img)
                                        <img src="{{ public_path('storage/' . $inspection->layout_img) }}" class="map-img" title="Layout">
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="col-title">ELEMENT / CATEGORY</td>
                    <td>{{ $defect->category }} &mdash; {{ $defect->type }}</td>
                </tr>
                <tr>
                    <td class="col-title">DEFECT TYPE</td>
                    <td><strong style="color: #c53030;">{{ $defect->defect }}</strong></td>
                </tr>
                <tr>
                    <td class="col-title">REMARKS</td>
                    <td>{!! nl2br(e($defect->desc)) !!}</td>
                </tr>
                <tr>
                    <td class="col-title">IMAGE EVIDENCE</td>
                    <td>
                        @if($defect->img && is_array($defect->img))
                            <table class="evidence-table">
                                <tr>
                                    @foreach(array_slice($defect->img, 0, 3) as $img)
                                        <td>
                                            <img src="{{ public_path('storage/' . $img) }}" class="evidence-img">
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        @else
                            <span style="color: #718096; font-style: italic;">No image evidence available.</span>
                        @endif
                    </td>
                </tr>
            </table>

            @php
                $lastLocation = $location;
                $globalCounter++;
            @endphp
        @endforeach

    @empty
        <p style="text-align: center; color: #718096; padding: 30px;">No defect records have been entered yet.</p>
    @endforelse

</body>
</html>