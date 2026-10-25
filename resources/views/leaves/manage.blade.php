@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    
    <!-- Staff Leave Balances Summary Table (Exclusive for Owner & Admin Office) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Staff Leave Balances Summary ({{ date('Y') }})</h2>
                <p class="text-sm text-gray-500">Overview of remaining leaves for all registered employees.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                        <th class="p-3">Staff Name</th>
                        <th class="p-3">Role</th>
                        <th class="p-3">Annual Leave Remaining</th>
                        <th class="p-3">MC Remaining</th>
                        <th class="p-3">Hospitalisation Remaining</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-sm">
                    @forelse($staffBalances as $item)
                        <tr>
                            <td class="p-3 font-bold text-gray-800">{{ $item['user']->name }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded bg-gray-100 text-gray-700 capitalize">
                                    {{ str_replace('_', ' ', $item['user']->role) }}
                                </span>
                            </td>
                            <td class="p-3">
                                <span class="font-bold text-blue-600">{{ $item['balance']['annual_remaining'] }}</span> / {{ $item['balance']['annual_total'] }} days
                                <span class="text-xs text-gray-400 block">Used: {{ $item['balance']['annual_used'] }}d · Pending: {{ $item['balance']['annual_pending'] }}d</span>
                            </td>
                            <td class="p-3">
                                <span class="font-bold text-emerald-600">{{ $item['balance']['mc_remaining'] }}</span> / {{ $item['balance']['mc_total'] }} days
                                <span class="text-xs text-gray-400 block">Used: {{ $item['balance']['mc_used'] }}d · Pending: {{ $item['balance']['mc_pending'] }}d</span>
                            </td>
                            <td class="p-3">
                                <span class="font-bold text-purple-600">{{ $item['balance']['hosp_remaining'] }}</span> / {{ $item['balance']['hosp_total'] }} days
                                <span class="text-xs text-gray-400 block">Used: {{ $item['balance']['hosp_used'] }}d · Pending: {{ $item['balance']['hosp_pending'] }}d</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">No staff members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Leave Approvals & Management Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Leave Applications & Approvals</h2>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg text-sm font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                        <th class="p-3">Staff Name</th>
                        <th class="p-3">Type</th>
                        <th class="p-3">Duration</th>
                        <th class="p-3">Reason</th>
                        <th class="p-3">Attachment</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-sm">
                    @forelse($leaves as $leave)
                        <tr>
                            <td class="p-3 font-bold">{{ $leave->user->name ?? '-' }}</td>
                            <td class="p-3 uppercase">{{ str_replace('_', ' ', $leave->leave_type) }}</td>
                            <td class="p-3">{{ $leave->start_date->format('d/m/Y') }} - {{ $leave->end_date->format('d/m/Y') }} ({{ $leave->total_days }} days)</td>
                            <td class="p-3">{{ $leave->reason }}</td>
                            <td class="p-3">
                                @if($leave->attachment)
                                    <a href="{{ route('leaves.attachment', $leave) }}" target="_blank" class="text-blue-600 underline text-xs font-semibold">View Document</a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <span class="px-2.5 py-1 text-xs font-bold rounded 
                                    {{ $leave->status === 'approved' ? 'bg-green-100 text-green-800' : ($leave->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ ucfirst($leave->status) }}
                                </span>
                            </td>
                            <td class="p-3">
                                @if($leave->status === 'pending' && $leave->user_id === Auth::id())
                                    <span class="text-xs text-gray-400 italic">Awaiting another approver</span>
                                @elseif($leave->status === 'pending')
                                    <div class="flex gap-2">
                                        <form action="{{ route('leaves.updateStatus', $leave->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded text-xs font-bold hover:bg-green-700 shadow-sm transition">Approve</button>
                                        </form>

                                        <form action="{{ route('leaves.updateStatus', $leave->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded text-xs font-bold hover:bg-red-700 shadow-sm transition">Reject</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Completed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-gray-500">No leave applications found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $leaves->links() }}</div>
    </div>
</div>
@endsection