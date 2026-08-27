@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto pb-10">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">All Generated Invoices</h2>
            <p class="text-gray-500 text-sm mt-1">Manage and print your official invoices here.</p>
        </div>
        <a href="{{ route('invoice.create') }}" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 shadow-sm transition">
            <i class="fas fa-plus mr-2"></i> New Invoice
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm border-b border-gray-200">
                        <th class="px-6 py-4 font-semibold">Date</th>
                        <th class="px-6 py-4 font-semibold">Invoice No.</th>
                        <th class="px-6 py-4 font-semibold">Customer Name</th>
                        <th class="px-6 py-4 font-semibold text-right">Grand Total</th>
                        <th class="px-6 py-4 font-semibold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-500">
                            <i class="far fa-calendar-alt mr-2 text-gray-400"></i>
                            {{ \Carbon\Carbon::parse($inv->date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 font-bold text-blue-600">{{ $inv->inv_no }}</td>
                        <td class="px-6 py-4 font-semibold text-gray-800 uppercase">{{ $inv->cus_name }}</td>
                        <td class="px-6 py-4 font-bold text-green-600 text-right">RM {{ number_format($inv->grand_total, 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('invoice.pdf', $inv->id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-sky-100 text-sky-700 hover:bg-sky-200 rounded-md font-medium text-xs transition">
                                <i class="fas fa-print mr-1.5"></i> Print
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400">No invoices recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection