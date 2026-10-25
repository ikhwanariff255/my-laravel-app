<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\CompanySetting;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Only Admin Office & Staff clock in
        if (!$user->canClockIn()) {
            abort(403, 'Access denied.');
        }

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $officeHours = CompanySetting::officeHours();

        return view('attendance.index', compact('todayAttendance', 'attendances', 'user', 'officeHours'));
    }

    public function clockIn(Request $request)
    {
        if (!Auth::user()->canClockIn()) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $allowedRadius = config('attendance.radius_meters');

        // Distance from the user to the office
        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            config('attendance.office_latitude'),
            config('attendance.office_longitude')
        );

        if ($distance > $allowedRadius) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed: You are ' . round($distance) . ' meters away from the office (maximum ' . $allowedRadius . 'm).'
            ], 400);
        }

        $user = Auth::user();

        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already clocked in today.'
            ], 400);
        }

        $isLate = Attendance::isLateAt(now());

        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now(),
            'clock_in_latitude' => $request->latitude,
            'clock_in_longitude' => $request->longitude,
            'distance_meters' => $distance,
            'status' => $isLate ? 'late' : 'present',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => ($isLate ? 'Clock-in recorded as LATE (late after ' . Attendance::lateCutoff()->format('H:i') . ').' : 'Clock-in successful!')
                . ' Distance: ' . round($distance) . ' meters.'
        ]);
    }

    public function clockOut(Request $request)
    {
        if (!Auth::user()->canClockIn()) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if (!$attendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'No clock-in record found for today.'
            ], 404);
        }

        if ($attendance->clock_out) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already clocked out today.'
            ], 400);
        }

        $isEarly = Attendance::isEarlyOutAt(now());

        // Before office end time the user must confirm; it is then flagged as an early clock-out
        if ($isEarly && !$request->boolean('confirm_early')) {
            return response()->json([
                'status' => 'confirm',
                'message' => 'Office hours end at ' . CompanySetting::officeHours()['end'] . '. Clocking out now will be recorded as an EARLY CLOCK-OUT. Continue?'
            ], 409);
        }

        $attendance->update([
            'clock_out' => now(),
            'early_out' => $isEarly,
            'clock_out_latitude' => $request->latitude,
            'clock_out_longitude' => $request->longitude,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $isEarly ? 'Clock-out recorded as EARLY CLOCK-OUT.' : 'Clock-out successful!'
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // In meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    // The report now lives inside the monthly dashboard; keep old links working
    public function report(Request $request)
    {
        $date = $request->filled('date') ? rescue(fn () => Carbon::parse($request->input('date')), null, false) : null;

        return redirect()->route('attendance.monthly', array_filter([
            'month' => $date?->format('Y-m'),
            'date' => $date?->toDateString(),
        ]));
    }

    public function monthly(Request $request)
    {
        // Monthly attendance dashboard is for the Owner only
        if (Auth::user()->role !== 'owner') {
            abort(403, 'Access denied. The attendance dashboard is for the Owner only.');
        }

        $month = rescue(
            fn () => Carbon::createFromFormat('Y-m', (string) $request->input('month'))->startOfMonth(),
            now()->startOfMonth(),
            false
        );
        $monthEnd = $month->copy()->endOfMonth();
        $today = Carbon::today();
        $workDays = config('attendance.work_days');

        $staff = User::whereIn('role', ['admin_office', 'staff'])->orderBy('name')->get();

        $attendances = Attendance::whereIn('user_id', $staff->pluck('id'))
            ->whereBetween('created_at', [$month, $monthEnd])
            ->get()
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->keyBy(fn ($a) => $a->created_at->toDateString()));

        $leaves = Leave::whereIn('user_id', $staff->pluck('id'))
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $monthEnd)
            ->whereDate('end_date', '>=', $month)
            ->get()
            ->groupBy('user_id');

        $days = collect(CarbonPeriod::create($month, $monthEnd))->map(fn ($d) => [
            'date' => $d->copy(),
            'workday' => in_array($d->dayOfWeekIso, $workDays),
            'future' => $d->gt($today),
        ]);

        $rows = $staff->map(function ($user) use ($days, $attendances, $leaves, $today) {
            $userAttendance = $attendances->get($user->id, collect());
            $userLeaves = $leaves->get($user->id, collect());
            $stats = ['present' => 0, 'late' => 0, 'early_out' => 0, 'leave' => 0, 'absent' => 0];
            $cells = [];

            foreach ($days as $d) {
                $key = $d['date']->toDateString();
                $label = $d['date']->format('D, d M');
                $record = $userAttendance->get($key);
                $onLeave = $userLeaves->first(fn ($l) => $d['date']->between($l->start_date, $l->end_date));

                if ($record) {
                    $isLate = $record->status === 'late';
                    $stats['present']++;
                    $stats['late'] += $isLate ? 1 : 0;
                    $stats['early_out'] += $record->early_out ? 1 : 0;
                    $type = $isLate && $record->early_out ? 'late_early' : ($isLate ? 'late' : ($record->early_out ? 'early' : 'on_time'));
                    $cells[] = ['type' => $type, 'title' => $label . ' — In ' . $record->clock_in->format('H:i') . ', Out ' . ($record->clock_out?->format('H:i') ?? '—')];
                } elseif (!$d['workday']) {
                    $cells[] = ['type' => 'off', 'title' => $label . ' — Off day'];
                } elseif ($onLeave) {
                    $stats['leave']++;
                    $cells[] = ['type' => 'leave', 'title' => $label . ' — On leave (' . ucfirst($onLeave->leave_type) . ')'];
                } elseif ($d['future'] || $d['date']->lt($user->created_at->copy()->startOfDay())) {
                    $cells[] = ['type' => 'none', 'title' => $label];
                } elseif ($d['date']->isSameDay($today)) {
                    $cells[] = ['type' => 'pending', 'title' => $label . ' — Not clocked in yet'];
                } else {
                    $stats['absent']++;
                    $cells[] = ['type' => 'absent', 'title' => $label . ' — Absent'];
                }
            }

            return [
                'user' => $user,
                'cells' => $cells,
                'stats' => $stats,
                'punctuality' => $stats['present'] ? (int) round(($stats['present'] - $stats['late']) / $stats['present'] * 100) : null,
            ];
        });

        $totals = [];
        foreach (['present', 'late', 'early_out', 'leave', 'absent'] as $key) {
            $totals[$key] = $rows->sum("stats.$key");
        }
        $expected = $totals['present'] + $totals['absent'];
        $totals['attendance_rate'] = $expected ? (int) round($totals['present'] / $expected * 100) : null;
        $totals['on_time_rate'] = $totals['present'] ? (int) round(($totals['present'] - $totals['late']) / $totals['present'] * 100) : null;
        // Map & staff table are paginated; the totals still cover everyone
        $perPage = 15;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $pageRows = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->only('month', 'user', 'date')]
        );

        // Attendance records for the month, optionally filtered by staff and/or date
        $selectedUser = $staff->firstWhere('id', (int) $request->input('user'));
        $selectedDate = $request->filled('date') ? rescue(fn () => Carbon::parse($request->input('date')), null, false) : null;
        if ($selectedDate && !$selectedDate->isSameMonth($month)) {
            $selectedDate = null;
        }

        $records = Attendance::with('user')
            ->whereIn('user_id', $staff->pluck('id'))
            ->whereBetween('created_at', [$month, $monthEnd])
            ->when($selectedUser, fn ($q) => $q->where('user_id', $selectedUser->id))
            ->when($selectedDate, fn ($q) => $q->whereDate('created_at', $selectedDate))
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'records_page')
            ->withQueryString()
            ->fragment('records');

        $officeHours = CompanySetting::officeHours();

        return view('attendance.monthly', compact('month', 'staff', 'rows', 'days', 'pageRows', 'totals', 'records', 'selectedUser', 'selectedDate', 'officeHours'));
    }

    public function updateOfficeHours(Request $request)
    {
        if (Auth::user()->role !== 'owner') {
            abort(403, 'Access denied. Only the Owner can change office hours.');
        }

        $validated = $request->validate([
            'office_start_time' => 'required|date_format:H:i',
            'office_grace_minutes' => 'required|integer|min:0|max:120',
            'office_end_time' => 'required|date_format:H:i|after:office_start_time',
        ], [
            'office_end_time.after' => 'End time must be after the start time.',
        ]);

        $settings = CompanySetting::first() ?? new CompanySetting();
        $settings->fill($validated)->save();

        return redirect()
            ->route('attendance.monthly', $request->only('month'))
            ->with('success', 'Office hours updated. New times apply to clock-ins and clock-outs from now on.');
    }
}
