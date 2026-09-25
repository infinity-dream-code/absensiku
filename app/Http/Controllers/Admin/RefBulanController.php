<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\RefBulan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefBulanController extends Controller
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

    /**
     * Generate / refresh ref_bulans data for the given year (default: current year).
     */
    public function refresh(Request $request)
    {
        $year = $request->input('year');
        if (!$year) {
            $year = Carbon::now('Asia/Jakarta')->year;
        }

        $year = (int) $year;

        // Get all holidays for that year as simple Y-m-d list
        $holidays = Holiday::whereYear('date', $year)
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();
        $holidaysSet = array_flip($holidays);

        for ($month = 1; $month <= 12; $month++) {
            $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta');
            $endOfMonth = $startOfMonth->copy()->endOfMonth();

            $workingDays = 0;
            $cursor = $startOfMonth->copy();

            while ($cursor->lte($endOfMonth)) {
                $dateStr = $cursor->format('Y-m-d');
                $dayOfWeek = $cursor->dayOfWeek; // 0 = Sunday, 6 = Saturday

                if ($dayOfWeek !== Carbon::SATURDAY && $dayOfWeek !== Carbon::SUNDAY) {
                    if (!isset($holidaysSet[$dateStr])) {
                        $workingDays++;
                    }
                }

                $cursor->addDay();
            }

            $monthName = $startOfMonth->locale('id')->isoFormat('MMMM');

            RefBulan::updateOrCreate(
                [
                    'year' => $year,
                    'month_code' => $month,
                ],
                [
                    'month_name' => $monthName,
                    'working_days' => $workingDays,
                ]
            );
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Referensi bulan untuk tahun ' . $year . ' berhasil diperbarui.');
    }
}

