<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendancePerformanceSummaryExport;
use App\Http\Controllers\Controller;
use App\Models\RefBulan;
use App\Models\User;
use App\Services\KpiCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceSummaryController extends Controller
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
        $refMonths = RefBulan::orderBy('year')
            ->orderBy('month_code')
            ->get();

        $selectedRefId = $request->input('ref_bulan_id');
        if (!$selectedRefId && $refMonths->isNotEmpty()) {
            $selectedRefId = $refMonths->last()->id;
        }

        $selectedRef = $selectedRefId ? $refMonths->firstWhere('id', (int) $selectedRefId) : null;

        $summaryRows = collect();
        $workingDaysCount = 0;

        if ($selectedRef) {
            $year = $selectedRef->year;
            $month = $selectedRef->month_code;

            $users = User::where('role', 'user')
                ->where('jenis', 1)
                ->orderBy('name')
                ->get();

            foreach ($users as $user) {
                $perf = KpiCalculator::attendancePerformance($user->id, $month, $year);

                $summaryRows->push([
                    'user' => $user,
                    'worked_days' => $perf['worked_days'],
                    'working_days' => $perf['working_days'],
                    'alpha' => $perf['alpha'],
                    'late' => $perf['late'],
                    'percent' => $perf['percent'],
                    'is_telat_wfo' => $perf['is_telat_wfo'],
                    'percent_final' => $perf['percent_final'],
                ]);
            }

            $firstRow = $summaryRows->first();
            $workingDaysCount = (int) ($selectedRef->working_days ?: ($firstRow['working_days'] ?? 0));
        }

        return view('admin.attendance-summary.index', [
            'refMonths' => $refMonths,
            'selectedRef' => $selectedRef,
            'selectedRefId' => $selectedRefId,
            'rows' => $summaryRows,
            'workingDaysCount' => $workingDaysCount,
        ]);
    }

    public function export(Request $request)
    {
        $refBulanId = $request->input('ref_bulan_id');

        return Excel::download(new AttendancePerformanceSummaryExport($refBulanId), 'Summary_Absensi.xlsx');
    }
}

