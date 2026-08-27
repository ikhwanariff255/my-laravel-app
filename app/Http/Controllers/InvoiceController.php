<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\CashFlow;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    // 1. Paparkan senarai invois
    public function index()
    {
        // Ambil data invois susun dari yang terbaru
        $invoices = Invoice::latest('date')->paginate(10);
        return view('invoices.index', compact('invoices'));
    }

    // 2. Paparkan borang Generate Invoice
    public function create()
    {
        // Ambil senarai staf untuk pilihan part-timer
        $staffs = User::whereIn('role', ['staff'])->orderBy('name', 'asc')->get();
        return view('invoices.create', compact('staffs'));
    }

    // 3. Proses simpan Invoice, Items, dan Auto Cash Flow
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_address' => 'required|string',
            'description' => 'required|array',
            'price' => 'required|array',
        ]);

        // AUTO-GENERATE INVOICE NUMBER
        $today = Carbon::now()->format('dmy');
        $lastInvoice = Invoice::where('inv_no', 'like', "INV-{$today}-%")->latest('id')->first();
        
        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice->inv_no);
            $newNumber = (int)end($parts) + 1;
        } else {
            $newNumber = 1;
        }
        $invoiceNo = "INV-{$today}-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        $invoiceDate = Carbon::now()->format('Y-m-d');

        // KIRA GRAND TOTAL
        $grandTotal = array_sum($request->price);

        // SIMPAN INVOICE
        $invoice = Invoice::create([
            'user_id' => Auth::id(),
            'inv_no' => $invoiceNo,
            'date' => $invoiceDate,
            'cus_name' => $request->customer_name,
            'cus_address' => $request->customer_address,
            'grand_total' => $grandTotal,
        ]);

        // SIMPAN INVOICE DETAILS (ITEMS)
        foreach ($request->description as $index => $desc) {
            $invoice->details()->create([
                'desc' => $desc,
                'price' => $request->price[$index]
            ]);
        }

        // AUTO SIMPAN KE CASH FLOW (CASH IN)
        CashFlow::create([
            'type' => 'in',
            'category' => 'Invoice',
            'amount' => $grandTotal,
            'description' => 'Payment received for Invoice ' . $invoiceNo . ' (' . $request->customer_name . ')',
            'date' => $invoiceDate,
            'reference_no' => $invoiceNo,
            'invoice_id' => $invoice->id,
        ]);

        // AUTO SIMPAN EXPENSES (CASH OUT) - JIKA ADA
        if ($request->has('staff_id') && $request->has('staff_amount')) {
            foreach ($request->staff_id as $index => $staffId) {
                $amount = (float) $request->staff_amount[$index];
                if (!empty($staffId) && $amount > 0) {
                    CashFlow::create([
                        'type' => 'out',
                        'category' => 'Part-Time',
                        'amount' => $amount,
                        'description' => 'Part-time wage for Invoice ' . $invoiceNo,
                        'date' => $invoiceDate,
                        'reference_no' => $invoiceNo,
                        'invoice_id' => $invoice->id,
                        'user_id' => $staffId,
                    ]);
                }
            }
        }

        // Redirect ke senarai invoice
        return redirect()->route('invoice.index')->with('success', 'Invoice & Cash Flow rekod berjaya dijanakan!');
    }

    // 4. Jana dan Muat Turun PDF Invois
    public function downloadPDF($id)
    {
        $invoice = Invoice::with('details')->findOrFail($id);
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        $pdf->setPaper('A4', 'portrait');
        
        // Buka fail PDF dalam browser
        return $pdf->stream('Invoice_' . $invoice->inv_no . '.pdf');
    }
}