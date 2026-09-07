<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Inspection;
use App\Models\User; // <-- 1. Pastikan User diimport di sini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    // Paparkan senarai invois mengikut syarikat
    public function index()
    {
        $currentUser = Auth::user();
        $invoices = Invoice::where('company_id', $currentUser->company_id)->latest()->paginate(10);

        return view('invoices.index', compact('invoices'));
    }

    // Paparkan borang cipta invois (boleh terima inspection_id)
    public function create(Request $request)
    {
        $inspection = null;
        if ($request->has('inspection_id')) {
            $inspection = Inspection::findOrFail($request->inspection_id);
        }

        $currentUser = Auth::user();
        $inspections = Inspection::where('company_id', $currentUser->company_id)->get();
        
        // 2. Ambil senarai staf untuk dropdown dinamik di borang invois
        $staffs = User::all(); 

        return view('invoices.create', compact('inspection', 'inspections', 'staffs'));
    }

    // Simpan invois baru ke pangkalan data
    // Simpan invois baru ke pangkalan data
    public function store(Request $request)
    {
        $request->validate([
            'inspection_id' => 'nullable|exists:inspections,id',
            'customer_name' => 'required|string|max:255',
            'customer_address' => 'required|string',
            'description' => 'required|array',
            'description.*' => 'required|string',
            'price' => 'required|array',
            'price.*' => 'required|numeric',
        ]);

        $currentUser = Auth::user();

        // 1. Jana Nombor Invois Automatik jika perlu (cth: INV-2026-XXXX)
        $invNo = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);

        // 2. Kira jumlah keseluruhan (Grand Total) daripada harga item-item yang dimasukkan
        $grandTotal = array_sum($request->price);

        // 3. Simpan induk Invois
        $invoice = Invoice::create([
            'company_id'    => $currentUser->company_id, // Wajib untuk Multi-Tenancy
            'inspection_id' => $request->inspection_id,
            'user_id'       => $currentUser->id,
            'inv_no'        => $invNo,
            'date'          => now(),
            'cus_name'      => $request->customer_name,
            'cus_address'   => $request->customer_address,
            'grand_total'   => $grandTotal,
        ]);

        // 4. Simpan butiran item invois ke dalam jadual details (jika jadual invoice_details wujud)
        foreach ($request->description as $index => $desc) {
            if (isset($request->price[$index])) {
                \App\Models\InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'desc' => $desc,
                    'price' => $request->price[$index],
                ]);
            }
        }

        return redirect()->route('invoices.index')->with('success', 'Invois berjaya dijana!');
    }
}