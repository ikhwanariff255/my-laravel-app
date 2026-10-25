<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        return view('attendance.index', compact('todayAttendance', 'attendances', 'user'));
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

        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now(),
            'clock_in_latitude' => $request->latitude,
            'clock_in_longitude' => $request->longitude,
            'distance_meters' => $distance,
            'status' => now()->format('H:i') > config('attendance.late_after') ? 'late' : 'present',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Clock-in successful! Distance: ' . round($distance) . ' meters.'
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

        $attendance->update([
            'clock_out' => now(),
            'clock_out_latitude' => $request->latitude,
            'clock_out_longitude' => $request->longitude,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Clock-out successful!'
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

    public function report(Request $request)
    {
        // Attendance report is for the Owner only
        if (Auth::user()->role !== 'owner') {
            abort(403, 'Access denied. The attendance report is for the Owner only.');
        }

        $query = Attendance::with('user')->orderBy('created_at', 'desc');

        // Filter by date if provided
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $attendances = $query->paginate(20)->withQueryString();

        return view('attendance.report', compact('attendances'));
    }
}
