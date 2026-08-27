<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pemeriksaan (Template 2) - {{ $inspection->title }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 15px 20px;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            color: #000;
            line-height: 1.2;
        }

        .page-break {
            page-break-after: always;
        }

        /* =====================================================
           MUKA DEPAN
        ===================================================== */
        .cover-container { text-align: center; padding-top: 50px; }
        .company-logo { max-width: 200px; max-height: 120px; margin-bottom: 30px; }
        .report-title { font-size: 22pt; font-weight: bold; margin-bottom: 40px; text-decoration: underline; text-transform: uppercase; }
        
        .cover-info-table { width: 85%; margin: 0 auto; text-align: left; font-size: 11pt; border-collapse: collapse; }
        .cover-info-table td { padding: 8px 5px; vertical-align: top; }
        .cover-info-table td.label { font-weight: bold; width: 35%; }
        .cover-info-table td.separator { width: 5%; text-align: center; font-weight: bold; }

        /* =====================================================
           PELAN UTAMA & INDICATION PLAN
        ===================================================== */
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

        /* =====================================================
           HALAMAN KHAS LOKASI
        ===================================================== */
        .loc-intro-container { text-align: center; padding-top: 20px; }
        .loc-title { font-size: 16pt; font-weight: bold; text-transform: uppercase; color: #1a365d; margin-bottom: 5px; }
        .loc-subtitle { font-size: 11pt; color: #4a5568; margin-bottom: 20px; font-weight: bold; }
        .loc-plan-box { border: 1px solid #cbd5e0; padding: 8px; background: #fff; display: inline-block; }
        .loc-plan-img { max-width: 100%; max-height: 480px; width: auto; height: auto; }

        /* =====================================================
           KOTAK JADUAL INDIVIDU DEFECT - FIXED VERSION
        ===================================================== */
        .defect-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1.5px solid #000;
        }

        .defect-box th,
        .defect-box td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
            font-size: 9pt;
            overflow: hidden;
            word-wrap: break-word;
        }

        .defect-header {
            background-color: #ffff00 !important;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 10pt;
            padding: 5px !important;
        }

        .sub-header {
            background-color: #f0f4f8;
            font-weight: bold;
            text-align: center;
            font-size: 8.5pt;
            text-transform: uppercase;
            padding: 4px 6px !important;
        }

        /* FIXED WIDTHS - Using pixel values for DOMPDF compatibility */
        .col-location { width: 70px; }
        .col-element { width: 75px; }
        .col-defect { width: 115px; }
        .col-remarks { width: auto; }

        /* Data row */
        .data-row td {
            min-height: 45px;
            vertical-align: middle;
            padding: 5px 6px !important;
        }

        /* Data cell styling */
        .data-location {
            font-weight: 500;
            color: #2d3748;
        }

        .data-element {
            font-weight: 500;
            color: #2d3748;
        }

        .data-defect {
            color: #c53030;
            font-weight: bold;
        }

        .data-remarks {
            color: #2d3748;
            word-wrap: break-word;
        }

        /* =====================================================
           BAHAGIAN GAMBAR (BAWAH) - PRESERVE ASPECT RATIO
        ===================================================== */
        .image-row td {
            padding: 4px 6px !important;
            vertical-align: middle;
            height: 110px;
        }

        .map-cell {
            text-align: center;
            vertical-align: middle !important;
            height: 110px;
            padding: 4px 6px !important;
            background-color: #fafafa;
        }

        .evidence-cell {
            padding: 4px 6px !important;
            vertical-align: middle;
            height: 110px;
            background-color: #fafafa;
        }

        /* Map image - maintain aspect ratio */
        .map-img {
            max-width: 100%;
            max-height: 100px;
            width: auto;
            height: auto;
            display: block;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            object-fit: contain;
        }

        /* Nested table untuk susun gambar melintang */
        .inner-evidence-table {
            width: 100%;
            height: 100px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .inner-evidence-table td {
            border: none;
            padding: 0 3px;
            vertical-align: middle;
            text-align: center;
            height: 100px;
        }

        .inner-evidence-table td:first-child { padding-left: 0; }
        .inner-evidence-table td:last-child { padding-right: 0; }

        /* Evidence images - maintain aspect ratio, transparent border */
        .evidence-img-wrapper {
            width: 100%;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            border-radius: 3px;
            background-color: transparent;
            overflow: hidden;
        }

        .evidence-img {
            max-width: 100%;
            max-height: 100px;
            width: auto;
            height: auto;
            display: block;
            object-fit: contain;
        }

        .no-defects {
            text-align: center;
            color: #718096;
            padding: 30px;
            font-size: 11pt;
        }

        @media print {
            .page-break { page-break-after: always; }
            .defect-box { page-break-inside: avoid; }
        }
    </style>
</head>

<body>

    <!-- MUKA DEPAN -->
    <div class="cover-container">
        @if($settings && $settings->logo_path)
            <img src="{{ public_path('storage/' . $settings->logo_path) }}" class="company-logo">
        @else
            <h2 style="margin-bottom: 30px;">{{ $settings->company_name ?? 'BENAMORA SDN BHD' }}</h2>
        @endif

        <div class="report-title">LAPORAN PEMERIKSAAN KECACATAN</div>

        <table class="cover-info-table">
            <tr><td class="label">NAMA PROJEK</td><td class="separator">:</td><td><strong>{{ strtoupper($inspection->title) }}</strong></td></tr>
            <tr><td class="label">NAMA KLIEN</td><td class="separator">:</td><td>{{ strtoupper($inspection->clientname) }}</td></tr>
            <tr><td class="label">ALAMAT</td><td class="separator">:</td><td>{{ strtoupper($inspection->address) }}, {{ $inspection->state }}</td></tr>
            <tr><td class="label">JENIS HARTANAH</td><td class="separator">:</td><td>{{ strtoupper($inspection->type) }}</td></tr>
            <tr><td class="label">PEGAWAI PEMERIKSA</td><td class="separator">:</td><td>{{ strtoupper($inspection->user->name ?? '-') }}</td></tr>
            <tr><td class="label">TARIKH LAPORAN</td><td class="separator">:</td><td>{{ strtoupper(date('d F Y')) }}</td></tr>
            <tr><td class="label">NO. SSM / CIDB</td><td class="separator">:</td><td>{{ $settings->ssm ?? '-' }} / {{ $settings->cidb ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- FLOOR PLAN -->
    <div class="section-header">Floor Plan</div>
    @if($inspection->layout_img)
        <div class="plan-box"><img src="{{ public_path('storage/' . $inspection->layout_img) }}" class="plan-img"></div>
    @endif

    <div class="page-break"></div>

    <!-- INDICATION PLAN -->
    <div class="section-header">Indication Plan</div>
    @if(isset($indicatedPath) && file_exists(public_path('storage/' . $indicatedPath)))
        <div class="plan-box"><img src="{{ public_path('storage/' . $indicatedPath) }}" class="plan-img"></div>
    @elseif($inspection->layout_img)
        <div class="plan-box"><img src="{{ public_path('storage/' . $inspection->layout_img) }}" class="plan-img"></div>
    @endif

    <div class="page-break"></div>

    <!-- LOKASI & JADUAL INDIVIDU -->
    @php
        $groupedDefects = $inspection->defects->groupBy(function($item) {
            return !empty($item->location) ? strtoupper(trim($item->location)) : 'UNSPECIFIED LOCATION';
        });
    @endphp

    @forelse($groupedDefects as $location => $defects)

        <!-- HALAMAN KHAS PENGENALAN LOKASI -->
        <div class="loc-intro-container">
            <div class="loc-title">LOCATION: {{ $location }}</div>
            <div class="loc-subtitle">DEFECT SPOTTED: {{ $defects->count() }} Defects spotted</div>
            <div class="loc-plan-box">
                @if(isset($locationMaps[$location]) && file_exists(public_path('storage/' . $locationMaps[$location])))
                    <img src="{{ public_path('storage/' . $locationMaps[$location]) }}" class="loc-plan-img" alt="Location Map with Defects">
                @elseif(isset($indicatedPath) && file_exists(public_path('storage/' . $indicatedPath)))
                    <img src="{{ public_path('storage/' . $indicatedPath) }}" class="loc-plan-img" alt="Floor Plan">
                @elseif($inspection->layout_img)
                    <img src="{{ public_path('storage/' . $inspection->layout_img) }}" class="loc-plan-img" alt="Floor Plan">
                @endif
            </div>
        </div>

        <div class="page-break"></div>

        @php
            $chunks = $defects->chunk(4);
            $globalNo = 1;
        @endphp

        @foreach($chunks as $chunkIndex => $chunkDefects)

            @foreach($chunkDefects as $defect)

                <table class="defect-box">
                    
                    <!-- HEADER ROW - DEFECT NUMBER -->
                    <tr>
                        <td colspan="4" class="defect-header">DEFECT {{ $globalNo++ }}</td>
                    </tr>

                    <!-- HEADER ROW - COLUMN TITLES with FIXED WIDTHS -->
                    <tr>
                        <th class="sub-header col-location">LOCATION</th>
                        <th class="sub-header col-element">ELEMENT</th>
                        <th class="sub-header col-defect">DEFECT</th>
                        <th class="sub-header col-remarks">REMARKS</th>
                    </tr>

                    <!-- DATA ROW -->
                    <tr class="data-row">
                        <td class="data-location col-location">{{ ucfirst(strtolower($defect->location)) }}</td>
                        <td class="data-element col-element">{{ ucfirst(strtolower($defect->category)) }}</td>
                        <td class="data-defect col-defect">{{ $defect->defect }}</td>
                        <td class="data-remarks col-remarks">{!! nl2br(e($defect->desc)) !!}</td>
                    </tr>

                    <!-- IMAGE ROW -->
                    <tr class="image-row">
                        <!-- Map Cell (col-location + col-element = 145px) -->
                        <td colspan="2" class="map-cell" style="width: 145px;">
                            @if(isset($defect->single_map_path) && file_exists(public_path('storage/' . $defect->single_map_path)))
                                <img src="{{ public_path('storage/' . $defect->single_map_path) }}" class="map-img" alt="Location Map">
                            @else
                                <span style="color: #a0aec0; font-size: 8pt;">No map</span>
                            @endif
                        </td>

                        <!-- Evidence Cell (col-defect + col-remarks = remaining space) -->
                        <td colspan="2" class="evidence-cell">
                            @php
                                $images = ($defect->img && is_array($defect->img)) ? array_slice($defect->img, 0, 3) : [];
                                $validImages = [];
                                foreach($images as $image) {
                                    if(file_exists(public_path('storage/' . $image))) {
                                        $validImages[] = $image;
                                    }
                                }
                                $imageCount = count($validImages);
                            @endphp

                            @if($imageCount > 0)
                                <table class="inner-evidence-table">
                                    <tr>
                                        @foreach($validImages as $image)
                                            <td style="width: {{ 100/$imageCount }}%;">
                                                <div class="evidence-img-wrapper">
                                                    <img src="{{ public_path('storage/' . $image) }}" class="evidence-img" alt="Evidence">
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                </table>
                            @else
                                <span style="color: #a0aec0; font-size: 8pt; display: flex; align-items: center; justify-content: center; height: 100px;">No evidence images</span>
                            @endif
                        </td>
                    </tr>

                </table>

            @endforeach

            @if(!$loop->last || !$loop->parent->last)
                <div class="page-break"></div>
            @endif

        @endforeach

    @empty
        <p class="no-defects">Tiada sebarang rekod kecacatan dimasukkan.</p>
    @endforelse

</body>
</html>