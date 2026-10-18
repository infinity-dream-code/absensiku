<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LemburController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::check() && Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $now = Carbon::now('Asia/Jakarta');
        $year = (int) $request->input('year', $now->year);
        $selectedMonth = (int) $request->input('month', $now->copy()->startOfMonth()->subMonth()->month);
        $selectedMonth = max(1, min(12, $selectedMonth));

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $availableYears = Overtime::where('user_id', Auth::id())
            ->selectRaw('YEAR(overtime_date) as year')
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

        $rows = Overtime::where('user_id', Auth::id())
            ->whereYear('overtime_date', $year)
            ->orderBy('overtime_date')
            ->orderBy('start_time')
            ->get();

        $byMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $byMonth[$m] = collect();
        }
        foreach ($rows as $row) {
            $byMonth[(int) $row->overtime_date->month]->push($row);
        }

        $monthCounts = [];
        foreach ($byMonth as $m => $items) {
            $monthCounts[$m] = $items->count();
        }

        return view('lembur.index', compact(
            'year',
            'selectedMonth',
            'monthNames',
            'availableYears',
            'byMonth',
            'monthCounts'
        ));
    }

    public function store(Request $request)
    {
        $start = substr((string) $request->input('start_time'), 0, 5);
        $end = substr((string) $request->input('end_time'), 0, 5);
        $request->merge([
            'start_time' => $start,
            'end_time' => $end,
        ]);

        $validated = $request->validate([
            'overtime_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'notes' => 'nullable|string|max:500',
        ], [
            'end_time.after' => 'Jam selesai harus setelah jam mulai (dalam hari yang sama).',
        ]);

        $overtime = Overtime::create([
            'user_id' => Auth::id(),
            'overtime_date' => $validated['overtime_date'],
            'start_time' => $validated['start_time'] . ':00',
            'end_time' => $validated['end_time'] . ':00',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data lembur berhasil disimpan!',
            'overtime' => $overtime,
        ]);
    }

    public function destroy(Overtime $overtime)
    {
        if ((int) $overtime->user_id !== (int) Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus data ini.',
            ], 403);
        }

        $overtime->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data lembur berhasil dihapus!',
        ]);
    }
}
