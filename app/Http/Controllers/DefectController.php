<?php

namespace App\Http\Controllers;

use App\Models\Defect;
use App\Models\Inspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Str;

class DefectController extends Controller
{
    public function createRapid(Inspection $inspection)
    {
        return view('defects.add_items', compact('inspection'));
    }

    public function storeRapid(Request $request, Inspection $inspection)
    {
        $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                // Proses compress gambar sebelum simpan ke S3
                $compressedImageContent = $this->compressImage($file);
                
                if ($compressedImageContent) {
                    $filename = 'defects/' . Str::uuid() . '.jpg';
                    Storage::disk('s3')->put($filename, $compressedImageContent);
                    $imagePaths[] = $filename;
                } else {
                    // Fallback jika proses compress gagal
                    $imagePaths[] = $file->store('defects', 's3');
                }
            }
        }

        $defect = new Defect();
        $defect->inspection_id = $inspection->id;
        $defect->user_id = Auth::id();
        $defect->location = $request->location;
        $defect->category = $request->category;
        $defect->type = $request->type;
        $defect->defect = $request->defect; 
        $defect->desc = $request->description; 
        $defect->mark_x = $request->mx ?? 0;
        $defect->mark_y = $request->my ?? 0;
        $defect->img = $imagePaths; 

        $defect->save();

        return response()->json([
            'success' => true, 
            'item_id' => $defect->id
        ]);
    }

    // Paparkan UI Edit
    public function edit(Defect $defect)
    {
        $inspection = $defect->inspection;
        return view('defects.edit', compact('defect', 'inspection'));
    }

    // Proses Update Data
    public function update(Request $request, Defect $defect)
    {
        $defect->location = $request->location;
        $defect->category = $request->category;
        $defect->type = $request->type;
        $defect->defect = $request->defect; 
        $defect->desc = $request->description; 
        $defect->mark_x = $request->mx ?? $defect->mark_x;
        $defect->mark_y = $request->my ?? $defect->mark_y;

        // Kalau ada gambar baru di-upload, kita gantikan yang lama
        if ($request->hasFile('images')) {
            // Padam gambar lama dari AWS S3
            if (is_array($defect->img)) {
                foreach ($defect->img as $oldImage) {
                    if (Storage::disk('s3')->exists($oldImage)) {
                        Storage::disk('s3')->delete($oldImage);
                    }
                }
            }

            // Masukkan gambar baru yang telah di-compress ke S3
            $imagePaths = [];
            foreach ($request->file('images') as $file) {
                $compressedImageContent = $this->compressImage($file);
                
                if ($compressedImageContent) {
                    $filename = 'defects/' . Str::uuid() . '.jpg';
                    Storage::disk('s3')->put($filename, $compressedImageContent);
                    $imagePaths[] = $filename;
                } else {
                    $imagePaths[] = $file->store('defects', 's3');
                }
            }
            $defect->img = $imagePaths;
        }

        $defect->save();

        return response()->json(['success' => true]);
    }

    // Proses Delete Data
    public function destroy(Defect $defect)
    {
        // Padam gambar dari AWS S3 sebelum delete rekod
        if (is_array($defect->img)) {
            foreach ($defect->img as $image) {
                if (Storage::disk('s3')->exists($image)) {
                    Storage::disk('s3')->delete($image);
                }
            }
        }
        
        $defect->delete();

        return redirect()->back()->with('success', 'Defect berjaya dipadam.');
    }

    /**
     * Fungsi untuk mengecilkan saiz fail gambar tanpa merosakkan kualiti visual & bentuk.
     */
    private function compressImage($file, $maxWidth = 900, $quality = 75)
    {
        $path = $file->getRealPath();
    
    // Jika saiz fail asal kurang dari 500KB, terus guna fail asal (tak perlu compress berat-berat)
    if (filesize($path) < 500 * 1024) {
        return file_get_contents($path);
    }

    list($origWidth, $origHeight, $imageType) = getimagesize($path);

        // Baca imej mengikut format asal
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = @imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = @imagecreatefrompng($path);
                break;
            case IMAGETYPE_WEBP:
                $sourceImage = @imagecreatefromwebp($path);
                break;
            default:
                return null; // Format tidak disokong, hantar null (guna method asal)
        }

        if (!$sourceImage) {
            return null;
        }

        // Kira dimensi baru secara berkadar (proportional) supaya bentuk gambar tak rosak/lonjong
        $targetWidth = $origWidth;
        $targetHeight = $origHeight;

        if ($origWidth > $maxWidth) {
            $targetWidth = $maxWidth;
            $targetHeight = round(($origHeight / $origWidth) * $maxWidth);
        }

        // Cipta imej kosong baru dengan saiz yang telah dioptimumkan
        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

        // Kekalkan latar belakang telus (transparent) jika fail asal adalah PNG/WebP
        if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_WEBP) {
            imagecolortransparent($targetImage, imagecolorallocatealpha($targetImage, 0, 0, 0, 127));
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
        }

        // Salin dan ubah saiz gambar asal ke imej baharu
        imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $origWidth, $origHeight);

        // Tangkap hasil output ke dalam bentuk string data (Buffer) dalamformat JPEG
        ob_start();
        imagejpeg($targetImage, null, $quality);
        $compressedData = ob_get_clean();

        // Bersihkan memori pelayan
        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        return $compressedData;
    }
}