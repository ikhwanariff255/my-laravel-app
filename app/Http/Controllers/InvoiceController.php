<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Inspection;
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

        return view('invoices.create', compact('inspection', 'inspections'));
    }

    // Simpan invois baru ke pangkalan data
    public function store(Request $request)
    {
        $request->validate([
            'inspection_id' => 'nullable|exists:inspections,id',
            'inv_no' => 'required|string|max:255',
            'date' => 'required|date',
            'cus_name' => 'required|string|max:255',
            'cus_address' => 'required|string',
            'grand_total' => 'required|numeric',
        ]);

        $currentUser = Auth::user();

        Invoice::create([
            'company_id' => $currentUser->company_id, // Wajib untuk Multi-Tenancy
            'inspection_id' => $request->inspection_id,
            'user_id' => $currentUser->id,
            'inv_no' => $request->inv_no,
            'date' => $request->date,
            'cus_name' => $request->cus_name,
            'cus_address' => $request->cus_address,
            'grand_total' => $request->grand_total,
        ]);

        return redirect()->route('invoices.index')->with('success', 'Invois berjaya dijana!');
    }
}