@extends('layouts.app')

@php
    // Heatmap cell colours (inline so no Tailwind rebuild is needed)
    $cellStyles = [
        'on_time'    => ['bg' => '#22c55e', 'label' => 'On time'],
        'late'       => ['bg' => '#facc15', 'label' => 'Late'],
        'early'      => ['bg' => '#fb923c', 'label' => 'Early clock-out'],
        'late_early' => ['bg' => 'linear-gradient(135deg, #facc15 50%, #fb923c 50%)', 'label' => 'Late + early out'],
        'leave'      => ['bg' => '#60a5fa', 'label' => 'On leave'],
        'absent'     => ['bg' => '#ef4444', 'label' => 'Absent'],
        'pending'    => ['bg' => '#e5e7eb', 'label' => 'Today (not in yet)'],
        'off'        => ['bg' => '#f3f4f6', 'label' => 'Off day'],
        'none'       => ['bg' => '#ffffff', 'label' => ''],
    ];
    $monthKey = $month->format('Y-m');
    $isCurrentMonth = $month->isSameMonth(now());
    $graceMinutes = $officeHours['grace'];
    $lateCutoff = \App\Models\Attendance::lateCutoff()->format('H:i');
    $userFilterUrl = fn ($user) => route('attendance.monthly', ['month' => $monthKey, 'user' => $user->id]) . '#records';
@endphp

@section('content')
<div class="max-w-7xl mx-auto pb-10 space-y-6">

    {{-- Header & month navigation --}}
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Attendance Dashboard</h2>
            <p class="text-gray-500 text-sm mt-1">
                {{ $month->format('F Y') }} · Office hours {{ \Carbon\Carbon::parse($officeHours['start'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($officeHours['end'])->format('g:i A') }}
                @if ($graceMinutes)
                    · {{ $graceMinutes }} min grace (late after {{ $lateCutoff }})
                @endif
            </p>
        </div>
        <form method="GET" action="{{ route('attendance.monthly') }}" class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('officeHoursPanel').classList.toggle('hidden')" class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                <i class="fa-solid fa-gear mr-1"></i> Office Hours
            </button>
            @if ($selectedUser)
                <input type="hidden" name="user" value="{{ $selectedUser->id }}">
            @endif
            <a href="{{ route('attendance.monthly', array_filter(['month' => $month->copy()->subMonth()->format('Y-m'), 'user' => $selectedUser?->id])) }}" class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50" title="Previous month">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <input type="month" name="month" value="{{ $monthKey }}" onchange="this.form.submit()" class="border-gray-300 rounded-lg shadow-sm text-sm">
            <a href="{{ route('attendance.monthly', array_filter(['month' => $month->copy()->addMonth()->format('Y-m'), 'user' => $selectedUser?->id])) }}" class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50" title="Next month">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
            @unless ($isCurrentMonth)
                <a href="{{ route('attendance.monthly') }}" class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">This month</a>
            @endunless
        </form>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
            <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Office hours settings (toggled by the "Office Hours" button) --}}
    @php
        $hasHoursErrors = $errors->hasAny(['office_start_time', 'office_grace_minutes', 'office_end_time']);
    @endphp
    <div id="officeHoursPanel" class="{{ $hasHoursErrors ? '' : 'hidden' }} bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-1"><i class="fa-solid fa-gear text-blue-500 mr-2"></i> Office Hours</h3>
        <p class="text-xs text-gray-500 mb-4">
            Applies to clock-ins and clock-outs from now on. Existing records keep their status.
        </p>
        <form method="POST" action="{{ route('attendance.officeHours') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            @csrf
            @method('PATCH')
            <input type="hidden" name="month" value="{{ $monthKey }}">

            <div>
                <label for="office_start_time" class="block text-sm font-medium text-gray-700 mb-1">Start time</label>
                <input type="time" id="office_start_time" name="office_start_time" value="{{ old('office_start_time', $officeHours['start']) }}" required class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
                @error('office_start_time') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="office_grace_minutes" class="block text-sm font-medium text-gray-700 mb-1">Grace (minutes)</label>
                <input type="number" id="office_grace_minutes" name="office_grace_minutes" value="{{ old('office_grace_minutes', $officeHours['grace']) }}" min="0" max="120" required class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
                @error('office_grace_minutes') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="office_end_time" class="block text-sm font-medium text-gray-700 mb-1">End time</label>
                <input type="time" id="office_end_time" name="office_end_time" value="{{ old('office_end_time', $officeHours['end']) }}" required class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
                @error('office_end_time') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Save</button>
                <button type="button" onclick="document.getElementById('officeHoursPanel').classList.add('hidden')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm">Cancel</button>
            </div>
        </form>
        <p class="text-xs text-gray-500 mt-3">
            Late = clock-in after start time + grace (currently after {{ $lateCutoff }}). Early clock-out = before end time.
        </p>
    </div>

    {{-- KPI cards --}}
    @php
        $kpis = [
            ['label' => 'Attendance Rate', 'value' => $totals['attendance_rate'] !== null ? $totals['attendance_rate'] . '%' : '—', 'sub' => $totals['present'] . ' days present', 'icon' => 'fa-user-check', 'color' => '#2563eb'],
            ['label' => 'On-Time Rate', 'value' => $totals['on_time_rate'] !== null ? $totals['on_time_rate'] . '%' : '—', 'sub' => 'Clock-in by ' . $lateCutoff, 'icon' => 'fa-clock', 'color' => '#16a34a'],
            ['label' => 'Late Arrivals', 'value' => $totals['late'], 'sub' => 'after ' . $lateCutoff, 'icon' => 'fa-person-running', 'color' => '#ca8a04'],
            ['label' => 'Early Clock-Outs', 'value' => $totals['early_out'], 'sub' => 'before ' . $officeHours['end'], 'icon' => 'fa-door-open', 'color' => '#ea580c'],
            ['label' => 'Leave Days', 'value' => $totals['leave'], 'sub' => 'approved, working days', 'icon' => 'fa-umbrella-beach', 'color' => '#0284c7'],
            ['label' => 'Absences', 'value' => $totals['absent'], 'sub' => 'no clock-in, no leave', 'icon' => 'fa-user-xmark', 'color' => '#dc2626'],
        ];
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
        @foreach ($kpis as $kpi)
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $kpi['label'] }}</p>
                    <i class="fa-solid {{ $kpi['icon'] }}" style="color: {{ $kpi['color'] }}"></i>
                </div>
                <h3 class="text-3xl font-black text-gray-800 mt-2">{{ $kpi['value'] }}</h3>
                <p class="text-xs text-gray-400 mt-1">{{ $kpi['sub'] }}</p>
            </div>
        @endforeach
    </div>

    @if ($rows->isEmpty())
        <div class="bg-white rounded-2xl p-10 shadow-sm border border-gray-100 text-center text-gray-500">
            No staff who clock in (Admin Office / Staff) yet.
        </div>
    @else

    {{-- Heatmap --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <h3 class="font-bold text-gray-800"><i class="fa-solid fa-calendar-days text-blue-500 mr-2"></i> Daily Attendance Map</h3>
            <div class="flex flex-wrap gap-3 text-xs text-gray-600">
                @foreach (['on_time', 'late', 'early', 'late_early', 'leave', 'absent', 'off'] as $type)
                    <span class="flex items-center gap-1">
                        <span class="inline-block w-3 h-3 rounded-sm border border-gray-200" style="background: {{ $cellStyles[$type]['bg'] }}"></span>
                        {{ $cellStyles[$type]['label'] }}
                    </span>
                @endforeach
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="text-xs" style="border-collapse: separate; border-spacing: 3px;">
                <thead>
                    <tr>
                        <th class="text-left pr-3 font-semibold text-gray-500" style="min-width: 140px;">Staff</th>
                        @foreach ($days as $d)
                            <th class="font-medium text-center {{ $d['date']->isToday() ? 'text-blue-600' : 'text-gray-400' }}" style="width: 22px;">
                                <div>{{ $d['date']->format('j') }}</div>
                                <div style="font-size: 9px;">{{ substr($d['date']->format('D'), 0, 1) }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pageRows as $row)
                        <tr>
                            <td class="pr-3 py-1 whitespace-nowrap">
                                <a href="{{ $userFilterUrl($row['user']) }}" class="font-semibold text-gray-700 hover:text-blue-600 hover:underline">{{ $row['user']->name }}</a>
                            </td>
                            @foreach ($row['cells'] as $cell)
                                <td title="{{ $cell['title'] }}" style="width: 22px; height: 22px; border-radius: 4px; background: {{ $cellStyles[$cell['type']]['bg'] }}; {{ $cell['type'] === 'none' ? 'border: 1px dashed #e5e7eb;' : '' }}"></td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-400 mt-3">Hover a square to see clock-in / clock-out times. Click a name to see their records.</p>
        @if ($pageRows->hasPages())
            <div class="mt-4">{{ $pageRows->links() }}</div>
        @endif
    </div>

    {{-- Per-staff summary --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-users text-blue-500 mr-2"></i> Staff Summary</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b bg-gray-50 text-gray-600">
                        <th class="p-3">Staff</th>
                        <th class="p-3 text-center">Present</th>
                        <th class="p-3 text-center">Late</th>
                        <th class="p-3 text-center">Leave</th>
                        <th class="p-3 text-center">Absent</th>
                        <th class="p-3" style="min-width: 160px;">Punctuality</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($pageRows as $row)
                        @php
                            $p = $row['punctuality'];
                            $barColor = $p === null ? '#e5e7eb' : ($p >= 90 ? '#22c55e' : ($p >= 70 ? '#facc15' : '#ef4444'));
                            $isSelected = $selectedUser && $selectedUser->id === $row['user']->id;
                        @endphp
                        <tr onclick="window.location='{{ $userFilterUrl($row['user']) }}'" class="cursor-pointer {{ $isSelected ? 'bg-blue-50' : 'hover:bg-gray-50' }}" title="View {{ $row['user']->name }}'s records">
                            <td class="p-3">
                                <a href="{{ $userFilterUrl($row['user']) }}" class="font-semibold text-gray-800 hover:text-blue-600">{{ $row['user']->name }}</a>
                                <div class="text-xs text-gray-400">{{ ucwords(str_replace('_', ' ', $row['user']->role)) }}</div>
                            </td>
                            <td class="p-3 text-center font-semibold">{{ $row['stats']['present'] }}</td>
                            <td class="p-3 text-center {{ $row['stats']['late'] ? 'text-yellow-600 font-bold' : 'text-gray-400' }}">{{ $row['stats']['late'] }}</td>
                            <td class="p-3 text-center {{ $row['stats']['leave'] ? 'text-blue-600 font-bold' : 'text-gray-400' }}">{{ $row['stats']['leave'] }}</td>
                            <td class="p-3 text-center {{ $row['stats']['absent'] ? 'text-red-600 font-bold' : 'text-gray-400' }}">{{ $row['stats']['absent'] }}</td>
                            <td class="p-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                        <div class="h-2 rounded-full" style="width: {{ $p ?? 0 }}%; background: {{ $barColor }};"></div>
                                    </div>
                                    <span class="text-xs font-bold text-gray-600 w-10 text-right">{{ $p !== null ? $p . '%' : '—' }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $pageRows->links() }}</div>
    </div>

    {{-- Attendance records (formerly the Attendance Report page) --}}
    <div id="records" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <h3 class="font-bold text-gray-800">
                <i class="fa-solid fa-clipboard-user text-blue-500 mr-2"></i> Attendance Records
                <span class="font-normal text-gray-500">
                    · {{ $selectedUser?->name ?? 'All staff' }} · {{ $selectedDate?->format('d M Y') ?? $month->format('F Y') }}
                </span>
            </h3>
            <form method="GET" action="{{ route('attendance.monthly') }}#records" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="month" value="{{ $monthKey }}">
                <select name="user" class="border-gray-300 rounded-lg shadow-sm text-sm">
                    <option value="">All staff</option>
                    @foreach ($staff as $member)
                        <option value="{{ $member->id }}" @selected($selectedUser?->id === $member->id)>{{ $member->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="date" value="{{ $selectedDate?->toDateString() }}" min="{{ $month->toDateString() }}" max="{{ $month->copy()->endOfMonth()->toDateString() }}" class="border-gray-300 rounded-lg shadow-sm text-sm">
                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Filter</button>
                @if ($selectedUser || $selectedDate)
                    <a href="{{ route('attendance.monthly', ['month' => $monthKey]) }}#records" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b bg-gray-50 text-gray-600">
                        <th class="p-3">Staff</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">Clock In</th>
                        <th class="p-3">Clock Out</th>
                        <th class="p-3">Distance (m)</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($records as $item)
                        <tr>
                            <td class="p-3 font-semibold">{{ $item->user->name ?? '-' }}</td>
                            <td class="p-3">{{ $item->created_at->format('D, d/m/Y') }}</td>
                            <td class="p-3">{{ $item->clock_in ? $item->clock_in->format('H:i') : '-' }}</td>
                            <td class="p-3">{{ $item->clock_out ? $item->clock_out->format('H:i') : '-' }}</td>
                            <td class="p-3">{{ round($item->distance_meters) }} m</td>
                            <td class="p-3">@include('attendance._status')</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-3 text-center text-gray-500">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $records->links() }}</div>
    </div>
    @endif
</div>
@endsection
