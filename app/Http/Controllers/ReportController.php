<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function partTimeWages(Request $request)
    {
        // Tetapkan bulan & tahun default kepada bulan semasa jika tiada filter
        $month = $request->input('month', Carbon::now()->format('m'));
        $year = $request->input('year', Carbon::now()->format('Y'));

        // 1. Dapatkan semua transaksi Cash Out yang mempunyai 'user_id'
        // (Ini bermaksud apa sahaja bayaran yang dikaitkan dengan staf akan dikira sebagai gaji)
        $transactions = CashFlow::with('user')
            ->where('type', 'out')
            ->whereNotNull('user_id') // <--- Inilah kunci penyelesaiannya
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $totalWages = $transactions->sum('amount');

        // 2. Kumpulan data ikut Staf untuk Jadual & Graf Bar (Bulan Pilihan)
        $staffWages = $transactions->groupBy('user_id')->map(function ($rows) {
            return [
                'name' => $rows->first()->user ? $rows->first()->user->name : 'Unknown Staff',
                'total' => $rows->sum('amount'),
                'job_count' => $rows->count(),
            ];
        })->sortByDesc('total');

        // Data untuk graf Bar
        $barLabels = $staffWages->pluck('name')->toArray();
        $barData = $staffWages->pluck('total')->toArray();

        // 3. Logik untuk Graf Trend 6 Bulan Terakhir
        $trendLabels = [];
        $trendData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $targetMonth = Carbon::now()->subMonths($i);
            $trendLabels[] = $targetMonth->format('M Y');
            
            // Kira jumlah cash out staf untuk bulan tersebut
            $monthlySum = CashFlow::where('type', 'out')
                ->whereNotNull('user_id') // <--- Gunakan logik yang sama di sini
                ->whereMonth('date', $targetMonth->format('m'))
                ->whereYear('date', $targetMonth->format('Y'))
                ->sum('amount');
                
            $trendData[] = $monthlySum;
        }

        // Hantar semua pembolehubah ke View
        return view('reports.part_time', compact(
            'month', 'year', 'totalWages', 'staffWages', 
            'barLabels', 'barData', 'trendLabels', 'trendData'
        ));
    }
}