<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\RefBulan;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceValidationController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role !== 'admin') {
                return redirect()->route('admin.login');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $employees = User::where('role', 'user')->where('jenis', 1)->orderBy('name')->get(['id', 'name', 'nip', 'nik']);
        $refMonths = RefBulan::orderBy('year')->orderBy('month_code')->get();

        $selectedUserId = (int) $request->input('user_id', 0);
        $selectedRefId = (int) $request->input('ref_bulan_id', 0);

        if ($selectedRefId <= 0 && $refMonths->isNotEmpty()) {
            $selectedRefId = (int) $refMonths->last()->id;
        }

        $selectedRef = $selectedRefId > 0 ? $refMonths->firstWhere('id', $selectedRefId) : null;
        $selectedEmployee = $selectedUserId > 0 ? $employees->firstWhere('id', $selectedUserId) : null;

        $calendar = [];
        if ($selectedRef) {
            $year = (int) $selectedRef->year;
            $month = (int) $selectedRef->month_code;
            $start = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta');
            $end = $start->copy()->endOfMonth();
            $today = Carbon::now('Asia/Jakarta')->startOfDay();
            $isCurrentMonth = ($today->year === $year && $today->month === $month);
            $maxClickableDate = $isCurrentMonth ? $today->copy() : $end->copy();

            $holidays = Holiday::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get(['date', 'description']);
            $holidayMap = [];
            foreach ($holidays as $holiday) {
                $holidayMap[Carbon::parse($holiday->date)->format('Y-m-d')] = $holiday->description;
            }

            $attendanceMap = [];
            $leaveMap = [];
            $checkInEndTime = Setting::getSettings()->check_in_end ?: '09:00:00';

            if ($selectedEmployee) {
                $attendances = Attendance::where('user_id', $selectedEmployee->id)
                    ->whereYear('attendance_date', $year)
                    ->whereMonth('attendance_date', $month)
                    ->get(['id', 'attendance_date', 'check_in']);

                foreach ($attendances as $attendance) {
                    $dateStr = Carbon::parse($attendance->attendance_date)->format('Y-m-d');
                    $attendanceMap[$dateStr] = $attendance;
                }

                $leaves = Leave::where('user_id', $selectedEmployee->id)
                    ->whereYear('leave_date', $year)
                    ->whereMonth('leave_date', $month)
                    ->get(['leave_date', 'leave_type']);
                foreach ($leaves as $leave) {
                    $dateStr = Carbon::parse($leave->leave_date)->format('Y-m-d');
                    $leaveMap[$dateStr] = $leave->leave_type;
                }
            }

            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $dateStr = $cursor->format('Y-m-d');
                $isWeekend = $cursor->isWeekend();
                $isHoliday = isset($holidayMap[$dateStr]);
                $isFuture = $cursor->gt($today);
                $leaveType = $leaveMap[$dateStr] ?? null;
                $attendance = $attendanceMap[$dateStr] ?? null;
                $status = 'disabled';
                $statusLabel = 'Non-kerja';
                $canValidate = false;

                if ($isFuture) {
                    $status = 'future';
                    $statusLabel = 'Belum lewat';
                } elseif ($isWeekend || $isHoliday) {
                    $status = 'disabled';
                    $statusLabel = $isHoliday ? 'Hari libur' : 'Akhir pekan';
                } elseif ($leaveType) {
                    $status = 'leave';
                    $statusLabel = 'Izin/' . strtoupper($leaveType);
                } elseif ($attendance && $attendance->check_in) {
                    $checkInEnd = Carbon::parse($dateStr . ' ' . $checkInEndTime, 'Asia/Jakarta');
                    $checkInTime = Carbon::parse($attendance->check_in, 'Asia/Jakarta');
                    if ($checkInTime->gt($checkInEnd)) {
                        $status = 'late';
                        $statusLabel = 'Terlambat';
                    } else {
                        $status = 'ontime';
                        $statusLabel = 'Tepat waktu';
                    }
                } else {
                    $status = 'alpha';
                    $statusLabel = 'Alpha';
                    $canValidate = $selectedEmployee !== null && $cursor->lte($maxClickableDate);
                }

                $calendar[] = [
                    'date' => $dateStr,
                    'day_name' => $cursor->locale('id')->isoFormat('dddd'),
                    'day_num' => (int) $cursor->format('d'),
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'is_holiday' => $isHoliday,
                    'holiday_description' => $holidayMap[$dateStr] ?? null,
                    'leave_type' => $leaveType,
                    'can_validate' => $canValidate,
                ];

                $cursor->addDay();
            }
        }

        return view('admin.attendance-validation.index', [
            'employees' => $employees,
            'refMonths' => $refMonths,
            'selectedUserId' => $selectedUserId,
            'selectedRefId' => $selectedRefId,
            'selectedRef' => $selectedRef,
            'selectedEmployee' => $selectedEmployee,
            'calendar' => $calendar,
        ]);
    }

    public function validateLate(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'ref_bulan_id' => 'nullable|integer',
        ]);

        $user = User::where('id', (int) $validated['user_id'])->where('role', 'user')->firstOrFail();
        $date = Carbon::parse($validated['date'], 'Asia/Jakarta')->startOfDay();
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        if ($date->gt($today)) {
            return back()->with('error', 'Tanggal belum lewat, belum bisa divalidasi.');
        }

        if ($date->isWeekend()) {
            return back()->with('error', 'Tanggal akhir pekan tidak bisa divalidasi.');
        }

        $isHoliday = Holiday::whereDate('date', $date->format('Y-m-d'))->exists();
        if ($isHoliday) {
            return back()->with('error', 'Tanggal hari libur tidak bisa divalidasi.');
        }

        $hasLeave = Leave::where('user_id', $user->id)->whereDate('leave_date', $date->format('Y-m-d'))->exists();
        if ($hasLeave) {
            return back()->with('error', 'Tanggal ini adalah izin/cuti/sakit, tidak bisa divalidasi.');
        }

        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $date->format('Y-m-d'))
            ->first();

        if ($existingAttendance && $existingAttendance->check_in) {
            return back()->with('error', 'Tanggal tersebut sudah memiliki absensi.');
        }

        DB::transaction(function () use ($user, $date, $existingAttendance) {
            $checkInTime = $date->copy()->setTime(12, 0, 0);
            $checkOutTime = $date->copy()->setTime(17, 0, 0);

            if ($existingAttendance) {
                $existingAttendance->update([
                    'work_type' => $existingAttendance->work_type ?: 'WFA',
                    'notes' => trim(($existingAttendance->notes ? $existingAttendance->notes . ' | ' : '') . 'Validasi admin: terlambat'),
                    'check_in' => $checkInTime,
                    'check_out' => $existingAttendance->check_out ?: $checkOutTime,
                    'is_validate' => 1,
                ]);
                $attendance = $existingAttendance;
            } else {
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'attendance_date' => $date->format('Y-m-d'),
                    'work_type' => 'WFA',
                    'notes' => 'Validasi admin: terlambat',
                    'check_in' => $checkInTime,
                    'check_out' => $checkOutTime,
                    'location_valid' => 1,
                    'is_validate' => 1,
                ]);
            }

            AttendanceLog::create([
                'attendance_id' => $attendance->id,
                'check_in_time' => $checkInTime,
                'status' => $attendance->work_type ?: 'WFA',
                'notes' => 'Validasi admin: terlambat',
            ]);
        });

        return back()->with('success', 'Alpha berhasil divalidasi menjadi terlambat (check-in jam 12:00).');
    }
}
