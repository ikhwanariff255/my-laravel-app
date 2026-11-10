@extends('layouts.app')

@section('content')
@php
    $isIn = $cashflow->type == 'in';
    $backUrl = route('cashflow.index', [
        'month' => \Carbon\Carbon::parse($cashflow->date)->format('m'),
        'year'  => \Carbon\Carbon::parse($cashflow->date)->format('Y'),
    ]);
@endphp
<div class="max-w-4xl mx-auto pb-10">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <a href="{{ $backUrl }}" class="text-sm text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-1.5"></i> Back to Cash Flow
            </a>
            <h2 class="text-2xl font-bold text-gray-800 mt-2">Transaction Details</h2>
            <p class="text-gray-500 text-sm mt-1">{{ $cashflow->reference_no ?? 'N/A' }}</p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if($cashflow->invoice_id && str_contains((string) $cashflow->reference_no, 'INV'))
                <a href="{{ route('invoice.pdf', $cashflow->invoice_id) }}" target="_blank" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 transition">
                    <i class="fas fa-file-invoice mr-1.5"></i> Invoice PDF
                </a>
            @else
                <a href="{{ route('cashflow.pdf', $cashflow->id) }}" target="_blank" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-print mr-1.5"></i> Print {{ $isIn ? 'Receipt' : 'Voucher' }}
                </a>
            @endif
        </div>
    </div>

    <!-- Main Details -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between gap-4">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full {{ $isIn ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }} flex items-center justify-center text-xl mr-4">
                    <i class="fas {{ $isIn ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                </div>
                <div>
                    <span class="px-2.5 py-1 {{ $isIn ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} text-xs font-bold rounded-full uppercase">
                        {{ $isIn ? 'Cash In' : 'Cash Out' }}
                    </span>
                    <p class="text-sm text-gray-500 mt-1.5">{{ $cashflow->category ?? '-' }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500 font-medium">Amount</p>
                <p class="text-2xl font-bold {{ $isIn ? 'text-green-600' : 'text-red-600' }}">
                    {{ $isIn ? '+' : '-' }}RM {{ number_format($cashflow->amount, 2) }}
                </p>
            </div>
        </div>

        <dl class="grid grid-cols-1 md:grid-cols-2 text-sm">
            <div class="px-6 py-4 border-b border-gray-100 md:border-r">
                <dt class="text-gray-500 font-medium">Reference No.</dt>
                <dd class="text-gray-800 font-bold mt-1">{{ $cashflow->reference_no ?? 'N/A' }}</dd>
            </div>
            <div class="px-6 py-4 border-b border-gray-100">
                <dt class="text-gray-500 font-medium">Transaction Date</dt>
                <dd class="text-gray-800 mt-1">{{ \Carbon\Carbon::parse($cashflow->date)->format('d M Y') }}</dd>
            </div>
            <div class="px-6 py-4 border-b border-gray-100 md:border-r">
                <dt class="text-gray-500 font-medium">Category</dt>
                <dd class="text-gray-800 mt-1">{{ $cashflow->category ?? '-' }}</dd>
            </div>
            <div class="px-6 py-4 border-b border-gray-100">
                <dt class="text-gray-500 font-medium">Staff / Payee</dt>
                <dd class="text-gray-800 mt-1">
                    @if($cashflow->user)
                        <i class="fas fa-user text-xs text-blue-600 mr-1"></i> {{ $cashflow->user->name }}
                    @else
                        -
                    @endif
                </dd>
            </div>
            <div class="px-6 py-4 border-b border-gray-100 md:col-span-2">
                <dt class="text-gray-500 font-medium">Description</dt>
                <dd class="text-gray-800 mt-1 whitespace-pre-line">{{ $cashflow->description }}</dd>
            </div>
            <div class="px-6 py-4 md:border-r border-b md:border-b-0 border-gray-100">
                <dt class="text-gray-500 font-medium">Recorded On</dt>
                <dd class="text-gray-800 mt-1">{{ $cashflow->created_at ? $cashflow->created_at->format('d M Y, h:i A') : '-' }}</dd>
            </div>
            <div class="px-6 py-4">
                <dt class="text-gray-500 font-medium">Last Updated</dt>
                <dd class="text-gray-800 mt-1">{{ $cashflow->updated_at ? $cashflow->updated_at->format('d M Y, h:i A') : '-' }}</dd>
            </div>
        </dl>
    </div>

    <!-- Linked Invoice -->
    @if($cashflow->invoice)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <h3 class="font-bold text-gray-700"><i class="fas fa-file-invoice mr-2 text-gray-400"></i> Linked Invoice</h3>
                <a href="{{ route('invoice.pdf', $cashflow->invoice->id) }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    View PDF <i class="fas fa-external-link-alt text-xs ml-1"></i>
                </a>
            </div>
            <dl class="grid grid-cols-1 md:grid-cols-3 text-sm">
                <div class="px-6 py-4">
                    <dt class="text-gray-500 font-medium">Invoice No.</dt>
                    <dd class="text-gray-800 font-bold mt-1">{{ $cashflow->invoice->inv_no }}</dd>
                </div>
                <div class="px-6 py-4">
                    <dt class="text-gray-500 font-medium">Customer</dt>
                    <dd class="text-gray-800 mt-1">{{ $cashflow->invoice->cus_name }}</dd>
                </div>
                <div class="px-6 py-4">
                    <dt class="text-gray-500 font-medium">Grand Total</dt>
                    <dd class="text-gray-800 mt-1">RM {{ number_format($cashflow->invoice->grand_total, 2) }}</dd>
                </div>
            </dl>
            @if($cashflow->invoice->details && $cashflow->invoice->details->count())
                <table class="w-full text-left text-sm border-t border-gray-100">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600">
                            <th class="px-6 py-3 font-semibold">Item</th>
                            <th class="px-6 py-3 font-semibold text-right">Price (RM)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($cashflow->invoice->details as $item)
                            <tr>
                                <td class="px-6 py-3 text-gray-700">{{ $item->desc }}</td>
                                <td class="px-6 py-3 text-gray-700 text-right">{{ number_format($item->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    <!-- Evidence / Receipt -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h3 class="font-bold text-gray-700"><i class="fas fa-paperclip mr-2 text-gray-400"></i> Evidence / Receipt</h3>
            @if($receiptExists)
                <a href="{{ route('cashflow.receipt', $cashflow->id) }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    Open in new tab <i class="fas fa-external-link-alt text-xs ml-1"></i>
                </a>
            @endif
        </div>
        <div class="p-6">
            @if($receiptExists)
                @if($receiptIsPdf)
                    <iframe src="{{ route('cashflow.receipt', $cashflow->id) }}" class="w-full h-[600px] border border-gray-200 rounded-lg"></iframe>
                @else
                    <a href="{{ route('cashflow.receipt', $cashflow->id) }}" target="_blank">
                        <img src="{{ route('cashflow.receipt', $cashflow->id) }}" alt="Evidence for {{ $cashflow->reference_no }}" class="max-w-full max-h-[600px] mx-auto rounded-lg border border-gray-200">
                    </a>
                @endif
            @elseif($cashflow->receipt)
                <p class="text-sm text-amber-600"><i class="fas fa-exclamation-triangle mr-1.5"></i> An evidence file was recorded but could not be found on the server.</p>
            @else
                <p class="text-sm text-gray-400">No evidence uploaded for this transaction.</p>
            @endif
        </div>
    </div>
</div>
@endsection
