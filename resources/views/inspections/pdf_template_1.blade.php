<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inspection Report - {{ $inspection->title }}</title>
    
    @php
        // Helper function to bypass DomPDF file restrictions
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
            margin: 30px 30px 70px 30px; 
        }
        
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 10.5pt; 
            color: #000; 
            line-height: 1.4; 
        }
        
        .page-break { page-break-after: always; }
        
        /* ================= FOOTER & PAGING ================= */
        footer { 
            position: fixed; 
            bottom: -40px; 
            left: 0px; 
            right: 0px; 
            height: 30px; 
            border-top: 1px solid #000; 
            padding-top: 5px;
            z-index: 1;
        }
        
        .footer-table { width: 100%; border: none; font-size: 9pt; font-weight: bold; color: #4a5568;}
        .footer-table td { border: none; padding: 0; }
        .page-number:after { content: counter(page); }

        /* ================= MUKA DEPAN ================= */
        .info-label { font-size: 9pt; font-weight: bold; color: #4a5568; margin-top: 18px; text-transform: uppercase;}
        .info-value { font-size: 11pt; font-weight: bold; margin-bottom: 2px; text-transform: uppercase; color: #000; line-height: 1.2;}

        /* ================= TABLE OF CONTENTS ================= */
        .toc-title { font-size: 20pt; font-weight: bold; margin-bottom: 20px; }
        .toc-table { width: 100%; border-collapse: collapse; font-size: 11pt; }

        /* ================= PELAN ================= */
        .section-title { font-size: 16pt; font-weight: bold; margin-bottom: 15px; color: #1a202c; }
        .plan-box { text-align: center; margin-bottom: 20px; }
        .plan-img { max-width: 100%; max-height: 600px; object-fit: contain; }

        /* ================= JADUAL DEFECT ================= */
        .location-heading { font-size: 16pt; font-weight: bold; color: #1a202c; margin-top: 20px; margin-bottom: 15px; }
        
        .defect-table { width: 100%; border-collapse: collapse; border: 1.5px solid #000; margin-bottom: 20px; page-break-inside: avoid; }
        .defect-table th, .defect-table td { border: 1px solid #000; padding: 5px 8px; vertical-align: top; }
        
        .def-header { background-color: #9cb4d4 !important; font-weight: bold; text-align: center; font-size: 11pt; text-transform: uppercase; }
        
        .label-col { width: 20%; font-weight: bold; text-transform: uppercase; }
        .val-col { width: 45%; }
        .map-col { width: 35%; text-align: center; vertical-align: middle; padding: 2px !important; }
        
        .minimap-img { max-width: 160px; max-height: 120px; object-fit: cover; object-position: center; border: 1px solid #cbd5e0; }
        
        .evidence-container { width: 100%; text-align: center; padding: 10px 0; }
        .evidence-table { margin: 0 auto; border: none; }
        .evidence-table td { border: none; padding: 0 5px; text-align: center; }
        .evidence-img { max-width: 200px; max-height: 150px; object-fit: contain; border: 1px solid #e2e8f0; }

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
                <!-- KIRI -->
                <td style="width: 42%; background-color: #ffffff; vertical-align: top; padding: 50px 30px 30px 45px; position: relative;">
                    
                    @php
                        $logoPath = public_path('assets/img/defectguru_logo.png');
                    @endphp

                    @if(file_exists($logoPath))
                        <img src="{{ getBase64Image($logoPath) }}" style="width: 100%; max-width: 240px; margin-bottom: 25px; display: block;" alt="Logo">
                    @else
                        <div style="font-size: 22pt; font-weight: bold; margin-bottom: 25px;"><span style="color:#000;">DEFECT</span><span style="color:#f6ad55;">GURU</span></div>
                    @endif

                    <div style="font-size: 26pt; font-weight: 900; margin-bottom: 20px; line-height: 1.15; color: #000; letter-spacing: -0.5px;">
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
                    
                    <!-- TARIKH & PEGAWAI DI BAWAH -->
                    <div style="margin-top: 40px; width: 100%;">
                        <table style="width: 100%; border: none; font-size: 8.5pt; font-weight: bold; color: #4a5568;">
                            <tr>
                                <td style="border: none; padding: 0; width: 50%;">INSPECTION DATE :</td>
                                <td style="border: none; padding: 0; width: 50%;">INSPECT BY :</td>
                            </tr>
                            <tr>
                                <td style="border: none; padding: 0; color: #000;">
                                    {{ !empty($inspection->inspection_date) ? date('d/m/Y', strtotime($inspection->inspection_date)) : date('d/m/Y') }}
                                </td>
                                <td style="border: none; padding: 0; color: #000;">{{ strtoupper($inspection->user->name ?? 'KAMIL') }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
                
                <!-- KANAN: Gambar Rumah (Col: img) -->
                <td style="width: 58%; background-color: #e2e8f0; padding: 0; vertical-align: top;">
                    @if(!empty($inspection->local_cover) && file_exists($inspection->local_cover))
                        <img src="{{ getBase64Image($inspection->local_cover) }}" style="width: 100%; height: 1200px; object-fit: cover; display: block;">
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
            @php 
                $estPage += ceil($defects->count() / 1.5); 
            @endphp
        @endforeach
    </table>

    <div class="page-break"></div>

    <!-- ================= 3. FLOOR PLAN ================= -->
    <div class="section-title">Floor Plan</div>
    @if($inspection->layout_img && file_exists(public_path('storage/' . $inspection->layout_img)))
        <div class="plan-box">
            <img src="{{ getBase64Image(public_path('storage/' . $inspection->layout_img)) }}" class="plan-img">
        </div>
    @endif

    <div class="page-break"></div>

    <!-- ================= 4. INDICATED LAYOUT PLAN ================= -->
    <div class="section-title">Indicated layout plan</div>
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

    <!-- ================= 5. SENARAI DEFECT ================= -->
    @php
        $globalCounter = 1;
    @endphp

    @forelse($groupedDefects as $location => $defects)
        
        <div class="location-heading">Location: {{ $location }}</div>
        
        @foreach($defects as $defect)
            <table class="defect-table">
                <tr>
                    <th colspan="3" class="def-header">DEFECT {{ $globalCounter }}</th>
                </tr>
                <tr>
                    <td class="label-col">LOCATION</td>
                    <td class="val-col">{{ strtoupper($defect->location) }}</td>
                    <td rowspan="4" class="map-col" style="text-align: center; vertical-align: middle; padding: 5px;">
    
                        @if(isset($defect->single_map_path) && file_exists($defect->single_map_path))
                            <!-- 1. Jika gambar dah ada titik kekal -->
                            <img src="{{ getBase64Image($defect->single_map_path) }}" style="width: 120px; height: auto; display: block; margin: 0 auto; border: 1.5px solid #cbd5e0;">
                            
                        @elseif($inspection->layout_img && file_exists(public_path('storage/' . $inspection->layout_img)))
                            <!-- 2. Fallback: Jika guna pelan kosong asal -->
                            <div style="position: relative; width: 120px; margin: 0 auto;">
                                <img src="{{ getBase64Image(public_path('storage/' . $inspection->layout_img)) }}" style="width: 100%; height: auto; display: block; border: 1.5px solid #cbd5e0;">
                                
                                @if(isset($defect->mark_x) && isset($defect->mark_y) && $defect->mark_x > 0)
                                    <div style="position: absolute; left: {{ $defect->mark_x }}%; top: {{ $defect->mark_y }}%; width: 10px; height: 10px; background: #ef4444; border-radius: 50%; margin-left: -5px; margin-top: -5px; border: 1px solid white;"></div>
                                @endif
                            </div>
                        @endif

                    </td>
                </tr>
                <tr>
                    <td class="label-col">ELEMENT</td>
                    <td class="val-col">{{ strtoupper($defect->category) }}</td>
                </tr>
                <tr>
                    <td class="label-col">DEFECT</td>
                    <td class="val-col" style="color: red; font-weight: bold;">{{ $defect->defect }}</td>
                </tr>
                <tr>
                    <td class="label-col">DESCRIPTION</td>
                    <td class="val-col">{!! nl2br(e($defect->desc)) !!}</td>
                </tr>
                
                <tr>
                    <td colspan="3">
                        <div class="evidence-container">
                            @if(!empty($defect->local_evidence))
                                <table class="evidence-table">
                                    <tr>
                                        @foreach($defect->local_evidence as $localImg)
                                            @if(file_exists($localImg))
                                                <td>
                                                    <img src="{{ getBase64Image($localImg) }}" class="evidence-img">
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                </table>
                            @else
                                <span style="color: #a0aec0; font-size: 9pt;">No image evidence</span>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>

            @php $globalCounter++; @endphp
        @endforeach

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif

    @empty
        <p style="text-align: center; color: #718096; padding: 40px;">No defect records found.</p>
    @endforelse

</body>
</html>