<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Inspection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // <-- Jangan lupa import Auth

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Asas Query Builder mengikut Syarikat (Multi-Tenant Check)
        $invoiceQuery = Invoice::query();
        $inspectionQuery = Inspection::query();

        // Jika bukan Super Admin (admin), tapis mengikut company_id syarikat mereka
        if ($user->role !== 'admin') {
            $invoiceQuery->where('company_id', $user->company_id);
            $inspectionQuery->where('company_id', $user->company_id);
        }

        // 2. Tarikh Bulan Semasa & Bulan Lepas
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');
        
        $lastMonth = Carbon::now()->subMonth()->format('m');
        $lastMonthYear = Carbon::now()->subMonth()->format('Y');

        // 3. Jualan Bulan Semasa (Tapis ikut tenant)
        $currentMonthSales = (clone $invoiceQuery)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('grand_total');

        // 4. Jualan Bulan Lepas
        $lastMonthSales = (clone $invoiceQuery)
            ->whereMonth('date', $lastMonth)
            ->whereYear('date', $lastMonthYear)
            ->sum('grand_total');

        // Kira Peratusan Pertumbuhan (Revenue Growth)
        $growthPercentage = 0;
        if ($lastMonthSales > 0) {
            $growthPercentage = (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100;
        } elseif ($currentMonthSales > 0) {
            $growthPercentage = 100; 
        }

        // 5. Data untuk Graf Tren Jualan 6 Bulan Terakhir
        $salesTrendLabels = [];
        $salesTrendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $target = Carbon::now()->subMonths($i);
            $salesTrendLabels[] = $target->format('M Y');
            $salesTrendData[] = (clone $invoiceQuery)
                ->whereMonth('date', $target->format('m'))
                ->whereYear('date', $target->format('Y'))
                ->sum('grand_total');
        }

        // 6. Pecahan Jenis Hartanah Paling Laris (Tapis ikut tenant)
        $propertyTypes = (clone $inspectionQuery)
            ->select('type', DB::raw('count(*) as total'))            
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $propertyLabels = array_keys($propertyTypes);
        $propertyData = array_values($propertyTypes);

        // Statistik Ringkas & Senarai Projek Terkini (Tapis ikut tenant)
        $totalInspections = (clone $inspectionQuery)->count();
        $totalInvoicesCount = (clone $invoiceQuery)->count();
        $recentInspections = (clone $inspectionQuery)->latest()->take(5)->get();

        return view('dashboard', compact(
            'currentMonthSales', 'growthPercentage', 'totalInspections', 
            'totalInvoicesCount', 'salesTrendLabels', 'salesTrendData', 
            'propertyLabels', 'propertyData', 'recentInspections'
        ));
    }
}