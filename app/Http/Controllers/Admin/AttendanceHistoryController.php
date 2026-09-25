<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use App\Exports\AttendanceExport;
use App\Exports\MonthlyAttendanceSummaryExport;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceHistoryController extends Controller
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
        $query = Attendance::with(['user', 'logs'])
            ->orderBy('attendance_date', 'desc')
            ->orderBy('check_in', 'desc');

        $settings = \App\Models\Setting::getSettings();

        // Dropdown karyawan: hanya yang jenis/is absensi = 1
        $employees = User::where('role', 'user')
            ->where('jenis', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'nik', 'nip']);

        if ($request->filled('date_from')) {
            $query->whereDate('attendance_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('attendance_date', '<=', $request->date_to);
        }

        if ($request->filled('year') && $request->filled('month')) {
            $query->whereYear('attendance_date', $request->year)
                  ->whereMonth('attendance_date', $request->month);
        } elseif ($request->filled('year')) {
            $query->whereYear('attendance_date', $request->year);
        } elseif ($request->filled('month')) {
            $query->whereMonth('attendance_date', $request->month);
        }

        if ($request->filled('work_type') && $request->work_type !== 'all') {
            $query->where('work_type', $request->work_type);
        }

        $filterByEmployee = $request->filled('user_id');
        if ($filterByEmployee) {
            $query->where('user_id', (int) $request->user_id);
        }

        // Filter karyawan spesifik → 10 data per halaman
        if ($filterByEmployee) {
            $attendances = $query->paginate(10)->withQueryString();
            $paginator = $attendances;
            $paginateByDay = false;

            $datesOnPage = $attendances->getCollection()
                ->map(fn ($a) => Carbon::parse($a->attendance_date)->format('Y-m-d'))
                ->unique()
                ->values();
        } else {
            // Semua karyawan → 2 hari per halaman
            $allAttendances = $query->get();

            $groupedByDate = $allAttendances->groupBy(function ($attendance) {
                return Carbon::parse($attendance->attendance_date)->format('Y-m-d');
            });

            $dates = $groupedByDate->keys()->sortDesc()->values();
            $perPage = 2;
            $currentPage = max(1, (int) $request->get('page', 1));
            $datesForPage = $dates->slice(($currentPage - 1) * $perPage, $perPage)->values();

            $attendances = collect();
            foreach ($datesForPage as $date) {
                if ($groupedByDate->has($date)) {
                    $attendances = $attendances->merge($groupedByDate->get($date));
                }
            }

            $attendances = $attendances->sortByDesc(function ($attendance) {
                return Carbon::parse($attendance->attendance_date)->format('Y-m-d') . ' ' .
                    ($attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i:s') : '00:00:00');
            })->values();

            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $datesForPage,
                $dates->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            $datesOnPage = $datesForPage;
            $paginateByDay = true;
        }

        $holidaysByDate = [];
        if ($datesOnPage->isNotEmpty()) {
            $holidays = Holiday::whereIn('date', $datesOnPage->all())->get();
            foreach ($holidays as $holiday) {
                $holidaysByDate[$holiday->date->format('Y-m-d')] = $holiday;
            }
        }

        return view('admin.attendance-history.index', compact(
            'attendances',
            'paginator',
            'holidaysByDate',
            'settings',
            'employees',
            'paginateByDay'
        ));
    }

    public function export(Request $request)
    {
        $filters = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'year' => $request->year,
            'month' => $request->month,
            'work_type' => $request->work_type,
            'user_id' => $request->user_id,
            'search' => $request->search,
        ];

        $filename = 'Export_Absensi_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new AttendanceExport($filters), $filename);
    }

    public function exportMonthlySummary(Request $request)
    {
        $year = $request->filled('year') && $request->year !== '' ? (int) $request->year : null;
        $month = $request->filled('month') && $request->month !== '' ? (int) $request->month : null;

        if ($year !== null && ($year < 2020 || $year > 2100)) {
            return back()->withErrors(['year' => 'Tahun harus antara 2020 dan 2100']);
        }

        if ($month !== null && ($month < 1 || $month > 12)) {
            return back()->withErrors(['month' => 'Bulan harus antara 1 dan 12']);
        }

        if ($year && $month) {
            $monthName = Carbon::create($year, $month, 1)->locale('id')->isoFormat('MMMM_YYYY');
            $filename = 'Rekap_Absensi_' . $monthName . '.xlsx';
        } elseif ($year) {
            $filename = 'Rekap_Absensi_Tahun_' . $year . '.xlsx';
        } elseif ($month) {
            $monthName = Carbon::create(null, $month, 1)->locale('id')->isoFormat('MMMM');
            $filename = 'Rekap_Absensi_Bulan_' . $monthName . '.xlsx';
        } else {
            $filename = 'Rekap_Absensi_Semua.xlsx';
        }

        return Excel::download(new MonthlyAttendanceSummaryExport($year, $month), $filename);
    }

    public function getLogs(Request $request, $attendanceId)
    {
        $attendance = Attendance::with('user')->findOrFail($attendanceId);

        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $logs = AttendanceLog::where('attendance_id', $attendanceId)
            ->orderBy('check_in_time', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'attendance' => $attendance,
            'logs' => $logs
        ]);
    }
}
