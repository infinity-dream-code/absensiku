<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Holiday;
use App\Models\RefBulan;
use App\Models\ReferensiWfo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WfoValidationController extends Controller
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
        $employees = User::where('role', 'user')
            ->where('jenis', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'nip', 'nik']);

        $refMonths = RefBulan::orderBy('year')->orderBy('month_code')->get();

        $selectedUserId = (int) $request->input('user_id', 0);
        $selectedRefId = (int) $request->input('ref_bulan_id', 0);

        if ($selectedRefId <= 0 && $refMonths->isNotEmpty()) {
            $selectedRefId = (int) $refMonths->last()->id;
        }

        $selectedRef = $selectedRefId > 0 ? $refMonths->firstWhere('id', $selectedRefId) : null;
        $selectedEmployee = $selectedUserId > 0 ? $employees->firstWhere('id', $selectedUserId) : null;

        $rows = [];
        if ($selectedRef && $selectedEmployee) {
            $rows = $this->buildRows($selectedEmployee, $selectedRef);
        }

        return view('admin.wfo-validation.index', [
            'employees' => $employees,
            'refMonths' => $refMonths,
            'selectedUserId' => $selectedUserId,
            'selectedRefId' => $selectedRefId,
            'selectedRef' => $selectedRef,
            'selectedEmployee' => $selectedEmployee,
            'rows' => $rows,
        ]);
    }

    public function apply(Request $request)
    {
        $validated = $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'user_id' => 'required|exists:users,id',
            'ref_bulan_id' => 'nullable|integer',
        ]);

        $attendance = Attendance::with('user')->findOrFail((int) $validated['attendance_id']);
        if ((int) $attendance->user_id !== (int) $validated['user_id']) {
            return back()->with('error', 'Data absensi tidak sesuai dengan karyawan yang dipilih.');
        }

        $attendanceDate = Carbon::parse($attendance->attendance_date, 'Asia/Jakarta')->startOfDay();
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        if ($attendanceDate->gt($today)) {
            return back()->with('error', 'Tanggal belum lewat, belum bisa divalidasi.');
        }
        if ($attendanceDate->isWeekend()) {
            return back()->with('error', 'Tanggal akhir pekan tidak bisa divalidasi WFO.');
        }
        if (Holiday::whereDate('date', $attendanceDate->format('Y-m-d'))->exists()) {
            return back()->with('error', 'Tanggal hari libur tidak bisa divalidasi WFO.');
        }

        $reference = ReferensiWfo::where('custid', (int) $validated['user_id'])->first();
        if (!$reference) {
            return back()->with('error', 'Referensi WFO karyawan belum diatur.');
        }

        $expectedWfo = $this->isExpectedWfo($reference, $attendanceDate);
        if (!$expectedWfo) {
            return back()->with('error', 'Tanggal tersebut tidak termasuk jadwal WFO karyawan.');
        }

        $attendance->loadMissing('logs');
        $isAlreadyWfo = $attendance->latestWorkType() === 'WFO';
        $isTelatWfo = $attendance->isTelatWfoCut();

        // Sudah WFO tepat waktu / sudah divalidasi → tidak perlu validasi lagi
        if ($isAlreadyWfo && !$isTelatWfo) {
            $updates = [];
            if ($attendance->work_type !== 'WFO') {
                $updates['work_type'] = 'WFO';
            }
            if ($attendance->is_validate) {
                $updates['is_telat_wfo'] = 0;
            }
            if (!empty($updates)) {
                $attendance->update($updates);
            }
            return back()->with('error', 'Absensi pada tanggal tersebut sudah WFO dan tidak telat.');
        }

        $earliest = $attendance->earliestCheckInTime();
        $hadAbsenBeforeTen = $earliest && $earliest->format('H:i:s') < '10:00:00';

        DB::transaction(function () use ($attendance, $attendanceDate, $hadAbsenBeforeTen, $earliest, $isAlreadyWfo) {
            $validatedCheckIn = $attendanceDate->copy()->setTime(10, 30, 0);

            // Sudah absen sebelum jam 10 → pertahankan check-in awal.
            // Belum → set check-in 10:30. Validasi admin selalu clear is_telat_wfo.
            $update = [
                'work_type' => 'WFO',
                'is_validate' => 1,
                'is_telat_wfo' => 0,
                'notes' => trim(($attendance->notes ? $attendance->notes . ' | ' : '') . 'Validasi WFO admin'),
            ];
            if ($hadAbsenBeforeTen && $earliest) {
                $update['check_in'] = $earliest;
            } else {
                $update['check_in'] = $validatedCheckIn;
            }

            $attendance->update($update);

            AttendanceLog::create([
                'attendance_id' => $attendance->id,
                'check_in_time' => Carbon::now('Asia/Jakarta'),
                'status' => 'WFO',
                'notes' => $isAlreadyWfo ? 'Validasi WFO admin (hapus telat > 10:30)' : 'Validasi WFO admin',
            ]);
        });

        if ($isAlreadyWfo && $isTelatWfo) {
            return back()->with('success', 'WFO telat berhasil divalidasi (flag telat dihapus, tidak potong 50%).');
        }

        return back()->with('success', $hadAbsenBeforeTen
            ? 'Absensi berhasil divalidasi menjadi WFO (check-in awal tetap, tidak telat).'
            : 'Absensi berhasil divalidasi menjadi WFO (check-in 10:30, tidak telat).');
    }

    private function buildRows(User $user, RefBulan $ref): array
    {
        $year = (int) $ref->year;
        $month = (int) $ref->month_code;
        $start = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta');
        $end = $start->copy()->endOfMonth();
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        $reference = ReferensiWfo::where('custid', $user->id)->first();
        $attendanceMap = Attendance::with('logs')
            ->where('user_id', $user->id)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->get()
            ->keyBy(fn ($attendance) => Carbon::parse($attendance->attendance_date)->format('Y-m-d'));

        $holidaySet = Holiday::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->flip();

        $rows = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dateStr = $cursor->format('Y-m-d');
            $isWeekend = $cursor->isWeekend();
            $isHoliday = isset($holidaySet[$dateStr]);
            $attendance = $attendanceMap[$dateStr] ?? null;
            $actualWorkType = $attendance ? $attendance->latestWorkType() : '-';
            $expectedWfo = $reference ? $this->isExpectedWfo($reference, $cursor) : false;
            $expectedLabel = $expectedWfo ? 'WFO' : '-';
            $isTelatWfo = $attendance ? $attendance->isTelatWfoCut() : false;

            // Bisa validasi jika: harusnya WFO, dan (bukan WFO ATAU sudah WFO tapi telat > 10:30)
            $needsValidate = $attendance && (
                $actualWorkType !== 'WFO' || $isTelatWfo
            );
            $canValidate = !$isWeekend
                && !$isHoliday
                && $cursor->lte($today)
                && $expectedWfo
                && $needsValidate;

            $rows[] = [
                'date' => $dateStr,
                'day_name' => $cursor->locale('id')->isoFormat('dddd'),
                'actual_work_type' => $actualWorkType,
                'expected_work_type' => $expectedLabel,
                'attendance_id' => $attendance?->id,
                'can_validate' => $canValidate,
                'is_telat_wfo' => $isTelatWfo,
                'is_holiday' => $isHoliday,
                'is_weekend' => $isWeekend,
            ];

            $cursor->addDay();
        }

        return $rows;
    }

    private function isExpectedWfo(ReferensiWfo $reference, Carbon $date): bool
    {
        if ($date->isWeekend()) {
            return false;
        }

        if ($reference->type === 'daily') {
            return true;
        }

        $days = $reference->hari_array;
        $dayName = strtolower($date->englishDayOfWeek); // monday...friday
        return in_array($dayName, $days, true);
    }
}
