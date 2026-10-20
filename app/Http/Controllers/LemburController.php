<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use App\Models\Setting;
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

        $settings = Setting::getSettings();

        return view('lembur.index', compact(
            'year',
            'selectedMonth',
            'monthNames',
            'availableYears',
            'byMonth',
            'monthCounts',
            'settings'
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
            'work_type' => 'required|in:WFO,WFH',
            'schedule_type' => 'required|in:piket,additional',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'notes' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ], [
            'end_time.after' => 'Jam selesai harus setelah jam mulai (dalam hari yang sama).',
        ]);

        $latitude = $request->filled('latitude') ? (float) $validated['latitude'] : null;
        $longitude = $request->filled('longitude') ? (float) $validated['longitude'] : null;
        $locationValid = null;

        if ($validated['work_type'] === 'WFO') {
            if ($latitude === null || $longitude === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lembur WFO wajib mengambil lokasi. Izinkan akses lokasi lalu coba lagi.',
                ], 422);
            }

            $settings = Setting::getSettings();
            if (!$settings->latitude || !$settings->longitude || !$settings->radius) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi kantor belum dikonfigurasi. Hubungi admin.',
                ], 422);
            }

            $distanceKm = $this->calculateDistance(
                (float) $settings->latitude,
                (float) $settings->longitude,
                $latitude,
                $longitude
            );
            $radiusKm = ((float) $settings->radius) / 1000;

            if ($distanceKm > $radiusKm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lembur WFO hanya bisa diajukan di dalam area kantor.',
                ], 422);
            }

            $locationValid = true;
        }

        $overtime = Overtime::create([
            'user_id' => Auth::id(),
            'overtime_date' => $validated['overtime_date'],
            'work_type' => $validated['work_type'],
            'schedule_type' => $validated['schedule_type'],
            'start_time' => $validated['start_time'] . ':00',
            'end_time' => $validated['end_time'] . ':00',
            'notes' => $validated['notes'] ?? null,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'location_valid' => $locationValid,
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

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
