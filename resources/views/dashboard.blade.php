@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto pb-10">
    
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Dashboard Overview</h2>
        <p class="text-gray-500 text-sm mt-1">Welcome back, {{ Auth::user()->name }}! Here is your business summary.</p>
    </div>

    <!-- Metric Cards Grid (Sales, Total Inspections, Invoices) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Current Month Sales -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-400 uppercase tracking-wider">This Month Sales</p>
                <h3 class="text-2xl font-black text-gray-800 mt-1">RM {{ number_format($currentMonthSales, 2) }}</h3>
                
                <!-- Revenue Growth badge -->
                <div class="flex items-center mt-2 text-xs font-bold">
                    @if($growthPercentage >= 0)
                        <span class="text-green-600 bg-green-50 px-2 py-0.5 rounded-full flex items-center">
                            <i class="fas fa-arrow-up mr-1"></i> +{{ number_format($growthPercentage, 1) }}%
                        </span>
                    @else
                        <span class="text-red-600 bg-red-50 px-2 py-0.5 rounded-full flex items-center">
                            <i class="fas fa-arrow-down mr-1"></i> {{ number_format($growthPercentage, 1) }}%
                        </span>
                    @endif
                    <span class="text-gray-400 font-normal ml-1.5">vs last month</span>
                </div>
            </div>
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>

        <!-- Total Inspections -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Total Inspections</p>
                <h3 class="text-2xl font-black text-gray-800 mt-1">{{ $totalInspections }} Projects</h3>
                <p class="text-xs text-gray-400 mt-2">Registered in system</p>
            </div>
            <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center text-2xl">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>

        <!-- Total Invoices -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Total Invoices Issued</p>
                <h3 class="text-2xl font-black text-gray-800 mt-1">{{ $totalInvoicesCount }} Bills</h3>
                <p class="text-xs text-gray-400 mt-2">Official receipts generated</p>
            </div>
            <div class="w-14 h-14 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center text-2xl">
                <i class="fas fa-file-invoice"></i>
            </div>
        </div>
    </div>

    <!-- Charts Grid (Sales Trend & Property Distribution) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Sales Trend Bar Chart (Takes 2 columns) -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-gray-800"><i class="fas fa-chart-bar text-blue-500 mr-2"></i> Monthly Sales Trend (6 Months)</h3>
            </div>
            <div class="relative h-72">
                <canvas id="dashboardSalesChart"></canvas>
            </div>
        </div>

        <!-- Property Distribution Doughnut Chart (Takes 1 column) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-gray-800"><i class="fas fa-home text-purple-500 mr-2"></i> Property Types</h3>
            </div>
            <div class="relative h-72 flex items-center justify-center">
                <canvas id="propertyDoughnutChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Actions (Start New Project & View List) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Action Card: Start New Project -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Have a new client?</p>
                <h4 class="text-lg font-bold text-gray-800">Create New Inspection</h4>
            </div>
            <a href="{{ route('inspection.create') }}" class="px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-lg shadow-sm hover:bg-blue-700 transition-colors">
                <i class="fa-solid fa-plus mr-1"></i> Start New
            </a>
        </div>

        <!-- Action Card: Full List -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Manage existing records</p>
                <h4 class="text-lg font-bold text-gray-800">Project Directory</h4>
            </div>
            <a href="{{ route('inspection.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-lg shadow-sm hover:bg-gray-200 transition-colors">
                <i class="fa-solid fa-list mr-1"></i> View List
            </a>
        </div>
    </div>

    <!-- Recent Projects Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Recent Records</h3>
            <a href="{{ route('inspection.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">View All &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="py-3 px-6 font-semibold">Property Title</th>
                        <th class="py-3 px-6 font-semibold">Client</th>
                        <th class="py-3 px-6 font-semibold">Date Registered</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($recentInspections as $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-6">
                                <p class="font-bold text-gray-900">{{ $item->title }}</p>
                                <span class="inline-block mt-1 px-2 py-0.5 bg-blue-50 text-blue-700 text-xs font-medium rounded-md">
                                    {{ $item->type }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-medium text-gray-800">{{ $item->clientname }}</td>
                            <td class="py-4 px-6 text-gray-500">{{ $item->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center text-gray-400">
                                <i class="fa-regular fa-folder-open text-3xl mb-2 block"></i>
                                No inspection projects have been recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Sales Trend Bar Chart
        const salesLabels = @json($salesTrendLabels);
        const salesData = @json($salesTrendData);

        new Chart(document.getElementById('dashboardSalesChart'), {
            type: 'bar',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Sales (RM)',
                    data: salesData,
                    backgroundColor: 'rgba(37, 99, 235, 0.8)',
                    borderColor: 'rgb(37, 99, 235)',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        // 2. Property Distribution Doughnut Chart
        const propLabels = @json($propertyLabels);
        const propData = @json($propertyData);

        new Chart(document.getElementById('propertyDoughnutChart'), {
            type: 'doughnut',
            data: {
                labels: propLabels.length > 0 ? propLabels : ['No Data'],
                datasets: [{
                    data: propData.length > 0 ? propData : [1],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12 } }
                }
            }
        });
    });
</script>
@endsection