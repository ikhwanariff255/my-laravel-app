<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inspection Report - {{ $inspection->title }}</title>
    
    @php
        // Helper function to bypass DomPDF file restrictions securely
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
        
        /* ================= FOOTER & PAGING ================= */
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
        
        .footer-table { width: 100%; border: none; font-size: 8.5pt; font-weight: bold; color: #4a5568;}
        .footer-table td { border: none; padding: 0; }
        .page-number:after { content: counter(page); }

        /* ================= MUKA DEPAN ================= */
        .info-label { font-size: 9pt; font-weight: bold; color: #4a5568; margin-top: 18px; text-transform: uppercase;}
        .info-value { font-size: 11pt; font-weight: bold; margin-bottom: 2px; text-transform: uppercase; color: #000; line-height: 1.2;}

        /* ================= TABLE OF CONTENTS ================= */
        .toc-title { font-size: 20pt; font-weight: bold; margin-bottom: 20px; }
        .toc-table { width: 100%; border-collapse: collapse; font-size: 11pt; }

        /* ================= PELAN ================= */
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

        /* ================= HALAMAN KHAS LOKASI ================= */
        .loc-intro-container { text-align: center; padding-top: 10px; }
        .loc-title { font-size: 15pt; font-weight: bold; text-transform: uppercase; color: #1a365d; margin-bottom: 3px; }
        .loc-subtitle { font-size: 10pt; color: #4a5568; margin-bottom: 15px; font-weight: bold; }
        .loc-plan-box { border: 1px solid #cbd5e0; padding: 6px; background: #fff; display: inline-block; }
        .loc-plan-img { max-width: 100%; max-height: 450px; width: auto; height: auto; }

        /* ================= KOTAK JADUAL INDIVIDU DEFECT ================= */
        .defect-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            border: 1.5px solid #000;
        }

        .defect-box th,
        .defect-box td {
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

        .col-location { width: 65px; }
        .col-element { width: 70px; }
        .col-defect { width: 110px; }
        .col-remarks { width: auto; }

        .data-row td { padding: 3px 5px !important; }

        .data-location { 
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif !important; 
    font-weight: 800 !important; /* Semi-bold (cukup-cukup rasa) */
    text-transform: none !important; 
    color: #2d3748; 
}

.data-element { 
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif !important; 
    font-weight: 800 !important; /* Semi-bold (cukup-cukup rasa) */
    text-transform: none !important; 
    color: #2d3748; 
}
        .data-defect { color: #c53030; font-weight: bold; }
        .data-remarks { color: #2d3748; word-wrap: break-word; }

        /* ================= BAHAGIAN GAMBAR ================= */
        .image-row td {
            padding: 3px 4px !important;
            vertical-align: middle;
            height: 135px;
        }

        .map-cell {
            text-align: center;
            vertical-align: middle !important;
            height: 135px;
            padding: 3px 4px !important;
            background-color: #fafafa;
        }

        .evidence-cell {
            padding: 3px 4px !important;
            vertical-align: middle;
            height: 135px;
            background-color: #fafafa;
        }

        .map-img {
            max-width: 100%;
            max-height: 125px;
            width: auto;
            height: auto;
            display: block;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            object-fit: contain;
        }

        .inner-evidence-table {
            width: 100%;
            height: 130px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .inner-evidence-table td {
            border: none;
            padding: 0 2px;
            vertical-align: middle;
            text-align: center;
            height: 130px;
        }

        .evidence-img-wrapper {
            width: 100%;
            height: 130px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .evidence-img {
            max-width: 100%;
            max-height: 125px;
            width: auto;
            height: auto;
            display: block;
            object-fit: contain;
            border-radius: 2px;
            border: 1px solid #cbd5e0;
        }

        .no-defects {
            text-align: center;
            color: #718096;
            padding: 30px;
            font-size: 11pt;
        }

        @media print {
            .page-break { page-break-after: always; }
        }
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

    <!-- ================= 1. MUKA DEPAN ================= -->
    <div style="position: absolute; top: -50px; left: -50px; width: 850px; height: 1200px; z-index: 100; background-color: #fff;">
        <table style="width: 100%; height: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td style="width: 42%; background-color: #ffffff; vertical-align: top; padding: 50px 30px 30px 45px; position: relative;">
                    @php
                        $logoPath = public_path('assets/img/defectguru_logo.png');
                    @endphp

                    @if(file_exists($logoPath))
                        <img src="{{ getBase64Image($logoPath) }}" style="width: 100%; max-width: 240px; margin-bottom: 25px; display: block;" alt="Logo">
                    @else
                        <div style="font-size: 22pt; font-weight: bold; margin-bottom: 25px;">{{ $settings->company_name ?? 'DEFECT GURU' }}</div>
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

                    <!-- <div class="info-label">NO. SSM / CIDB:</div>
                    <div class="info-value">{{ $settings->ssm ?? '-' }} / {{ $settings->cidb ?? '-' }}</div> -->
                    
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
                    @php
                        $coverImgPath = null;
                        if(isset($inspection->img) && file_exists(public_path('storage/' . $inspection->img))) {
                            $coverImgPath = public_path('storage/' . $inspection->img);
                        } elseif($inspection->layout_img && file_exists(public_path('storage/' . $inspection->layout_img))) {
                            $coverImgPath = public_path('storage/' . $inspection->layout_img);
                        }
                    @endphp
                    @if($coverImgPath)
                        <img src="{{ getBase64Image($coverImgPath) }}" style="width: 100%; height: 1200px; object-fit: cover; display: block;">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- ================= 2. TABLE OF CONTENTS ================= -->
    <!-- ================= 2. TABLE OF CONTENTS ================= -->
    @php
        $groupedDefects = $inspection->defects->groupBy(function($item) {
            return !empty($item->location) ? strtoupper(trim($item->location)) : 'UNSPECIFIED LOCATION';
        });

        // Muka surat bermula selepas Muka Depan (1), TOC (2), Floor Plan (3), Indication Plan (4) = Mula dari 5
        $currentPage = 5; 
        $pageMapping = [];

        foreach($groupedDefects as $location => $defects) {
            $pageMapping[$location] = $currentPage;
            
            // 1 halaman untuk pengenalan lokasi (Location Map intro)
            $currentPage++; 
            
            // Kira berapa halaman yang diambil oleh jadual defects (4 defects setiap muka surat / chunk)
            $chunks = $defects->chunk(4);
            $currentPage += $chunks->count();
        }
    @endphp

    <div class="toc-title">Contents</div>
    <table class="toc-table">
        @foreach($pageMapping as $location => $pageNo)
            <tr>
                <td style="padding: 8px 0; white-space: nowrap;">Location: {{ $location }}</td>
                <td style="padding: 8px 10px; width: 99%; vertical-align: middle;">
                    <div style="border-bottom: 1.5px dotted #a0aec0; width: 100%; height: 1px;"></div>
                </td>
                <td style="padding: 8px 0; text-align: right; white-space: nowrap;">{{ $pageNo }}</td>
            </tr>
        @endforeach
    </table>

    <div class="page-break"></div>

    <!-- ================= 3. FLOOR PLAN ================= -->
    <div class="section-header">Floor Plan</div>
    @if($inspection->layout_img && file_exists(public_path('storage/' . $inspection->layout_img)))
        <div class="plan-box"><img src="{{ getBase64Image(public_path('storage/' . $inspection->layout_img)) }}" class="plan-img"></div>
    @endif

    <div class="page-break"></div>

    <!-- ================= 4. INDICATION PLAN ================= -->
    <div class="section-header">Indication Plan</div>
    @php
        $indPath = null;
        if(isset($indicatedPath) && file_exists($indicatedPath)) {
            $indPath = $indicatedPath;
        } elseif(isset($indicatedPath) && file_exists(public_path('storage/' . $indicatedPath))) {
            $indPath = public_path('storage/' . $indicatedPath);
        } elseif($inspection->layout_img && file_exists(public_path('storage/' . $inspection->layout_img))) {
            $indPath = public_path('storage/' . $inspection->layout_img);
        }
    @endphp
    @if($indPath)
        <div class="plan-box"><img src="{{ getBase64Image($indPath) }}" class="plan-img"></div>
    @endif

    <div class="page-break"></div>

    <!-- ================= 5. LOKASI & JADUAL INDIVIDU ================= -->
    @forelse($groupedDefects as $location => $defects)

        <!-- HALAMAN KHAS PENGENALAN LOKASI -->
        <div class="loc-intro-container">
            <div class="loc-title">LOCATION: {{ $location }}</div>
            <div class="loc-subtitle">DEFECT SPOTTED: {{ $defects->count() }} Defects spotted</div>
            <div class="loc-plan-box">
                @php
                    $locMapPath = null;
                    // Semak sama ada $locationMaps[$location] wujud dan sah
                    if (isset($locationMaps[$location])) {
                        $mapFile = $locationMaps[$location];
                        if (file_exists($mapFile)) {
                            $locMapPath = $mapFile;
                        } elseif (file_exists(public_path('storage/' . $mapFile))) {
                            $locMapPath = public_path('storage/' . $mapFile);
                        }
                    }
                    
                    // Fallback ke indicatedPath atau layout_img jika tiada peta khusus lokasi
                    if (!$locMapPath) {
                        if (isset($indicatedPath) && file_exists($indicatedPath)) {
                            $locMapPath = $indicatedPath;
                        } elseif (isset($indicatedPath) && file_exists(public_path('storage/' . $indicatedPath))) {
                            $locMapPath = public_path('storage/' . $indicatedPath);
                        } elseif ($inspection->layout_img && file_exists(public_path('storage/' . $inspection->layout_img))) {
                            $locMapPath = public_path('storage/' . $inspection->layout_img);
                        }
                    }
                @endphp

                @if($locMapPath)
                    <img src="{{ getBase64Image($locMapPath) }}" class="loc-plan-img" alt="Location Map with Defects">
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
                    <tr>
                        <td colspan="4" class="defect-header">DEFECT {{ $globalNo++ }}</td>
                    </tr>
                    <tr>
                        <th class="sub-header col-location">LOCATION</th>
                        <th class="sub-header col-element">ELEMENT</th>
                        <th class="sub-header col-defect">DEFECT</th>
                        <th class="sub-header col-remarks">REMARKS</th>
                    </tr>
                    <tr class="data-row">
                        <td class="data-location col-location">{{ ucfirst(strtolower($defect->location)) }}</td>
                        <td class="data-element col-element">{{ ucfirst(strtolower($defect->category)) }}</td>
                        <td class="data-defect col-defect" style="color: red; font-weight: bold;">{{ $defect->defect }}</td>
                        <td class="data-remarks col-remarks">{!! nl2br(e($defect->desc)) !!}</td>
                    </tr>
                    <tr class="image-row">
                        <!-- Map Cell -->
                        <td colspan="2" class="map-cell" style="width: 135px;">
                            @if(isset($defect->single_map_path) && file_exists($defect->single_map_path))
                                <img src="{{ getBase64Image($defect->single_map_path) }}" class="map-img" alt="Location Map">
                            @elseif(isset($defect->single_map_path) && file_exists(public_path('storage/' . $defect->single_map_path)))
                                <img src="{{ getBase64Image(public_path('storage/' . $defect->single_map_path)) }}" class="map-img" alt="Location Map">
                            @else
                                <span style="color: #a0aec0; font-size: 8pt;">No map</span>
                            @endif
                        </td>

                        <!-- Evidence Cell -->
                        <td colspan="2" class="evidence-cell">
                            @php
                                $images = ($defect->img && is_array($defect->img)) ? array_slice($defect->img, 0, 4) : [];
                                $validImages = [];
                                foreach($images as $image) {
                                    $imgFullPath = public_path('storage/' . $image);
                                    if(file_exists($imgFullPath)) {
                                        $validImages[] = $imgFullPath;
                                    }
                                }
                                $imageCount = count($validImages);
                            @endphp

                            @if($imageCount > 0)
                                <table class="inner-evidence-table">
                                    <tr>
                                        @foreach($validImages as $imgFullPath)
                                            <td style="width: {{ 100/$imageCount }}%;">
                                                <div class="evidence-img-wrapper">
                                                    <img src="{{ getBase64Image($imgFullPath) }}" class="evidence-img" alt="Evidence">
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                </table>
                            @else
                                <span style="color: #a0aec0; font-size: 8pt; display: flex; align-items: center; justify-content: center; height: 130px;">No evidence images</span>
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