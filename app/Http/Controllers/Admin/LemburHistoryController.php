<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OvertimeExport;
use App\Http\Controllers\Controller;
use App\Models\Overtime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class LemburHistoryController extends Controller
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
        $now = Carbon::now('Asia/Jakarta');
        $year = (int) $request->input('year', $now->year);
        $month = $request->filled('month') ? (int) $request->month : null;

        $employees = User::where('role', 'user')
            ->where('jenis', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'nip', 'nik']);

        $query = Overtime::with('user')
            ->whereYear('overtime_date', $year)
            ->orderByDesc('overtime_date')
            ->orderByDesc('start_time');

        if ($month) {
            $query->whereMonth('overtime_date', $month);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $overtimes = $query->paginate(20)->withQueryString();

        $availableYears = Overtime::selectRaw('YEAR(overtime_date) as year')
            ->distinct()
            ->pluck('year')
            ->push($now->year)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        if (!in_array($year, $availableYears, true)) {
            $availableYears[] = $year;
            rsort($availableYears);
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return view('admin.lembur-history.index', compact(
            'overtimes',
            'employees',
            'year',
            'month',
            'availableYears',
            'monthNames'
        ));
    }

    public function export(Request $request)
    {
        $filters = [
            'year' => $request->input('year'),
            'month' => $request->input('month'),
            'user_id' => $request->input('user_id'),
            'search' => $request->input('search'),
        ];

        $filename = 'Export_Lembur_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new OvertimeExport($filters), $filename);
    }
}
