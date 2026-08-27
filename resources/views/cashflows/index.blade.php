@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto pb-10">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Cash Flow Management</h2>
            <p class="text-gray-500 text-sm mt-1">Track all money coming in and going out.</p>
        </div>
        
        <!-- Filter & Download PDF Form -->
        <form action="{{ route('cashflow.index') }}" method="GET" class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-xl shadow-sm border border-gray-100">
            <select name="month" class="px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:border-blue-500 bg-white">
                @for($i = 1; $i <= 12; $i++)
                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ request('month', date('m')) == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                        {{ date("F", mktime(0, 0, 0, $i, 10)) }}
                    </option>
                @endfor
            </select>
            <select name="year" class="px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:border-blue-500 bg-white">
                @for($y = date('Y'); $y >= 2024; $y--)
                    <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 transition">
                <i class="fas fa-filter mr-1.5"></i> Filter
            </button>
            
            <!-- Butang Download Report PDF T-Format -->
            <a href="{{ route('cashflow.report_pdf', ['month' => request('month', date('m')), 'year' => request('year', date('Y'))]) }}" target="_blank" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition">
                <i class="fas fa-file-pdf mr-1.5"></i> Monthly Report (PDF)
            </a>

            <a href="{{ route('cashflow.create') }}" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-1.5"></i> Add Entry
            </a>
        </form>
    </div>
    
    {{-- <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Cash Flow Management</h2>
            <p class="text-gray-500 text-sm mt-1">Track all money coming in and going out.</p>
        </div>
        <a href="{{ route('cashflow.create') }}" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 shadow-sm transition">
            <i class="fas fa-plus mr-2"></i> Add Manual Entry
        </a>
    </div> --}}

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg border-l-4 border-green-500">
            {{ session('success') }}
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Cash In -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
            <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-2xl mr-4">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Cash In</p>
                <h3 class="text-2xl font-bold text-gray-800">RM {{ number_format($totalIn, 2) }}</h3>
            </div>
        </div>

        <!-- Cash Out -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-2xl mr-4">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Cash Out / Expenses</p>
                <h3 class="text-2xl font-bold text-gray-800">RM {{ number_format($totalOut, 2) }}</h3>
            </div>
        </div>

        <!-- Profit / Balance -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
            <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-2xl mr-4">
                <i class="fas fa-wallet"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Net Profit / Balance</p>
                <h3 class="text-2xl font-bold {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    RM {{ number_format($profit, 2) }}
                </h3>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h3 class="font-bold text-gray-700">Transaction History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm border-b border-gray-200">
                        <th class="px-6 py-4 font-semibold">Date</th>
                        <th class="px-6 py-4 font-semibold">Ref No.</th>
                        <th class="px-6 py-4 font-semibold">Description</th>
                        <th class="px-6 py-4 font-semibold text-center">Type</th>
                        <th class="px-6 py-4 font-semibold text-right">Amount (RM)</th>
                        <th class="px-6 py-4 font-semibold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse($cashflows as $cf)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($cf->date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-700 whitespace-nowrap">
                            {{ $cf->reference_no ?? 'N/A' }}
                            <div class="text-xs text-gray-400 font-normal mt-0.5">{{ $cf->category }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-700 min-w-[250px]">
                            {{ $cf->description }}
                            @if($cf->user)
                                <span class="block text-xs text-blue-600 font-semibold mt-0.5"><i class="fas fa-user text-xs mr-1"></i> {{ $cf->user->name }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($cf->type == 'in')
                                <span class="px-2.5 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full uppercase">IN</span>
                            @else
                                <span class="px-2.5 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full uppercase">OUT</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-bold text-right {{ $cf->type == 'in' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $cf->type == 'in' ? '+' : '-' }}{{ number_format($cf->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($cf->invoice_id && str_contains($cf->reference_no, 'INV'))
                                <!-- Jika ia datang dari Invoice Auto, butang print bawa ke Invoice PDF -->
                                <a href="{{ route('invoice.pdf', $cf->invoice_id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-md font-medium text-xs transition">
                                    <i class="fas fa-file-invoice mr-1.5"></i> Invoice
                                </a>
                            @else
                                <!-- Jika ia resit manual (atau payment out manual/gaji), print Resit PDF Manual -->
                                <a href="{{ route('cashflow.pdf', $cf->id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-md font-medium text-xs transition">
                                    <i class="fas fa-print mr-1.5"></i> Print
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400">No transactions recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $cashflows->links() }}
        </div>
    </div>
</div>
@endsection