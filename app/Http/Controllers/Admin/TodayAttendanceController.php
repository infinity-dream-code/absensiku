<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use Carbon\Carbon;

class TodayAttendanceController extends Controller
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
        // Filter tanggal: default hari ini, bisa pilih tanggal lain (dalam 1 bulan atau kapan saja)
        $selectedDate = $request->filled('date')
            ? Carbon::parse($request->date, 'Asia/Jakarta')->startOfDay()
            : Carbon::today('Asia/Jakarta');

        // Hanya karyawan wajib absen (jenis = 1)
        $employees = User::where('role', 'user')
            ->where('jenis', 1)
            ->orderBy('name')
            ->get();
        $totalEmployees = $employees->count();
        $employeeIds = $employees->pluck('id');

        // User ID yang sudah absen - earliest check_in; jenis absen = log terakhir
        $attendanceRecords = Attendance::with('logs')
            ->whereIn('user_id', $employeeIds)
            ->whereDate('attendance_date', $selectedDate)
            ->whereNotNull('check_in')
            ->get();
        $presentUserIds = $attendanceRecords
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->sortBy('check_in')->first())
            ->keys()
            ->flip()
            ->all();

        $leaveUserIds = Leave::whereIn('user_id', $employeeIds)
            ->whereDate('leave_date', $selectedDate)
            ->pluck('user_id')
            ->unique()
            ->flip()
            ->all();

        $countPresent = 0;
        $countOnLeave = 0;
        $countAbsent = 0;
        $employeeCards = [];

        foreach ($employees as $user) {
            $userId = $user->id;
            $hasLeave = isset($leaveUserIds[$userId]);
            $hasAttendance = isset($presentUserIds[$userId]);

            if ($hasLeave) {
                $countOnLeave++;
                $leaveRecord = Leave::where('user_id', $userId)->whereDate('leave_date', $selectedDate)->first();
                $leaveTypeLabel = $leaveRecord ? ($leaveRecord->leave_type === 'cuti' ? 'Cuti' : ($leaveRecord->leave_type === 'sakit' ? 'Sakit' : 'Izin')) : '-';
                $employeeCards[] = [
                    'user' => $user,
                    'status' => 'leave',
                    'label' => 'Izin',
                    'detail' => 'Jenis: ' . $leaveTypeLabel,
                ];
            } elseif ($hasAttendance) {
                $countPresent++;
                $att = $attendanceRecords->where('user_id', $userId)->sortBy('check_in')->first();
                $earliest = $att ? $att->earliestCheckInTime() : null;
                $checkInTime = $earliest ? $earliest->format('H:i') : '-';
                $employeeCards[] = [
                    'user' => $user,
                    'status' => 'present',
                    'label' => 'Sudah Absen',
                    'detail' => 'Check In: ' . $checkInTime,
                    'work_type' => $att ? $att->latestWorkType() : '-',
                ];
            } else {
                $countAbsent++;
                $employeeCards[] = [
                    'user' => $user,
                    'status' => 'absent',
                    'label' => 'Tidak Absen',
                    'detail' => 'Tidak ada catatan absensi',
                ];
            }
        }

        return view('admin.today-attendance.index', compact(
            'selectedDate',
            'totalEmployees',
            'countPresent',
            'countOnLeave',
            'countAbsent',
            'employeeCards'
        ));
    }
}
