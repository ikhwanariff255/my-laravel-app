<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inspection Report - {{ $inspection->title }}</title>

    @php
        if (!function_exists('getBase64Image')) {
            function getBase64Image($path) {
                if ($path && file_exists($path)) {
                    $ext = pathinfo($path, PATHINFO_EXTENSION);
                    $type = strtolower($ext);
                    if ($type === 'jpg') $type = 'jpeg';
                    $data = @file_get_contents($path);
                    if ($data) {
                        return 'data:image/' . $type . ';base64,' . base64_encode($data);
                    }
                }
                return '';
            }
        }
    @endphp

    <style>
        @page {
            size: A4 portrait;
            margin: 20px 25px 50px 25px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #000;
            line-height: 1.15;
        }
        .page-break { page-break-after: always; }

        footer {
            position: fixed;
            bottom: -30px;
            left: 0px;
            right: 0px;
            height: 25px;
            border-top: 1px solid #000;
            padding-top: 4px;
            z-index: 1;
        }
        .footer-table { width: 100%; border: none; font-size: 8.5pt; font-weight: bold; color: #4a5568; }
        .footer-table td { border: none; padding: 0; }
        .page-number:after { content: counter(page); }

        .info-label { font-size: 9pt; font-weight: bold; color: #4a5568; margin-top: 18px; text-transform: uppercase; }
        .info-value { font-size: 11pt; font-weight: bold; margin-bottom: 2px; text-transform: uppercase; color: #000; line-height: 1.2; }

        .toc-title { font-size: 20pt; font-weight: bold; margin-bottom: 20px; }
        .toc-table { width: 100%; border-collapse: collapse; font-size: 11pt; }

        .section-header {
            background-color: #1a365d;
            color: #fff;
            padding: 6px 10px;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .plan-box { text-align: center; border: 1px solid #cbd5e0; padding: 10px; background: #fff; margin-bottom: 10px; }
        .plan-img { max-width: 100%; max-height: 520px; width: auto; height: auto; }

        .loc-intro-container { text-align: center; padding-top: 10px; }
        .loc-title { font-size: 15pt; font-weight: bold; text-transform: uppercase; color: #1a365d; margin-bottom: 3px; }
        .loc-subtitle { font-size: 10pt; color: #4a5568; margin-bottom: 15px; font-weight: bold; }
        .loc-plan-box { border: 1px solid #cbd5e0; padding: 6px; background: #fff; display: inline-block; }
        .loc-plan-img { max-width: 100%; max-height: 450px; width: auto; height: auto; }

        .defect-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            border: 1.5px solid #000;
        }
        .defect-box th, .defect-box td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: top;
            font-size: 8.5pt;
            overflow: hidden;
            word-wrap: break-word;
        }
        .defect-header {
            background-color: #9cb4d4 !important;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 9pt;
            padding: 3px !important;
        }
        .sub-header {
            background-color: #f0f4f8;
            font-weight: bold;
            text-align: center;
            font-size: 8pt;
            text-transform: uppercase;
            padding: 3px 5px !important;
        }
        .col-location { width: 15%; }
        .col-element { width: 20%; }
        .col-defect { width: 25%; }
        .col-remarks { width: 40%; }
        .data-location, .data-element, .data-defect, .data-remarks {
            padding: 5px 3px;
            vertical-align: top;
            font-size: 8pt;
        }
        .data-defect { color: red; font-weight: bold; }

        .map-cell { width: 130px; text-align: center; vertical-align: middle; padding: 2px; }
        .map-img { max-width: 125px; max-height: 125px; }
        .evidence-cell { padding: 2px; vertical-align: middle; }
        .evidence-img {
            max-width: 100%;
            max-height: 125px;
            width: auto;
            height: auto;
            display: block;
            border-radius: 2px;
            border: 1px solid #cbd5e0;
        }
        .no-evidence { color: #a0aec0; font-size: 8pt; text-align: center; height: 130px; display: flex; align-items: center; justify-content: center; }
        .no-defects { text-align: center; color: #718096; padding: 30px; font-size: 11pt; }
        @media print { .page-break { page-break-after: always; } }
    </style>
</head>
<body>

    <!-- ================= FOOTER ================= -->
    <footer>
        <table class="footer-table">
            <tr>
                <td style="text-align: left; width: 33%;">DefectGURU</td>
                <td style="text-align: center; width: 33%;" class="page-number"></td>
                <td style="text-align: right; width: 33%;">{{ strtoupper($inspection->title) }}</td>
            </tr>
        </table>
    </footer>

    <!-- ================= 1. COVER ================= -->
    <div style="position: absolute; top: -50px; left: -50px; width: 850px; height: 1200px; z-index: 100; background-color: #fff;">
        <table style="width: 100%; height: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td style="width: 42%; background-color: #ffffff; vertical-align: top; padding: 50px 30px 30px 45px;">
                    @php $logoPath = public_path('assets/img/defectguru_logo.png'); @endphp
                    @if(file_exists($logoPath))
                        <img src="{{ getBase64Image($logoPath) }}" style="width: 100%; max-width: 240px; margin-bottom: 25px; display: block;" alt="Logo">
                    @else
                        <div style="font-size: 22pt; font-weight: bold; margin-bottom: 25px;">DEFECT GURU</div>
                    @endif

                    <div style="font-size: 24pt; font-weight: 900; margin-bottom: 20px; line-height: 1.15; color: #000; letter-spacing: -0.5px;">
                        HOME<br>DEFECT<br>INSPECTION<br>REPORT
                    </div>
                    <div style="border-bottom: 2.5px solid #000; width: 100%; margin-bottom: 15px;"></div>

                    <div class="info-label" style="margin-top: 0;">PROJECT:</div>
                    <div class="info-value">{{ $inspection->title }}</div>
                    <div class="info-label">NAME:</div>
                    <div class="info-value">{{ $inspection->clientname }}</div>
                    <div class="info-label">ADDRESS:</div>
                    <div class="info-value">{{ $inspection->address }}<br>{{ $inspection->state }}</div>
                    <div class="info-label">CONTACT NO:</div>
                    <div class="info-value">{{ $inspection->cus_no ?? $inspection->contact ?? '-' }}</div>
                    <div class="info-label">EMAIL:</div>
                    <div class="info-value" style="text-transform: none;">{{ $inspection->cus_email ?? $inspection->email ?? '-' }}</div>
                    <div class="info-label">NO. SSM / CIDB:</div>
                    <div class="info-value">{{ $settings->ssm ?? '-' }} / {{ $settings->cidb ?? '-' }}</div>

                    <div style="margin-top: 30px; width: 100%;">
                        <table style="width: 100%; border: none; font-size: 8.5pt; font-weight: bold; color: #4a5568;">
                            <tr>
                                <td style="border: none; padding: 0; width: 50%;">INSPECTION DATE :</td>
                                <td style="border: none; padding: 0; width: 50%;">INSPECT BY :</td>
                            </tr>
                            <tr>
                                <td style="border: none; padding: 0; color: #000;">
                                    {{ !empty($inspection->inspection_date) ? date('d/m/Y', strtotime($inspection->inspection_date)) : date('d/m/Y') }}
                                </td>
                                <td style="border: none; padding: 0; color: #000;">{{ strtoupper($inspection->user->name ?? '-') }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="width: 58%; background-color: #e2e8f0; padding: 0; vertical-align: top;">
                    @if(!empty($inspection->local_cover) && file_exists($inspection->local_cover))
                        <img src="{{ getBase64Image($inspection->local_cover) }}" style="width: 100%; height: 1200px; object-fit: cover; display: block;">
                    @elseif($inspection->img && file_exists(public_path('storage/' . $inspection->img)))
                        <img src="{{ getBase64Image(public_path('storage/' . $inspection->img)) }}" style="width: 100%; height: 1200px; object-fit: cover; display: block;">
                    @elseif($inspection->layout_img && file_exists(public_path('storage/' . $inspection->layout_img)))
                        <img src="{{ getBase64Image(public_path('storage/' . $inspection->layout_img)) }}" style="width: 100%; height: 1200px; object-fit: cover; display: block;">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- ================= 2. TABLE OF CONTENTS ================= -->
    @php
        $groupedDefects = $inspection->defects->groupBy(function($item) {
            return !empty($item->location) ? strtoupper(trim($item->location)) : 'UNSPECIFIED LOCATION';
        });
        $estPage = 5;
    @endphp
    <div class="toc-title">Contents</div>
    <table class="toc-table">
        @foreach($groupedDefects as $location => $defects)
            <tr>
                <td style="padding: 8px 0; white-space: nowrap;">Location: {{ $location }}</td>
                <td style="padding: 8px 10px; width: 99%; vertical-align: middle;">
                    <div style="border-bottom: 1.5px dotted #a0aec0; width: 100%; height: 1px;"></div>
                </td>
                <td style="padding: 8px 0; text-align: right; white-space: nowrap;">{{ $estPage }}</td>
            </tr>
            @php $estPage += ceil($defects->count() / 4); @endphp
        @endforeach
    </table>

    <div class="page-break"></div>

    <!-- ================= 3. FLOOR PLAN (plain) ================= -->
    <div class="section-header">Floor Plan</div>
    @if(isset($plainLayoutPath) && file_exists($plainLayoutPath))
        <div class="plan-box">
            <img src="{{ getBase64Image($plainLayoutPath) }}" class="plan-img">
        </div>
    @elseif($inspection->layout_img && file_exists(public_path('storage/' . $inspection->layout_img)))
        <div class="plan-box">
            <img src="{{ getBase64Image(public_path('storage/' . $inspection->layout_img)) }}" class="plan-img">
        </div>
    @endif

    <div class="page-break"></div>

    <!-- ================= 4. INDICATED PLAN ================= -->
    <div class="section-header">Indicated Plan</div>
    @if(isset($indicatedPath) && file_exists($indicatedPath))
        <div class="plan-box">
            <img src="{{ getBase64Image($indicatedPath) }}" class="plan-img">
        </div>
    @elseif($inspection->layout_img && file_exists(public_path('storage/' . $inspection->layout_img)))
        <div class="plan-box">
            <img src="{{ getBase64Image(public_path('storage/' . $inspection->layout_img)) }}" class="plan-img">
        </div>
    @endif

    <div class="page-break"></div>

    <!-- ================= 5. LOCATIONS & DEFECT TABLES ================= -->
    @forelse($groupedDefects as $location => $defects)

        <!-- Location intro page -->
        <div class="loc-intro-container">
            <div class="loc-title">LOCATION: {{ $location }}</div>
            <div class="loc-subtitle">DEFECT SPOTTED: {{ $defects->count() }} Defects spotted</div>
            <div class="loc-plan-box">
                @if(isset($locationMaps[$location]) && file_exists($locationMaps[$location]))
                    <img src="{{ getBase64Image($locationMaps[$location]) }}" class="loc-plan-img" alt="Location Map">
                @elseif(isset($indicatedPath) && file_exists($indicatedPath))
                    <img src="{{ getBase64Image($indicatedPath) }}" class="loc-plan-img" alt="Indicated Plan">
                @elseif($inspection->layout_img && file_exists(public_path('storage/' . $inspection->layout_img)))
                    <img src="{{ getBase64Image(public_path('storage/' . $inspection->layout_img)) }}" class="loc-plan-img" alt="Floor Plan">
                @endif
            </div>
        </div>
        <div class="page-break"></div>

        <!-- Defect tables (max 4 per page) -->
        @php $defectNo = 1; @endphp
        @foreach($defects->chunk(4) as $chunk)
            @foreach($chunk as $defect)
                <table class="defect-box">
                    <tr><td colspan="4" class="defect-header">DEFECT {{ $defectNo++ }}</td></tr>
                    <tr>
                        <th class="sub-header col-location">LOCATION</th>
                        <th class="sub-header col-element">ELEMENT</th>
                        <th class="sub-header col-defect">DEFECT</th>
                        <th class="sub-header col-remarks">REMARKS</th>
                    </tr>
                    <tr>
                        <td class="data-location">{{ ucfirst(strtolower($defect->location)) }}</td>
                        <td class="data-element">{{ ucfirst(strtolower($defect->category)) }}</td>
                        <td class="data-defect">{{ $defect->defect }}</td>
                        <td class="data-remarks">{!! nl2br(e($defect->desc)) !!}</td>
                    </tr>
                    <!-- Image row -->
                    <tr>
                        <td colspan="2" class="map-cell">
                            @if(isset($defect->single_map_path) && file_exists($defect->single_map_path))
                                <img src="{{ getBase64Image($defect->single_map_path) }}" class="map-img" alt="Map">
                            @else
                                <span style="color: #a0aec0; font-size: 8pt;">No map</span>
                            @endif
                        </td>
                        <td colspan="2" class="evidence-cell">
                            @php
                                $evidenceFiles = [];
                                if (!empty($defect->local_evidence) && is_array($defect->local_evidence)) {
                                    $evidenceFiles = array_slice($defect->local_evidence, 0, 4);
                                } elseif (!empty($defect->img) && is_array($defect->img)) {
                                    $s3Disk = Storage::disk('s3');
                                    foreach (array_slice($defect->img, 0, 4) as $s3Path) {
                                        if ($s3Disk->exists($s3Path)) {
                                            $evidenceFiles[] = $s3Disk->url($s3Path);
                                        }
                                    }
                                }
                                $total = count($evidenceFiles);
                            @endphp
                            @if($total > 0)
                                <table style="width:100%; border:0; border-collapse:collapse;">
                                    <tr>
                                        @foreach($evidenceFiles as $file)
                                            <td style="width:{{ 100/$total }}%; padding:2px; text-align:center; vertical-align:middle; border:0;">
                                                @if(file_exists($file))
                                                    <img src="{{ getBase64Image($file) }}" class="evidence-img" alt="Evidence">
                                                @else
                                                    <img src="{{ $file }}" class="evidence-img" alt="Evidence">
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                </table>
                            @else
                                <div class="no-evidence">No evidence images</div>
                            @endif
                        </td>
                    </tr>
                </table>
            @endforeach
            @if(!$loop->last)
                <div class="page-break"></div>
            @endif
        @endforeach

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif

    @empty
        <div class="no-defects">No defect records found.</div>
    @endforelse

</body>
</html>