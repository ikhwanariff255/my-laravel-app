<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Inspection;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InspectionController extends Controller
{
    public function staffs()
    {
        return $this->belongsToMany(User::class, 'inspection_user', 'inspection_id', 'user_id');
    }

    public function create()
    {
        $currentUser = Auth::user();

        // Jika Super Admin, boleh pilih semua staf. Jika Company Admin, hanya staf syarikat sendiri.
        if ($currentUser->role === 'admin') {
            $staffs = User::all();
        } else {
            $staffs = User::where('company_id', $currentUser->company_id)->get();
        }

        return view('inspections.create', compact('staffs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'clientname' => 'required|string|max:255',
            'user_id' => 'required|array',
            'user_id.*' => 'exists:users,id',
            'address' => 'required|string',
            'state' => 'required|string',
            'type' => 'required|string',
            'template_id' => 'nullable|exists:templates,id',
            'cropped_image' => 'nullable|string',
            'cropped_layout' => 'nullable|string',
        ]);

        $user = Auth::user();

        if (!$user->company_id) {
            return back()->with('error', 'Akaun anda tidak diikat pada sebarang syarikat.');
        }

        $imgPath = null;
        $layoutPath = null;

        if ($request->filled('cropped_image')) {
            $base64Image = $request->cropped_image;
            if (str_contains($base64Image, 'data:image')) {
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            }
            $imageDecoded = base64_decode($base64Image);
            if ($imageDecoded !== false) {
                $filename = 'home_'.time().'_'.Str::random(5).'.jpg';
                Storage::disk('s3')->put('inspections/'.$filename, $imageDecoded);
                $imgPath = 'inspections/'.$filename;
            }
        }

        if ($request->filled('cropped_layout')) {
            $base64Layout = $request->cropped_layout;
            if (str_contains($base64Layout, 'data:image')) {
                $base64Layout = substr($base64Layout, strpos($base64Layout, ',') + 1);
            }
            $layoutDecoded = base64_decode($base64Layout);
            if ($layoutDecoded !== false) {
                $filename = 'layout_'.time().'_'.Str::random(5).'.jpg';
                Storage::disk('s3')->put('inspections/'.$filename, $layoutDecoded);
                $layoutPath = 'inspections/'.$filename;
            }
        }

        $inspection = Inspection::create([
            'company_id'      => $user->company_id,
            'user_id'         => $user->id,
            'template_id'     => $request->template_id,
            'title'           => $request->title,
            'clientname'      => $request->clientname,
            'address'         => $request->address,
            'state'           => $request->state,
            'type'            => $request->type,
            'img'             => $imgPath,
            'layout_img'      => $layoutPath,
            'cus_no'          => $request->cus_no,
            'cus_email'       => $request->cus_email,
            'inspection_date' => $request->inspection_date,
        ]);

        $inspection->staffs()->attach($request->user_id);

        return redirect()->route('inspection.index')->with('success', 'Projek pemeriksaan berjaya didaftarkan!');
    }

    public function show(Inspection $inspection)
    {
        $defects = $inspection->defects()->paginate(5);
        return view('inspections.show', compact('inspection', 'defects'));
    }

    public function edit(Inspection $inspection)
    {
        $currentUser = Auth::user();

        if ($currentUser->role === 'admin') {
            $staffs = User::all();
        } else {
            $staffs = User::where('company_id', $currentUser->company_id)->get();
        }

        return view('inspections.edit', compact('inspection', 'staffs'));
    }

    public function update(Request $request, Inspection $inspection)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'clientname' => 'required|string|max:255',
            'cus_no' => 'nullable|string|max:255',
            'cus_email' => 'nullable|email|max:255',
            'inspection_date' => 'nullable|date',
            'user_id' => 'required|array',
            'user_id.*' => 'exists:users,id',
            'address' => 'required|string',
            'state' => 'required|string',
            'type' => 'required|string',
            'template_id' => 'nullable|exists:templates,id',
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
                if ($inspection->img && Storage::disk('s3')->exists($inspection->img)) {
                    Storage::disk('s3')->delete($inspection->img);
                }
                $filename = 'home_'.time().'_'.Str::random(5).'.jpg';
                Storage::disk('s3')->put('inspections/'.$filename, $imageDecoded);
                $imgPath = 'inspections/'.$filename;
            }
        }

        if ($request->filled('cropped_layout')) {
            $base64Layout = $request->cropped_layout;
            if (str_contains($base64Layout, 'data:image')) {
                $base64Layout = substr($base64Layout, strpos($base64Layout, ',') + 1);
            }
            $layoutDecoded = base64_decode($base64Layout);
            if ($layoutDecoded !== false) {
                if ($inspection->layout_img && Storage::disk('s3')->exists($inspection->layout_img)) {
                    Storage::disk('s3')->delete($inspection->layout_img);
                }
                $filename = 'layout_'.time().'_'.Str::random(5).'.jpg';
                Storage::disk('s3')->put('inspections/'.$filename, $layoutDecoded);
                $layoutPath = 'inspections/'.$filename;
            }
        }

        $inspection->update([
            'title' => $request->title,
            'clientname' => $request->clientname,
            'cus_no' => $request->cus_no,
            'cus_email' => $request->cus_email,
            'inspection_date' => $request->inspection_date,
            'address' => $request->address,
            'state' => $request->state,
            'type' => $request->type,
            'template_id' => $request->template_id,
            'img' => $imgPath,
            'layout_img' => $layoutPath,
        ]);

        $inspection->staffs()->sync($request->user_id);

        return redirect()->route('inspection.index')->with('success', 'Maklumat projek berjaya dikemaskini!');
    }

    /**
     * Generate PDF with parallel S3 downloads and memory optimisation
     */
    public function downloadPDF($id)
    {
        $currentUser = Auth::user();
        $company = $currentUser->company; 

        if (!$company || !$company->package) {
            return back()->with('error', 'Syarikat anda tidak mempunyai pakej langganan yang sah.');
        }

        $tokensLeft = $company->tokens_left ?? 0;        

        // SEMAKAN: Semak baki token setiap kali butang generate ditekan
        if ($tokensLeft <= 0) {
            return redirect()->back()->with('error', 'Baki token anda adalah 0. Sila topup token untuk mencetak laporan.');
        } else {
            // Tolak 1 token serta-merta setiap kali PDF di-generate
            $company->decrement('tokens_left');
        }

        // Increase memory and execution time
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        $startTime = microtime(true);
        $inspection = Inspection::findOrFail($id);

        // Increase memory and execution time
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        $startTime = microtime(true);
        $inspection = Inspection::findOrFail($id);

        // ==================== SECTION 1: Process Layout Images ====================
        $locationMaps = [];
        $indicatedPath = null;
        $plainLayoutPath = null;
        $imgData = null;

        if ($inspection->layout_img && Storage::disk('s3')->exists($inspection->layout_img)) {
            $imgData = Storage::disk('s3')->get($inspection->layout_img);
        }

        Storage::disk('public')->makeDirectory('inspections');

        if ($imgData) {
            // Save plain layout (no markers)
            $plainLayoutPath = storage_path('app/public/inspections/plain_layout_'.$id.'.jpg');
            file_put_contents($plainLayoutPath, $imgData);

            // Create indicated layout with markers
            $img = @imagecreatefromstring($imgData);
            if ($img) {
                $width = imagesx($img);
                $height = imagesy($img);
                $red = imagecolorallocate($img, 255, 0, 0);
                $white = imagecolorallocate($img, 255, 255, 255);
                $markerSize = max(15, round($width / 50));

                foreach ($inspection->defects as $defect) {
                    if ($defect->mark_x > 0 || $defect->mark_y > 0) {
                        $px = ($defect->mark_x / 100) * $width;
                        $py = ($defect->mark_y / 100) * $height;
                        imagefilledellipse($img, $px, $py, $markerSize, $markerSize, $red);
                        imageellipse($img, $px, $py, $markerSize, $markerSize, $white);
                    }
                }

                $indicatedFilename = 'indicated_'.$id.'_'.time().'.jpg';
                $indicatedPath = storage_path('app/public/inspections/'.$indicatedFilename);
                imagejpeg($img, $indicatedPath, 85);
                imagedestroy($img);

                // Location-specific maps
                $groupedDefects = $inspection->defects->groupBy(function ($item) {
                    return ! empty($item->location) ? strtoupper(trim($item->location)) : 'UNSPECIFIED LOCATION';
                });

                foreach ($groupedDefects as $location => $defects) {
                    $locImg = @imagecreatefromstring($imgData);
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

                        $locFilename = 'indicated_loc_'.md5($location).'_'.$id.'_'.time().'.jpg';
                        $locFullPath = storage_path('app/public/inspections/'.$locFilename);
                        imagejpeg($locImg, $locFullPath, 85);
                        $locationMaps[$location] = $locFullPath;
                        imagedestroy($locImg);
                    }
                }

                // Individual defect maps
                foreach ($inspection->defects as $defect) {
                    if ($defect->mark_x > 0 || $defect->mark_y > 0) {
                        $singleImg = @imagecreatefromstring($imgData);
                        if ($singleImg) {
                            $sW = imagesx($singleImg);
                            $sH = imagesy($singleImg);
                            $sRed = imagecolorallocate($singleImg, 255, 0, 0);
                            $sWhite = imagecolorallocate($singleImg, 255, 255, 255);
                            $sSize = max(25, round($sW / 30));

                            $sPx = ($defect->mark_x / 100) * $sW;
                            $sPy = ($defect->mark_y / 100) * $sH;
                            imagefilledellipse($singleImg, $sPx, $sPy, $sSize, $sSize, $sRed);
                            imageellipse($singleImg, $sPx, $sPy, $sSize, $sSize, $sWhite);

                            $mapFilename = 'map_defect_'.$defect->id.'.jpg';
                            $mapFullPath = storage_path('app/public/inspections/'.$mapFilename);
                            imagejpeg($singleImg, $mapFullPath, 85);
                            imagedestroy($singleImg);

                            $defect->single_map_path = $mapFullPath;
                        }
                    }
                }
            }
        }

        // ==================== SECTION 2: Download Cover Image (parallel) ====================
        $coverPromise = null;
        $tempCoverImage = null;
        $client = new Client();

        if ($inspection->img && Storage::disk('s3')->exists($inspection->img)) {
            $s3Client = Storage::disk('s3')->getClient();
            $coverPromise = $s3Client->getObjectAsync([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key'    => $inspection->img,
            ])->then(
                function ($result) use (&$tempCoverImage) {
                    $tempCoverImage = $this->compressAndSaveImage((string) $result['Body'], 70);
                    return $tempCoverImage;
                },
                function ($reason) {
                    Log::error("Cover image download failed: ".$reason);
                    return null;
                }
            );
        }

        // ==================== SECTION 3: Download Evidence Images in Parallel ====================
        $evidenceMap = $this->downloadEvidenceImagesParallel($inspection);

        // Wait for cover download to finish (if any)
        if ($coverPromise) {
            $tempCoverImage = $coverPromise->wait();
            if ($tempCoverImage) {
                $inspection->local_cover = $tempCoverImage;
            }
        }

        // Assign local_evidence to each defect
        $tempEvidenceFiles = [];
        foreach ($inspection->defects as $defect) {
            if (isset($evidenceMap[$defect->id])) {
                $defect->local_evidence = $evidenceMap[$defect->id];
                $tempEvidenceFiles = array_merge($tempEvidenceFiles, $evidenceMap[$defect->id]);
            } else {
                $defect->local_evidence = [];
            }
        }

        // ==================== SECTION 4: Generate PDF ====================
        $template = $inspection->template;
        $viewFile = $template ? $template->view_file : 'pdf_template_2';

        $pdf = Pdf::setOptions(['isRemoteEnabled' => true]);
        $pdf->loadView('inspections.'.$viewFile, compact('inspection', 'indicatedPath', 'locationMaps', 'plainLayoutPath'));
        $pdf->setPaper('A4', 'portrait');

        $safeTitle = str_replace([' ', '/', '\\'], '_', $inspection->title);
        $response = $pdf->download('Laporan_Defect_'.$safeTitle.'.pdf');

        // ==================== SECTION 5: Cleanup ====================
        $this->cleanupTempFiles($indicatedPath, $locationMaps, $inspection, $tempCoverImage, $tempEvidenceFiles, $plainLayoutPath);

        $endTime = microtime(true);
        Log::info("PDF Generation Time: ".round($endTime - $startTime, 2)."s (ID: {$id})");

        return $response;
    }

    /**
     * Parallel download of evidence images with compression
     */
    protected function downloadEvidenceImagesParallel(Inspection $inspection): array
    {
        $tempEvidenceImages = [];
        $s3Client = Storage::disk('s3')->getClient();
        $promises = [];

        foreach ($inspection->defects as $defect) {
            $tempEvidenceImages[$defect->id] = [];
            if (!empty($defect->img) && is_array($defect->img)) {
                $images = array_slice($defect->img, 0, 4);
                foreach ($images as $s3Path) {
                    if (Storage::disk('s3')->exists($s3Path)) {
                        $promise = $s3Client->getObjectAsync([
                            'Bucket' => config('filesystems.disks.s3.bucket'),
                            'Key'    => $s3Path,
                        ])->then(
                            function ($result) use ($defect) {
                                $tempFile = $this->compressAndSaveImage((string) $result['Body'], 65);
                                if ($tempFile) {
                                    return ['defect_id' => $defect->id, 'path' => $tempFile];
                                }
                                return null;
                            },
                            function ($reason) use ($s3Path) {
                                Log::error("S3 download failed: {$s3Path} - ".$reason);
                                return null;
                            }
                        );
                        $promises[] = $promise;
                    }
                }
            }
        }

        if (!empty($promises)) {
            try {
                $results = Promise\Utils::settle($promises)->wait();
                foreach ($results as $result) {
                    if ($result['state'] === 'fulfilled' && $result['value'] !== null) {
                        $data = $result['value'];
                        $tempEvidenceImages[$data['defect_id']][] = $data['path'];
                    }
                }
            } catch (\Exception $e) {
                Log::error("Parallel download failed, falling back to sequential: ".$e->getMessage());
                return $this->downloadEvidenceImagesSequential($inspection);
            }
        }

        return $tempEvidenceImages;
    }

    /**
     * Sequential download of evidence images (fallback)
     */
    protected function downloadEvidenceImagesSequential(Inspection $inspection): array
    {
        $tempEvidenceImages = [];

        foreach ($inspection->defects as $defect) {
            $localPaths = [];

            if (!empty($defect->img) && is_array($defect->img)) {
                foreach (array_slice($defect->img, 0, 4) as $s3Path) {
                    if (Storage::disk('s3')->exists($s3Path)) {
                        try {
                            $imgContent = Storage::disk('s3')->get($s3Path);
                            $tempFile = $this->compressAndSaveImage($imgContent, 65);
                            if ($tempFile) {
                                $localPaths[] = $tempFile;
                            }
                        } catch (\Exception $e) {
                            Log::error("Error downloading {$s3Path}: ".$e->getMessage());
                        }
                    }
                }
            }

            $tempEvidenceImages[$defect->id] = $localPaths;
        }

        return $tempEvidenceImages;
    }

    /**
     * Compress an image and save as JPEG
     */
    protected function compressAndSaveImage($imageData, $quality = 65)
    {
        try {
            $img = @imagecreatefromstring($imageData);
            if (!$img) {
                return null;
            }
            $tempFile = storage_path('app/public/inspections/img_'.uniqid().'.jpg');
            imagejpeg($img, $tempFile, $quality);
            imagedestroy($img);
            return $tempFile;
        } catch (\Exception $e) {
            Log::error("Image compression failed: ".$e->getMessage());
            return null;
        }
    }

    /**
     * Clean up temporary files after PDF generation
     */
    protected function cleanupTempFiles($indicatedPath, $locationMaps, $inspection, $tempCoverImage, $tempEvidenceFiles, $plainLayoutPath = null): void
    {
        if ($indicatedPath && file_exists($indicatedPath)) {
            @unlink($indicatedPath);
        }

        foreach ($locationMaps as $locMap) {
            if (file_exists($locMap)) {
                @unlink($locMap);
            }
        }

        foreach ($inspection->defects as $defect) {
            if (isset($defect->single_map_path) && file_exists($defect->single_map_path)) {
                @unlink($defect->single_map_path);
            }
        }

        if ($tempCoverImage && file_exists($tempCoverImage)) {
            @unlink($tempCoverImage);
        }

        foreach ($tempEvidenceFiles as $tempFile) {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }

        if ($plainLayoutPath && file_exists($plainLayoutPath)) {
            @unlink($plainLayoutPath);
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user(); 

        $query = Inspection::with('user')->latest();

        if ($user->role !== 'admin') {
            $query->where('company_id', $user->company_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('clientname', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $inspections = $query->paginate(10)->appends($request->all());

        return view('inspections.index', compact('inspections'));
    }

    public function destroy(Inspection $inspection)
    {
        if ($inspection->img && Storage::disk('s3')->exists($inspection->img)) {
            Storage::disk('s3')->delete($inspection->img);
        }

        if ($inspection->layout_xml && Storage::disk('s3')->exists($inspection->layout_img)) {
            Storage::disk('s3')->delete($inspection->layout_img);
        }

        $inspection->staffs()->detach();
        $inspection->delete();

        return redirect()->route('inspection.index')->with('success', 'Projek pemeriksaan berjaya dipadam!');
    }
}