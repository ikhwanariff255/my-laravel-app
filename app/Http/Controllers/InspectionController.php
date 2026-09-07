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
        $staffs = User::all();
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

        $reportLimit = 50;

        $currentMonthCount = Inspection::where('company_id', $user->company_id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        if ($currentMonthCount >= $reportLimit) {
            $company = \App\Models\Company::find($user->company_id);
            if ($company && $company->tokens_left > 0) {
                $company->decrement('tokens_left');
            } else {
                return back()->with('error', 'Had laporan bulanan telah habis. Sila tambah token (RM14/report) atau naik taraf pakej.');
            }
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
        $staffs = User::all();
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
     * OPTIMIZED: Generate PDF with parallel S3 image downloads
     * Reduces timeout issues by downloading all images concurrently instead of sequentially
     */
    public function downloadPDF($id)
    {
        $startTime = microtime(true);
        
        // Fetch inspection with defects
        $inspection = Inspection::findOrFail($id);
        
        // Get company settings
// Get company settings
// $settings = CompanySetting::first();
        // ==================== SECTION 1: Process Layout Images ====================
        $locationMaps = [];
        $indicatedPath = null;
        $imgData = null;

        if ($inspection->layout_img && Storage::disk('s3')->exists($inspection->layout_img)) {
            $imgData = Storage::disk('s3')->get($inspection->layout_img);
        }

        Storage::disk('public')->makeDirectory('inspections');

        if ($imgData) {
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
                imagejpeg($img, $indicatedPath, 90);
                imagedestroy($img);

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
                        imagejpeg($locImg, $locFullPath, 90);
                        $locationMaps[$location] = $locFullPath;
                        imagedestroy($locImg);
                    }
                }
            }

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
                        imagejpeg($singleImg, $mapFullPath, 90);
                        imagedestroy($singleImg);

                        $defect->single_map_path = $mapFullPath;
                    }
                }
            }
        }

        // ==================== SECTION 2: OPTIMIZED - Download Cover Image ====================
        $tempCoverImage = null;
        if ($inspection->img && Storage::disk('s3')->exists($inspection->img)) {
            try {
                $coverData = Storage::disk('s3')->get($inspection->img);
                $tempCoverImage = storage_path('app/public/inspections/cover_'.time().'.jpg');
                file_put_contents($tempCoverImage, $coverData);
                $inspection->local_cover = $tempCoverImage;
            } catch (\Exception $e) {
                Log::error("Error downloading cover image: ".$e->getMessage());
            }
        }

        // ==================== SECTION 3: OPTIMIZED - Parallel Download of Evidence Images ====================
        $tempEvidenceFiles = [];
        $tempEvidenceImages = $this->downloadEvidenceImagesInParallel($inspection);
        
        foreach ($tempEvidenceImages as $defectId => $localPaths) {
            $defect = $inspection->defects()->find($defectId);
            if ($defect) {
                $defect->local_evidence = $localPaths;
            }
            // Track temp files for cleanup
            $tempEvidenceFiles = array_merge($tempEvidenceFiles, $localPaths);
        }

        // ==================== SECTION 4: Generate PDF ====================
        // $template = $inspection->template;
        // $viewFile = $template ? $template->view_file : 'pdf_template_1';

        // $pdf = Pdf::setOptions(['isRemoteEnabled' => true]);

         $template = $inspection->template;
        $viewFile = $template ? $template->view_file : 'pdf_template_1';

        $pdf = Pdf::setOptions(['isRemoteEnabled' => true]);

        if ($viewFile === 'pdf_template_2') {
            $pdf->loadView('inspections.pdf_template_2', compact('inspection', 'indicatedPath', 'locationMaps'));
        } else {
            $pdf->loadView('inspections.pdf_template_1', compact('inspection', 'indicatedPath'));
        }
        $pdf->setPaper('A4', 'portrait');
        $safeTitle = str_replace([' ', '/', '\\'], '_', $inspection->title);
        $response = $pdf->download('Laporan_Defect_'.$safeTitle.'.pdf');

        // ==================== SECTION 5: Cleanup Temp Files ====================
        $this->cleanupTempFiles($indicatedPath, $locationMaps, $inspection, $tempCoverImage, $tempEvidenceFiles);

        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);
        Log::info("PDF Generation Time (Inspection ID: {$id}): {$executionTime} seconds.");

        return $response;
    }

    /**
     * OPTIMIZED: Download evidence images in parallel using Guzzle promises
     * Instead of sequential downloads, all S3 calls are made concurrently
     * 
     * @param Inspection $inspection
     * @return array Array of defect_id => [local_file_paths]
     */
    protected function downloadEvidenceImagesInParallel(Inspection $inspection): array
    {
        $tempEvidenceImages = [];
        
        // Build array of promises for parallel downloads
        $promises = [];
        $promiseMap = []; // Track which promise belongs to which defect/image
        
        $client = new Client();
        $s3Client = Storage::disk('s3')->getClient();

        foreach ($inspection->defects as $defect) {
            $localPaths = [];
            
            if (!empty($defect->img) && is_array($defect->img)) {
                // Only process first 4 images (as per original logic)
                $imagesToDownload = array_slice($defect->img, 0, 4);
                
                foreach ($imagesToDownload as $s3Path) {
                    if (Storage::disk('s3')->exists($s3Path)) {
                        // Create promise for this S3 file
                        $promise = $s3Client->getObjectAsync([
                            'Bucket' => config('filesystems.disks.s3.bucket'),
                            'Key'    => $s3Path,
                        ])->then(
                            function ($result) use ($s3Path) {
                                // Success: Save to temp file
                                $tempFilename = storage_path('app/public/inspections/ev_'.uniqid().'.jpg');
                                file_put_contents($tempFilename, (string) $result['Body']);
                                return ['success' => true, 'path' => $tempFilename, 's3Path' => $s3Path];
                            },
                            function ($reason) use ($s3Path) {
                                // Failure: Log and return null
                                Log::error("Failed to download evidence image {$s3Path}: ".$reason);
                                return ['success' => false, 's3Path' => $s3Path];
                            }
                        );
                        
                        $promises[] = $promise;
                        $promiseMap[] = ['defect_id' => $defect->id, 'index' => count($localPaths)];
                    }
                }
            }
            
            $tempEvidenceImages[$defect->id] = $localPaths;
        }

        // Execute all promises in parallel
        if (!empty($promises)) {
            try {
                $results = Promise\Utils::settle($promises)->wait();
                
                $promiseIndex = 0;
                foreach ($results as $result) {
                    if ($result['state'] === 'fulfilled' && $result['value']['success']) {
                        $defectId = $promiseMap[$promiseIndex]['defect_id'];
                        $tempEvidenceImages[$defectId][] = $result['value']['path'];
                    }
                    $promiseIndex++;
                }
            } catch (\Exception $e) {
                Log::error("Error during parallel S3 downloads: ".$e->getMessage());
                // Fall back to sequential download
                return $this->downloadEvidenceImagesSequential($inspection);
            }
        }

        return $tempEvidenceImages;
    }

    /**
     * Fallback: Sequential download (slower but more reliable for troubleshooting)
     * 
     * @param Inspection $inspection
     * @return array Array of defect_id => [local_file_paths]
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
                            $tempFilename = storage_path('app/public/inspections/ev_'.uniqid().'.jpg');
                            file_put_contents($tempFilename, $imgContent);
                            $localPaths[] = $tempFilename;
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
     * Clean up temporary files after PDF generation
     */
    protected function cleanupTempFiles($indicatedPath, $locationMaps, $inspection, $tempCoverImage, $tempEvidenceFiles): void
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
    }

    public function index(Request $request)
    {
        $query = Inspection::with('user')->latest();

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

        if ($inspection->layout_img && Storage::disk('s3')->exists($inspection->layout_img)) {
            Storage::disk('s3')->delete($inspection->layout_img);
        }

        $inspection->staffs()->detach();
        $inspection->delete();

        return redirect()->route('inspection.index')->with('success', 'Projek pemeriksaan berjaya dipadam!');
    }
}