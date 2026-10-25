@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    
    <!-- Personal Leave Balance Summary Cards -->
    <div>
        <h2 class="text-xl font-bold text-gray-800 mb-4">My Leave Entitlement ({{ date('Y') }})</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Annual Leave Balance -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Annual / Emergency Leave</p>
                    <p class="text-3xl font-extrabold text-blue-600 mt-1">{{ $leaveBalance['annual_remaining'] }} <span class="text-sm font-normal text-gray-500">/ {{ $leaveBalance['annual_total'] }} Days</span></p>
                    <p class="text-xs text-gray-400 mt-1">Used: {{ $leaveBalance['annual_used'] }} days · Pending: {{ $leaveBalance['annual_pending'] }} days</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
            </div>

            <!-- Medical Leave Balance -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Medical Leave (MC)</p>
                    <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $leaveBalance['mc_remaining'] }} <span class="text-sm font-normal text-gray-500">/ {{ $leaveBalance['mc_total'] }} Days</span></p>
                    <p class="text-xs text-gray-400 mt-1">Used: {{ $leaveBalance['mc_used'] }} days · Pending: {{ $leaveBalance['mc_pending'] }} days</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-notes-medical"></i>
                </div>
            </div>

            <!-- Hospitalisation Leave Balance -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Hospitalisation Leave</p>
                    <p class="text-3xl font-extrabold text-purple-600 mt-1">{{ $leaveBalance['hosp_remaining'] }} <span class="text-sm font-normal text-gray-500">/ {{ $leaveBalance['hosp_total'] }} Days</span></p>
                    <p class="text-xs text-gray-400 mt-1">Used: {{ $leaveBalance['hosp_used'] }} days · Pending: {{ $leaveBalance['hosp_pending'] }} days</p>
                </div>
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-hospital-user"></i>
                </div>
            </div>

        </div>
    </div>

    <!-- Apply Leave Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Apply Leave / MC</h2>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg text-sm font-semibold">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('leaves.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type</label>
                    <select name="leave_type" required class="w-full rounded-lg border-gray-300 shadow-sm p-2.5">
                        <option value="annual">Annual Leave (Min 3 days in advance)</option>
                        <option value="emergency">Emergency Leave (Deducted from Annual Leave)</option>
                        <option value="mc">Medical Leave / MC (Requires MC attachment)</option>
                        <option value="hospitalisation">Hospitalisation Leave (Requires attachment)</option>
                        <option value="compassionate">Compassionate Leave (2 days)</option>
                        <option value="maternity">Maternity Leave (98 days)</option>
                        <option value="paternity">Paternity Leave (7 days)</option>
                        <option value="unpaid">Unpaid Leave</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" required class="w-full rounded-lg border-gray-300 shadow-sm p-2.5">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" required class="w-full rounded-lg border-gray-300 shadow-sm p-2.5">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                <textarea name="reason" rows="2" required placeholder="State your reason for leave..." class="w-full rounded-lg border-gray-300 shadow-sm p-2.5"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Attachment / MC Slip (Required for MC, Hospitalisation &amp; Compassionate)</label>
                <input type="file" name="attachment" class="w-full text-sm text-gray-500 border border-gray-300 rounded-lg p-2">
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-sm">
                    Submit Application
                </button>
            </div>
        </form>
    </div>

    <!-- Personal Application History -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">My Leave History</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                        <th class="p-3">Type</th>
                        <th class="p-3">Period</th>
                        <th class="p-3">Days</th>
                        <th class="p-3">Reason</th>
                        <th class="p-3">Attachment</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-sm">
                    @forelse($leaves as $leave)
                        <tr>
                            <td class="p-3 font-semibold uppercase">{{ str_replace('_', ' ', $leave->leave_type) }}</td>
                            <td class="p-3">{{ $leave->start_date->format('d/m/Y') }} - {{ $leave->end_date->format('d/m/Y') }}</td>
                            <td class="p-3">{{ $leave->total_days }} days</td>
                            <td class="p-3">{{ $leave->reason }}</td>
                            <td class="p-3">
                                @if($leave->attachment)
                                    <a href="{{ route('leaves.attachment', $leave) }}" target="_blank" class="text-blue-600 underline text-xs font-semibold">View File</a>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">No leave history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $leaves->links() }}</div>
    </div>

</div>
@endsection