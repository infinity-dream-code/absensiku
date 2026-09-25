<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\RefBulan;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendancePerformanceSummaryExport implements FromCollection, WithHeadings, WithMapping
{
    protected ?int $refBulanId;

    public function __construct(?int $refBulanId)
    {
        $this->refBulanId = $refBulanId;
    }

    public function collection()
    {
        $ref = $this->refBulanId
            ? RefBulan::find($this->refBulanId)
            : RefBulan::orderBy('year')->orderBy('month_code')->get()->last();

        if (!$ref) {
            return collect();
        }

        $year = $ref->year;
        $month = $ref->month_code;

        $settings = Setting::getSettings();
        $checkInEndTime = $settings->check_in_end ?: '10:00:00';

        $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta');
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $holidays = Holiday::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();
        $holidaysSet = array_flip($holidays);

        $workingDates = [];
        $cursor = $startOfMonth->copy();
        while ($cursor->lte($endOfMonth)) {
            $dateStr = $cursor->format('Y-m-d');
            $dayOfWeek = $cursor->dayOfWeek;

            if ($dayOfWeek !== Carbon::SATURDAY && $dayOfWeek !== Carbon::SUNDAY) {
                if (!isset($holidaysSet[$dateStr])) {
                    $workingDates[] = $dateStr;
                }
            }

            $cursor->addDay();
        }

        $denominator = $ref->working_days ?: count($workingDates);
        if ($denominator <= 0) {
            $denominator = 1;
        }

        // Hanya karyawan dengan jenis = 1 (wajib absen)
        $users = User::where('role', 'user')
            ->where('jenis', 1)
            ->orderBy('name')
            ->get();

        $attendances = Attendance::with('logs')
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->whereNotNull('check_in')
            ->get();

        $attendancesByUserDate = [];
        $telatWfoByUser = [];
        foreach ($attendances as $attendance) {
            $userId = $attendance->user_id;
            $dateStr = $attendance->attendance_date instanceof Carbon
                ? $attendance->attendance_date->format('Y-m-d')
                : Carbon::parse($attendance->attendance_date)->format('Y-m-d');

            $existing = $attendancesByUserDate[$userId][$dateStr] ?? null;
            if ($existing === null || ($attendance->check_in && $existing->check_in && $attendance->check_in < $existing->check_in)) {
                $attendancesByUserDate[$userId][$dateStr] = $attendance;
            }

            // Weekend/libur tidak ikut potong 50%
            if (in_array($dateStr, $workingDates, true) && $attendance->isTelatWfoCut()) {
                $telatWfoByUser[$userId] = true;
            }
        }

        $leaves = Leave::whereYear('leave_date', $year)
            ->whereMonth('leave_date', $month)
            ->get();

        $leavesByUserDate = [];
        foreach ($leaves as $leave) {
            $userId = $leave->user_id;
            $dateStr = $leave->leave_date instanceof Carbon
                ? $leave->leave_date->format('Y-m-d')
                : Carbon::parse($leave->leave_date)->format('Y-m-d');
            $leavesByUserDate[$userId][$dateStr] = $leave;
        }

        $rows = collect();

        foreach ($users as $user) {
            $userId = $user->id;
            $userAttendances = $attendancesByUserDate[$userId] ?? [];
            $userLeaves = $leavesByUserDate[$userId] ?? [];

            $alpha = 0;
            $late = 0;
            $presentOrLeaveDays = 0;

            foreach ($workingDates as $dateStr) {
                $attendance = $userAttendances[$dateStr] ?? null;
                $leave = $userLeaves[$dateStr] ?? null;

                if ($attendance) {
                    $presentOrLeaveDays++;

                    // Terlambat: lewat batas check-in (0.25) ATAU WFO > 10:30
                    if ($attendance->isLateStatus($checkInEndTime)) {
                        $late++;
                    }
                } elseif ($leave) {
                    $presentOrLeaveDays++;
                } else {
                    $alpha++;
                }
            }

            $workedDays = $presentOrLeaveDays;

            $alphaPercentLoss = 0.0;
            if ($alpha > 0) {
                $alphaPercentRaw = ($alpha / $denominator) * 100;
                $alphaPercentLoss = floor($alphaPercentRaw);
            }

            $latePercentLoss = 0.0;
            if ($late > 0) {
                $latePercentRaw = ($late / $denominator) * 100;
                $latePercentRounded = floor($latePercentRaw * 10) / 10;
                $latePercentLoss = $latePercentRounded * 0.25;
            }

            $totalLoss = $alphaPercentLoss + $latePercentLoss;
            if ($totalLoss > 100) {
                $totalLoss = 100;
            }
            $finalPercent = 100 - $totalLoss;
            $percent = round($finalPercent, 1);

            $isTelatWfo = !empty($telatWfoByUser[$userId]);
            $percentFinal = ($isTelatWfo && $percent > 50) ? 50.0 : $percent;

            $rows->push([
                'user' => $user,
                'worked_days' => $workedDays,
                'working_days' => $denominator,
                'alpha' => $alpha,
                'late' => $late,
                'percent' => $percent,
                'is_telat_wfo' => $isTelatWfo,
                'percent_final' => $percentFinal,
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'NIP',
            'Nama Karyawan',
            'Jumlah Hari Kerja',
            'Alpha',
            'Terlambat',
            'Persen',
            'Is Telat WFO',
            'Perhitungan Final',
        ];
    }

    public function map($row): array
    {
        return [
            $row['user']->nip ?: '-',
            $row['user']->name,
            $row['worked_days'] . '/' . $row['working_days'],
            $row['alpha'],
            $row['late'],
            $row['percent'],
            !empty($row['is_telat_wfo']) ? 'Ya' : 'Tidak',
            $row['percent_final'],
        ];
    }
}

