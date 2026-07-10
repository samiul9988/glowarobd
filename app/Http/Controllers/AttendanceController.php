<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $service)
    {
        $this->attendanceService = $service;
    }

    public function index(Request $request)
    {
        $date = $request->month
            ? Carbon::createFromFormat('Y-m', $request->month)
            : now();

        $staffs = DB::table('staff')
            ->whereNotNull('staff.user_id')
            ->join('users', 'staff.user_id', '=', 'users.id')
            ->join('roles', 'staff.role_id', '=', 'roles.id')
            ->where('users.banned', 0)
            ->whereNotIn('staff.employment_status', ['terminated', 'resigned'])
            ->orderByDesc('users.recent_login')
            ->select('staff.*', 'roles.name as role_name', 'users.name as staff_name')
            ->get();

        $attendances = Attendance::query()
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->whereIn('staff_id', $staffs->pluck('id'))
            ->get()
            ->groupBy('staff_id');

        $records = $staffs->map(function ($staff) use ($attendances) {
            $attendanceRows = $attendances->get($staff->id, collect());

            $totalDays = $attendanceRows->count();
            $present = $attendanceRows->where('status', 'present')->count();
            $absent = $attendanceRows->where('status', 'absent')->count();
            $leaveCount = $attendanceRows->where('status', 'leave')->count();
            $holidays = $attendanceRows->where('status', 'holiday')->count();
            $weekends = $attendanceRows->where('status', 'offday')->count();
            $lateCount = $attendanceRows->where('status', 'present')->where('late_minutes', '>', 0)->count();
            $workingDays = $present + $absent + $leaveCount;
            $totalWorkMinutes = $attendanceRows->where('status', 'present')->sum('work_minutes');
            $totalOtMinutes = $attendanceRows->sum('overtime_minutes');

            return (object) [
                'staff_id' => $staff->id,
                'employee_id' => $staff->employee_id,
                'staff_name' => $staff->staff_name ?? '—-',
                'role_name' => $staff->role_name ?? '—-',
                'total_days' => $totalDays,
                'working_days' => $workingDays,
                'present' => $present,
                'absent' => $absent,
                'leave_count' => $leaveCount,
                'late_count' => $lateCount,
                'holidays' => $holidays,
                'weekends' => $weekends,
                'total_work_minutes' => $totalWorkMinutes,
                'total_ot_minutes' => $totalOtMinutes,
                'attendance_rate' => $workingDays > 0 ? round(($present / $workingDays) * 100, 2) : 0,
            ];
        });

        $minDate = Attendance::min('date');
        if ($minDate) {
            $minDate = Carbon::parse($minDate)->format('Y-m');
        }

        $todaySummary = $this->attendanceService->getTodaySummary();

        return view('backend.attendances.index', compact('records', 'minDate', 'todaySummary'));
    }

    public function changelogs(Request $request, $id)
    {
        try {
            $decryptedId = decrypt($id);
            $attendance = Attendance::with('logs')->findOrFail($decryptedId);

            return view('backend.attendances.changelogs', compact('attendance'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function checkIn(Request $request)
    {
        try {
            $payload = $request->validate([
                'checkInType' => 'required|string|in:regular,alternative',
                'alternativeDate' => 'required_if:checkInType,alternative|nullable|date|not_in:' . now()->toDateString(),
                'note' => 'required_if:checkInType,alternative|nullable|string|min:3',
            ], [
                'alternativeDate.required_if' => 'Please provide an alternative date for alternative check-in.',
                'alternativeDate.not_in' => 'Alternative date cannot be same as attendance date.',
                'note.required_if' => 'Please provide a note for alternative check-in.',
            ]);
            $user = Auth::user();
            $user->load('staff');

            if (! $user->staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $response = $this->attendanceService->checkIn($user->staff, $payload);

            return response()->json($response);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    public function checkOut(Request $request)
    {
        $user = Auth::user();
        $user->load('staff');

        if (! $user->staff) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $response = $this->attendanceService->checkOut($user->staff);

        return response()->json($response);
    }

    public function overtimeIn(Request $request)
    {
        $user = Auth::user();

        $response = $this->attendanceService->overtimeIn($user);

        return response()->json($response, $response['code'] ?? 200);
    }

    public function overtimeOut(Request $request)
    {
        $user = Auth::user();

        $response = $this->attendanceService->overtimeOut($user);

        return response()->json($response, $response['code'] ?? 200);
    }

    public function getByMonth(Request $request, int $id)
    {
        $date = $request->month ? Carbon::createFromFormat('Y-m', $request->month) : now();

        $attendances = $this->attendanceService->getByMonth($id, $date->year, $date->month);

        $totalDays = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        $absent = $attendances->where('status', 'absent')->count();
        $leave = $attendances->where('status', 'leave')->count();
        $workingDays = $present + $absent + $leave;

        $leaves = \App\Models\Leave::query()
            ->whereNotNull(['approved_start_date', 'approved_end_date', 'user_id'])
            ->where('user_id', getUserIdForStaff($id))
            ->whereYear('approved_start_date', $date->year)
            ->whereMonth('approved_start_date', $date->month)
            ->get();
        $summary = [
            'total' => $totalDays,
            'working' => $workingDays.'/'.$totalDays,
            'present' => $present,
            'absent' => $absent,
            'leaves' => [
                'total' => $leave,
                'paid' => $leaves->sum('paid_days'),
                'unpaid' => $leaves->sum('unpaid_days'),
            ],
            'ot' => gmdate('H:i', $attendances->sum('overtime_minutes') * 60),
        ];

        return response()->json([
            'html' => view('backend.attendances.partials.list', compact('attendances'))->render(),
            'summary' => $summary,
        ]);
    }

    public function update(UpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::find($id);

        if (! $attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found',
            ], 404);
        }

        try {
            $payload = $request->validated();
            $attendance = $this->attendanceService->updateAttendance($attendance, $payload);

            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => collect($e->errors())
                    ->map(fn ($errors) => $errors[0] ?? null)
                    ->filter(), // removes null values
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating attendance: ' . $e->getMessage(), [
                'attendance_id' => $id,
                'payload' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating attendance',
            ], 500);
        }
    }
}
