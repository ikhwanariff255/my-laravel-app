@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto pb-10">
    
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Part-Time Wages Report</h2>
            <p class="text-gray-500 text-sm mt-1">Track and analyze your part-time staff expenses.</p>
        </div>
        
        <!-- Filter Form -->
        <form action="{{ route('reports.part_time') }}" method="GET" class="flex gap-2 bg-white p-2 rounded-lg shadow-sm border border-gray-100">
            <select name="month" class="px-3 py-2 border border-gray-200 rounded-md text-sm outline-none focus:border-blue-500">
                @for($i = 1; $i <= 12; $i++)
                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ $month == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                        {{ date("F", mktime(0, 0, 0, $i, 10)) }}
                    </option>
                @endfor
            </select>
            <select name="year" class="px-3 py-2 border border-gray-200 rounded-md text-sm outline-none focus:border-blue-500">
                @for($y = date('Y'); $y >= 2024; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Filter</button>
        </form>
    </div>

    <!-- Key Metric Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8 flex items-center justify-between border-l-4 border-l-blue-500">
        <div>
            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-1">Total Part-Time Wages ({{ date("F Y", mktime(0, 0, 0, $month, 10, $year)) }})</p>
            <h3 class="text-3xl font-black text-gray-800">RM {{ number_format($totalWages, 2) }}</h3>
        </div>
        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center text-blue-600 text-2xl">
            <i class="fas fa-users"></i>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Bar Chart (Staff Performance) -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-700 mb-4"><i class="fas fa-chart-bar text-blue-500 mr-2"></i> Wages Breakdown by Staff</h3>
            <div class="relative h-64">
                <canvas id="barChart"></canvas>
            </div>
        </div>

        <!-- Line Chart (6 Months Trend) -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-700 mb-4"><i class="fas fa-chart-line text-green-500 mr-2"></i> 6-Month Expense Trend</h3>
            <div class="relative h-64">
                <canvas id="lineChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h3 class="font-bold text-gray-700">Staff Payout Details</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm border-b border-gray-200">
                        <th class="px-6 py-4 font-semibold">No.</th>
                        <th class="px-6 py-4 font-semibold">Staff Name</th>
                        <th class="px-6 py-4 font-semibold text-center">Total Jobs/Tasks</th>
                        <th class="px-6 py-4 font-semibold text-right">Total Payout (RM)</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @php $i = 1; @endphp
                    @forelse($staffWages as $staff)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-500">{{ $i++ }}</td>
                        <td class="px-6 py-4 font-bold text-gray-700">{{ $staff['name'] }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">{{ $staff['job_count'] }} Times</span>
                        </td>
                        <td class="px-6 py-4 font-bold text-right text-gray-800">{{ number_format($staff['total'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400">No part-time wages recorded for this month.</td>
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
        // Data dari Controller
        const barLabels = @json($barLabels);
        const barData = @json($barData);
        
        const trendLabels = @json($trendLabels);
        const trendData = @json($trendData);

        // Render Bar Chart (Staff)
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: barLabels,
                datasets: [{
                    label: 'Total Payout (RM)',
                    data: barData,
                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        // Render Line Chart (Trend)
        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Part-Time Expenses (RM)',
                    data: trendData,
                    backgroundColor: 'rgba(34, 197, 94, 0.2)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: 'rgb(34, 197, 94)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    });
</script>
@endsection