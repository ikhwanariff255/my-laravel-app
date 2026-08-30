<?php

namespace App\Http\Controllers;

use App\Models\Defect;
use App\Models\Inspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; 

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
                // Changed 'public' to 's3'
                $imagePaths[] = $file->store('defects', 's3');
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
                    // Changed 'public' to 's3'
                    if (Storage::disk('s3')->exists($oldImage)) {
                        Storage::disk('s3')->delete($oldImage);
                    }
                }
            }

            // Masukkan gambar baru ke S3
            $imagePaths = [];
            foreach ($request->file('images') as $file) {
                // Changed 'public' to 's3'
                $imagePaths[] = $file->store('defects', 's3');
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
                // Changed 'public' to 's3'
                if (Storage::disk('s3')->exists($image)) {
                    Storage::disk('s3')->delete($image);
                }
            }
        }
        
        $defect->delete();

        return redirect()->back()->with('success', 'Defect berjaya dipadam.');
    }
}