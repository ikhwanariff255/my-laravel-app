<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveController extends Controller
{
    // Entitlements according to Offer Letter
    private const ANNUAL_ENTITLEMENT = 12; // 1 day per completed month
    private const MC_ENTITLEMENT = 14;     // 14 days per year
    private const HOSP_ENTITLEMENT = 60;   // 60 days per year

    // Leave types counted in calendar days; all others count working days (Mon-Fri)
    private const CALENDAR_DAY_TYPES = ['maternity'];

    /**
     * Calculate leave balances for a user in the current year.
     * Pending applications are reserved against the balance so multiple
     * pending requests cannot exceed it. $excludeLeaveId leaves one
     * application out (used when approving it).
     */
    private function getLeaveBalance($userId, $excludeLeaveId = null)
    {
        $currentYear = Carbon::now()->year;

        $leaves = Leave::where('user_id', $userId)
            ->whereIn('status', ['approved', 'pending'])
            ->whereYear('start_date', $currentYear)
            ->when($excludeLeaveId, fn ($q) => $q->where('id', '!=', $excludeLeaveId))
            ->selectRaw('leave_type, status, SUM(total_days) as total_days')
            ->groupBy('leave_type', 'status')
            ->get();

        $sum = function (array $types, string $status) use ($leaves) {
            return (int) $leaves->whereIn('leave_type', $types)->where('status', $status)->sum('total_days');
        };

        // Emergency leave is deducted from Annual Leave balance
        $annualTypes = ['annual', 'emergency'];

        $balance = [];
        foreach ([
            'annual' => [$annualTypes, self::ANNUAL_ENTITLEMENT],
            'mc'     => [['mc'], self::MC_ENTITLEMENT],
            'hosp'   => [['hospitalisation'], self::HOSP_ENTITLEMENT],
        ] as $key => [$types, $entitlement]) {
            $used = $sum($types, 'approved');
            $pending = $sum($types, 'pending');

            $balance[$key . '_total'] = $entitlement;
            $balance[$key . '_used'] = $used;
            $balance[$key . '_pending'] = $pending;
            $balance[$key . '_remaining'] = max(0, $entitlement - $used - $pending);
        }

        return $balance;
    }

    /**
     * Number of leave days between two dates (inclusive).
     */
    private function countLeaveDays(string $leaveType, Carbon $start, Carbon $end): int
    {
        if (in_array($leaveType, self::CALENDAR_DAY_TYPES)) {
            return $start->diffInDays($end) + 1;
        }

        return collect(CarbonPeriod::create($start, $end))
            ->filter(fn (Carbon $day) => $day->isWeekday())
            ->count();
    }

    /**
     * Returns an error message if $days exceeds the remaining balance for the leave type.
     */
    private function balanceError(string $leaveType, int $days, array $balance): ?string
    {
        $checks = [
            'annual'          => ['annual', 'Annual Leave'],
            'emergency'       => ['annual', 'Annual Leave'],
            'mc'              => ['mc', 'Medical Leave (MC)'],
            'hospitalisation' => ['hosp', 'Hospitalisation Leave'],
        ];

        if (!isset($checks[$leaveType])) {
            return null;
        }

        [$key, $label] = $checks[$leaveType];
        $remaining = $balance[$key . '_remaining'];

        if ($days > $remaining) {
            return 'Insufficient ' . $label . ' balance. Remaining: ' . $remaining . ' days (including pending applications).';
        }

        return null;
    }

    /**
     * Display personal leave form, personal balances, and history.
     */
    public function index()
    {
        $userId = Auth::id();
        $leaveBalance = $this->getLeaveBalance($userId);

        $leaves = Leave::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('leaves.index', compact('leaves', 'leaveBalance'));
    }

    /**
     * Process leave application submission.
     */
    public function store(Request $request)
    {
        $rules = [
            'leave_type' => 'required|in:annual,emergency,mc,hospitalisation,compassionate,maternity,paternity,unpaid',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];

        // MC, Hospitalisation, and Compassionate leave require proof
        if (in_array($request->leave_type, ['mc', 'hospitalisation', 'compassionate'])) {
            $rules['attachment'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
        }

        // Annual leave must be applied at least 3 days in advance
        if ($request->leave_type === 'annual') {
            $rules['start_date'] = 'required|date|after_or_equal:' . now()->addDays(3)->format('Y-m-d');
        }

        $request->validate($rules, [
            'start_date.after_or_equal' => 'Annual leave must be applied at least 3 days in advance.',
            'attachment.required'       => 'Please upload a valid MC or supporting document for this leave type.',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $totalDays = $this->countLeaveDays($request->leave_type, $start, $end);

        if ($totalDays === 0) {
            return redirect()->back()->withInput()->withErrors(['start_date' => 'The selected dates fall on weekends only. Please choose working days.']);
        }

        // Balance checking
        $error = $this->balanceError($request->leave_type, $totalDays, $this->getLeaveBalance(Auth::id()));
        if ($error) {
            return redirect()->back()->withInput()->withErrors(['start_date' => $error]);
        }

        // Stored on the private disk; served through leaves.attachment
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leaves', 'local');
        }

        Leave::create([
            'user_id'    => Auth::id(),
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'total_days' => $totalDays,
            'reason'     => $request->reason,
            'attachment' => $attachmentPath,
            'status'     => 'pending',
        ]);

        return redirect()->route('leaves.index')->with('success', 'Leave application submitted successfully!');
    }

    /**
     * Show a leave attachment to its owner or to Owner / Admin Office.
     */
    public function attachment(Leave $leave)
    {
        $user = Auth::user();

        if ($leave->user_id !== $user->id && !$user->canManageSystem()) {
            abort(403, 'Access denied.');
        }

        if (!$leave->attachment) {
            abort(404);
        }

        // Older uploads were stored on the public disk
        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($leave->attachment)) {
                return Storage::disk($disk)->response($leave->attachment);
            }
        }

        abort(404);
    }

    /**
     * Leave approvals & overall staff leave balances (Owner & Admin Office).
     */
    public function manage()
    {
        $leaves = Leave::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Fetch leave balances for all staff for Owner and Admin Office overview
        $users = User::orderBy('name')->get();
        $staffBalances = [];
        foreach ($users as $user) {
            $staffBalances[] = [
                'user'    => $user,
                'balance' => $this->getLeaveBalance($user->id),
            ];
        }

        return view('leaves.manage', compact('leaves', 'staffBalances'));
    }

    /**
     * Update leave application status.
     */
    public function updateStatus(Request $request, Leave $leave)
    {
        $request->validate([
            'status'       => 'required|in:approved,rejected',
            'admin_remark' => 'nullable|string|max:255',
        ]);

        if ($leave->status !== 'pending') {
            return redirect()->back()->withErrors(['status' => 'This leave application has already been processed.']);
        }

        if ($leave->user_id === Auth::id()) {
            return redirect()->back()->withErrors(['status' => 'You cannot approve or reject your own leave application.']);
        }

        // Re-check the balance at approval time, leaving this application out
        if ($request->status === 'approved') {
            $error = $this->balanceError($leave->leave_type, $leave->total_days, $this->getLeaveBalance($leave->user_id, $leave->id));
            if ($error) {
                return redirect()->back()->withErrors(['status' => 'Cannot approve: ' . $error]);
            }
        }

        $leave->update([
            'status'       => $request->status,
            'admin_remark' => $request->admin_remark,
        ]);

        return redirect()->back()->with('success', 'Leave application status updated successfully.');
    }
}
