<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Inspection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    public function index()
    {
        // 1. Tarikh Bulan Semasa & Bulan Lepas
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');
        
        $lastMonth = Carbon::now()->subMonth()->format('m');
        $lastMonthYear = Carbon::now()->subMonth()->format('Y');

        // 2. Jualan Bulan Semasa (Cash In dari Invoice)
        $currentMonthSales = Invoice::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('grand_total');

        // 3. Jualan Bulan Lepas (Untuk Kira Growth %)
        $lastMonthSales = Invoice::whereMonth('date', $lastMonth)
            ->whereYear('date', $lastMonthYear)
            ->sum('grand_total');

        // Kira Peratusan Pertumbuhan (Revenue Growth)
        $growthPercentage = 0;
        if ($lastMonthSales > 0) {
            $growthPercentage = (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100;
        } elseif ($currentMonthSales > 0) {
            $growthPercentage = 100; 
        }

        // 4. Data untuk Graf Tren Jualan 6 Bulan Terakhir
        $salesTrendLabels = [];
        $salesTrendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $target = Carbon::now()->subMonths($i);
            $salesTrendLabels[] = $target->format('M Y');
            $salesTrendData[] = Invoice::whereMonth('date', $target->format('m'))
                ->whereYear('date', $target->format('Y'))
                ->sum('grand_total');
        }

        // 5. Pecahan Jenis Hartanah Paling Laris (Property Distribution)
        $propertyTypes = Inspection::select('type', DB::raw('count(*) as total'))            
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $propertyLabels = array_keys($propertyTypes);
        $propertyData = array_values($propertyTypes);

        // Statistik Ringkas & Senarai Projek Terkini
        $totalInspections = Inspection::count();
        $totalInvoicesCount = Invoice::count();
        $recentInspections = Inspection::latest()->take(5)->get();

        return view('dashboard', compact(
            'currentMonthSales', 'growthPercentage', 'totalInspections', 
            'totalInvoicesCount', 'salesTrendLabels', 'salesTrendData', 
            'propertyLabels', 'propertyData', 'recentInspections'
        ));
    }
}