<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CashFlowController extends Controller
{
    // 1. Paparkan Dashboard & Senarai Cash Flow
    // 1. Paparkan Dashboard & Senarai Cash Flow (Dengan Filter Bulan/Tahun)
    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('m'));
        $year = $request->input('year', Carbon::now()->format('Y'));

        // Query asas ditapis mengikut bulan & tahun
        $query = CashFlow::with(['invoice', 'user'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year);

        // Kira jumlah keseluruhan berdasarkan bulan yang ditapis
        $totalIn = (clone $query)->where('type', 'in')->sum('amount');
        $totalOut = (clone $query)->where('type', 'out')->sum('amount');
        $profit = $totalIn - $totalOut;

        // Ambil rekod transaksi dengan pagination & kekalkan parameter filter pada pautan muka surat
        $cashflows = $query->latest('date')->latest('id')->paginate(15)->appends(['month' => $month, 'year' => $year]);

        return view('cashflows.index', compact('cashflows', 'totalIn', 'totalOut', 'profit', 'month', 'year'));
    }

    // 2. Borang Kemasukan Manual
    public function create()
    {
        // Tarik senarai staf jika Cash Out tu adalah untuk bayar gaji manual
        $staffs = User::whereIn('role', ['staff'])->orderBy('name', 'asc')->get();
        return view('cashflows.create', compact('staffs'));
    }

    // 3. Simpan Transaksi Manual
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'date' => 'required|date',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // Auto Generate Nombor Rujukan Manual (REC = Receipt/In, VOU = Voucher/Out)
        $prefix = $request->type == 'in' ? 'REC' : 'VOU';
        $today = Carbon::parse($request->date)->format('dmy');
        
        $lastRecord = CashFlow::where('reference_no', 'like', "{$prefix}-{$today}-%")->latest('id')->first();
        
        if ($lastRecord) {
            $parts = explode('-', $lastRecord->reference_no);
            $newNumber = (int)end($parts) + 1;
        } else {
            $newNumber = 1;
        }
        $refNo = "{$prefix}-{$today}-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        // Simpan ke database
        CashFlow::create([
            'type' => $request->type,
            'category' => $request->category,
            'amount' => $request->amount,
            'description' => $request->description,
            'date' => $request->date,
            'reference_no' => $refNo,
            'user_id' => $request->user_id, // Boleh null kalau bukan untuk staf
        ]);

        return redirect()->route('cashflow.index')->with('success', 'Transaksi aliran tunai manual berjaya direkodkan!');
    }

    // 4. Download PDF (Resit Manual / Baucar)
    public function downloadPDF($id)
    {
        $cashflow = CashFlow::with(['user', 'invoice'])->findOrFail($id);
        
        $pdf = Pdf::loadView('cashflows.pdf', compact('cashflow'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->stream($cashflow->reference_no . '.pdf');
    }

    // Jana Laporan Bulanan Format T (PDF)
    public function monthlyReportPDF(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('m'));
        $year = $request->input('year', Carbon::now()->format('Y'));

        // Tarik senarai Cash In & Cash Out mengikut bulan/tahun yang dipilih
        $cashIns = CashFlow::where('type', 'in')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'asc')
            ->get();

        $cashOuts = CashFlow::where('type', 'out')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'asc')
            ->get();

        $totalIn = $cashIns->sum('amount');
        $totalOut = $cashOuts->sum('amount');
        $netBalance = $totalIn - $totalOut;

        // Load view PDF format T
        $pdf = Pdf::loadView('cashflows.report_pdf', compact(
            'cashIns', 'cashOuts', 'totalIn', 'totalOut', 'netBalance', 'month', 'year'
        ));
        
        $pdf->setPaper('A4', 'portrait'); // Guna landskap sebab format T perlukan ruang melintang yang luas
        
        return $pdf->stream("CashFlow_Report_{$month}_{$year}.pdf");
    }
}