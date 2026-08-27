<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Inspection;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InspectionController extends Controller
{
    // Paparkan borang pendaftaran inspection

    public function staffs()
    {
        return $this->belongsToMany(User::class, 'inspection_user', 'inspection_id', 'user_id');
    }
    public function create()
    {
        $staffs = User::all();
        return view('inspections.create', compact('staffs'));
    }

    // Simpan data dari borang ke dalam database
    // Simpan data dari borang ke dalam database
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'clientname' => 'required|string|max:255',
            'user_id' => 'required|array', // Pastikan ia array
            'user_id.*' => 'exists:users,id',
            'address' => 'required|string',
            'state' => 'required|string',
            'type' => 'required|string',
            'cropped_image' => 'nullable|string',
            'cropped_layout' => 'nullable|string',
        ]);

        Storage::disk('public')->makeDirectory('inspections');

        $imgPath = null;
        $layoutPath = null;

        if ($request->filled('cropped_image')) {
            $base64Image = $request->cropped_image;
            if (str_contains($base64Image, 'data:image')) {
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            }
            $imageDecoded = base64_decode($base64Image);
            if ($imageDecoded !== false) {
                $filename = 'home_' . time() . '_' . Str::random(5) . '.jpg';
                Storage::disk('public')->put('inspections/' . $filename, $imageDecoded);
                $imgPath = 'inspections/' . $filename;
            }
        }

        if ($request->filled('cropped_layout')) {
            $base64Layout = $request->cropped_layout;
            if (str_contains($base64Layout, 'data:image')) {
                $base64Layout = substr($base64Layout, strpos($base64Layout, ',') + 1);
            }
            $layoutDecoded = base64_decode($base64Layout);
            if ($layoutDecoded !== false) {
                $filename = 'layout_' . time() . '_' . Str::random(5) . '.jpg';
                Storage::disk('public')->put('inspections/' . $filename, $layoutDecoded);
                $layoutPath = 'inspections/' . $filename;
            }
        }

        // Cipta inspection
        $inspection = Inspection::create([
            'user_id'    => Auth::id(),
            'title' => $request->title,
            'clientname' => $request->clientname,
            'address' => $request->address,
            'state' => $request->state,
            'type' => $request->type,
            'img' => $imgPath,
            'layout_img' => $layoutPath,
        ]);

        // Simpan multiple staf ke pivot table
        $inspection->staffs()->attach($request->user_id);

        return redirect()->route('inspection.index')->with('success', 'Projek pemeriksaan berjaya didaftarkan!');
    }

    // Paparkan butiran terperinci projek inspection
    public function show(Inspection $inspection)
    {
        // Load defects dengan pagination (5 rekod setiap halaman)
        $defects = $inspection->defects()->paginate(5);

        return view('inspections.show', compact('inspection', 'defects'));
    }

    // Paparkan borang edit projek
    public function edit(Inspection $inspection)
    {
        $staffs = User::all();
        return view('inspections.edit', compact('inspection', 'staffs'));
    }

    // Simpan kemaskini data projek
    public function update(Request $request, Inspection $inspection)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'clientname' => 'required|string|max:255',
            'user_id' => 'required|array',
            'user_id.*' => 'exists:users,id',
            'address' => 'required|string',
            'state' => 'required|string',
            'type' => 'required|string',
            'cropped_image' => 'nullable|string',
            'cropped_layout' => 'nullable|string',
        ]);

        $imgPath = $inspection->img;
        $layoutPath = $inspection->layout_img;

        if ($request->filled('cropped_image')) {
            $base64Image = $request->cropped_image;
            if (str_contains($base64Image, 'data:image')) {
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            }
            $imageDecoded = base64_decode($base64Image);
            if ($imageDecoded !== false) {
                if ($inspection->img && Storage::disk('public')->exists($inspection->img)) {
                    Storage::disk('public')->delete($inspection->img);
                }
                $filename = 'home_' . time() . '_' . Str::random(5) . '.jpg';
                Storage::disk('public')->put('inspections/' . $filename, $imageDecoded);
                $imgPath = 'inspections/' . $filename;
            }
        }

        if ($request->filled('cropped_layout')) {
            $base64Layout = $request->cropped_layout;
            if (str_contains($base64Layout, 'data:image')) {
                $base64Layout = substr($base64Layout, strpos($base64Layout, ',') + 1);
            }
            $layoutDecoded = base64_decode($base64Layout);
            if ($layoutDecoded !== false) {
                if ($inspection->layout_img && Storage::disk('public')->exists($inspection->layout_img)) {
                    Storage::disk('public')->delete($inspection->layout_img);
                }
                $filename = 'layout_' . time() . '_' . Str::random(5) . '.jpg';
                Storage::disk('public')->put('inspections/' . $filename, $layoutDecoded);
                $layoutPath = 'inspections/' . $filename;
            }
        }

        $inspection->update([
            'title' => $request->title,
            'clientname' => $request->clientname,
            'address' => $request->address,
            'state' => $request->state,
            'type' => $request->type,
            'img' => $imgPath,
            'layout_img' => $layoutPath,
        ]);

        // Kemaskini senarai staf (sync menggantikan yang lama dengan pilihan baru)
        $inspection->staffs()->sync($request->user_id);

        return redirect()->route('inspection.index')->with('success', 'Maklumat projek berjaya dikemaskini!');
    }

    // Fungsi untuk memuat turun PDF laporan
    public function downloadPDF($id, $template_type)
    {
        $inspection = Inspection::with('defects', 'user')->findOrFail($id);
        $settings = CompanySetting::first();

        $indicatedPath = null;
        $sourcePath = $inspection->layout_img ? storage_path('app/public/' . $inspection->layout_img) : null;

        $locationMaps = [];

        if ($sourcePath && file_exists($sourcePath)) {
            $imgData = @file_get_contents($sourcePath);
            $img = @imagecreatefromstring($imgData);

            if ($img) {
                $width = imagesx($img);
                $height = imagesy($img);
                $red = imagecolorallocate($img, 255, 0, 0);
                $white = imagecolorallocate($img, 255, 255, 255);
                $markerSize = max(15, round($width / 50));

                // 1. Jana Indicated Layout (Pelan Utama Bertitik Penuh)
                foreach ($inspection->defects as $defect) {
                    if ($defect->mark_x > 0 || $defect->mark_y > 0) {
                        $px = ($defect->mark_x / 100) * $width;
                        $py = ($defect->mark_y / 100) * $height;
                        imagefilledellipse($img, $px, $py, $markerSize, $markerSize, $red);
                        imageellipse($img, $px, $py, $markerSize, $markerSize, $white);
                    }
                }

                $indicatedFilename = 'indicated_' . $id . '_' . time() . '.jpg';
                $indicatedFullPath = storage_path('app/public/inspections/' . $indicatedFilename);
                imagejpeg($img, $indicatedFullPath, 90);
                $indicatedPath = 'inspections/' . $indicatedFilename;
                imagedestroy($img);

                // 1B. Jana Location-Specific Indication Maps (Hanya Defect Lokasi Tersebut)
                $groupedDefects = $inspection->defects->groupBy(function($item) {
                    return !empty($item->location) ? strtoupper(trim($item->location)) : 'UNSPECIFIED LOCATION';
                });

                foreach ($groupedDefects as $location => $defects) {
                    $locImg = @imagecreatefromstring(@file_get_contents($sourcePath));
                    if ($locImg) {
                        $lW = imagesx($locImg);
                        $lH = imagesy($locImg);
                        $lRed = imagecolorallocate($locImg, 255, 0, 0);
                        $lWhite = imagecolorallocate($locImg, 255, 255, 255);
                        $lMarkerSize = max(15, round($lW / 50));

                        foreach ($defects as $defect) {
                            if ($defect->mark_x > 0 || $defect->mark_y > 0) {
                                $lPx = ($defect->mark_x / 100) * $lW;
                                $lPy = ($defect->mark_y / 100) * $lH;
                                imagefilledellipse($locImg, $lPx, $lPy, $lMarkerSize, $lMarkerSize, $lRed);
                                imageellipse($locImg, $lPx, $lPy, $lMarkerSize, $lMarkerSize, $lWhite);
                            }
                        }

                        $locFilename = 'indicated_loc_' . md5($location) . '_' . $id . '_' . time() . '.jpg';
                        $locFullPath = storage_path('app/public/inspections/' . $locFilename);
                        imagejpeg($locImg, $locFullPath, 90);
                        $locationMaps[$location] = 'inspections/' . $locFilename;
                        imagedestroy($locImg);
                    }
                }
            }

            // 2. Jana Peta Mini Individu (Single Marker Map) untuk setiap Defect
            foreach ($inspection->defects as $defect) {
                if ($defect->mark_x > 0 || $defect->mark_y > 0) {
                    $singleImg = @imagecreatefromstring(@file_get_contents($sourcePath));
                    if ($singleImg) {
                        $sW = imagesx($singleImg);
                        $sH = imagesy($singleImg);
                        $sRed = imagecolorallocate($singleImg, 255, 0, 0);
                        $sWhite = imagecolorallocate($singleImg, 255, 255, 255);
                        $sSize = max(25, round($sW / 30)); // Saiz titik lebih besar sikit untuk peta mini

                        $sPx = ($defect->mark_x / 100) * $sW;
                        $sPy = ($defect->mark_y / 100) * $sH;
                        imagefilledellipse($singleImg, $sPx, $sPy, $sSize, $sSize, $sRed);
                        imageellipse($singleImg, $sPx, $sPy, $sSize, $sSize, $sWhite);

                        $mapFilename = 'map_defect_' . $defect->id . '.jpg';
                        $mapFullPath = storage_path('app/public/inspections/' . $mapFilename);
                        imagejpeg($singleImg, $mapFullPath, 90);
                        imagedestroy($singleImg);

                        // Simpan temporary path pada objek defect
                        $defect->single_map_path = 'inspections/' . $mapFilename;
                    }
                }
            }
        }

        if ($template_type == 'template1') {
            $pdf = Pdf::loadView('inspections.pdf_template_1', compact('inspection', 'settings', 'indicatedPath'));
        } else {
            $pdf = Pdf::loadView('inspections.pdf_template_2', compact('inspection', 'settings', 'indicatedPath', 'locationMaps'));
        }

        $pdf->setPaper('A4', 'portrait');
        $response = $pdf->download('Laporan_Defect_' . str_replace(' ', '_', $inspection->title) . '.pdf');

        // Cleanup fail sementara
        if ($indicatedPath && file_exists(storage_path('app/public/' . $indicatedPath))) {
            @unlink(storage_path('app/public/' . $indicatedPath));
        }
        foreach ($locationMaps as $locMap) {
            if (file_exists(storage_path('app/public/' . $locMap))) {
                @unlink(storage_path('app/public/' . $locMap));
            }
        }
        foreach ($inspection->defects as $defect) {
            if (isset($defect->single_map_path) && file_exists(storage_path('app/public/' . $defect->single_map_path))) {
                @unlink(storage_path('app/public/' . $defect->single_map_path));
            }
        }

        return $response;
    }

    // Paparkan senarai semua projek inspection
    public function index(Request $request)
    {
        // 1. Mula bina query asas
        $query = Inspection::with('user')->latest();

        // 2. Logik Carian (Search bar) - Cari nama projek, klien, atau alamat
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('clientname', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // 3. Logik Tapisan (Filter Dropdown) - Tapis mengikut jenis hartanah
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // 4. Paginate 10 rekod dan KEKALKAN parameter carian pada URL (supaya bila tekan page 2, carian tak hilang)
        $inspections = $query->paginate(10)->appends($request->all());

        return view('inspections.index', compact('inspections'));
    }
}
