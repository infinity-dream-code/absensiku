<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Holiday;
use App\Models\Setting;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class MonthlyAttendanceSummaryExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $year;
    protected $month;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Hanya karyawan wajib absen (jenis = 1)
        $users = User::where('role', 'user')
            ->where('jenis', 1)
            ->orderBy('name')
            ->get();

        // Get settings for check-in end time
        $settings = Setting::getSettings();
        $checkInEndTime = $settings->check_in_end ?: '10:00:00';

        // Get all attendances with filters (optimize: don't load user relation)
        $query = Attendance::with('logs')->whereNotNull('check_in');

        if ($this->year !== null) {
            $query->whereYear('attendance_date', $this->year);
        }

        if ($this->month !== null) {
            $query->whereMonth('attendance_date', $this->month);
        }

        $attendances = $query->get();

        // Get all leaves with filters (optimize: don't load user relation)
        $leaveQuery = Leave::query();
        if ($this->year !== null) {
            $leaveQuery->whereYear('leave_date', $this->year);
        }
        if ($this->month !== null) {
            $leaveQuery->whereMonth('leave_date', $this->month);
        }
        $leaves = $leaveQuery->get();

        // Get holidays for the period
        $holidayQuery = Holiday::query();
        if ($this->year !== null) {
            $holidayQuery->where('year', $this->year);
        }
        if ($this->month !== null) {
            $holidayQuery->whereMonth('date', $this->month);
        }
        $holidays = $holidayQuery->pluck('date')->map(function($date) {
            return Carbon::parse($date)->format('Y-m-d');
        })->toArray();

        // Determine date range
        $startDate = null;
        $endDate = null;
        $today = Carbon::today('Asia/Jakarta');
        $yesterday = $today->copy()->subDay(); // Alpha hanya dihitung sampai kemarin
        
        // Program launch date (1 Januari 2026)
        $launchDate = Carbon::create(2026, 1, 1);
        
        if ($this->year !== null && $this->month !== null) {
            $requestedStartDate = Carbon::create($this->year, $this->month, 1);
            $requestedEndDate = $requestedStartDate->copy()->endOfMonth();
            
            // Start date should be the later of launch date or requested start date
            $startDate = $requestedStartDate->lt($launchDate) ? $launchDate->copy() : $requestedStartDate;
            
            // End date should be the earlier of yesterday or end of month
            // Alpha hanya dihitung sampai kemarin, karena hari ini masih bisa absen
            $endDate = $yesterday->lt($requestedEndDate) ? $yesterday->copy() : $requestedEndDate;
            
            // If end date is before start date, no calculation needed
            if ($endDate->lt($startDate)) {
                $endDate = null;
            }
        } elseif ($this->year !== null) {
            $requestedStartDate = Carbon::create($this->year, 1, 1);
            $requestedEndDate = $requestedStartDate->copy()->endOfYear();
            
            // Start date should be the later of launch date or requested start date
            $startDate = $requestedStartDate->lt($launchDate) ? $launchDate->copy() : $requestedStartDate;
            
            // End date should be the earlier of yesterday or end of year
            // Alpha hanya dihitung sampai kemarin, karena hari ini masih bisa absen
            $endDate = $yesterday->lt($requestedEndDate) ? $yesterday->copy() : $requestedEndDate;
            
            // If end date is before start date, no calculation needed
            if ($endDate->lt($startDate)) {
                $endDate = null;
            }
        }

        // Pre-group attendances by user_id + date; simpan yang CHECK-IN PALING AWAL per user per hari
        $attendancesByUser = [];
        foreach ($attendances as $attendance) {
            $userId = $attendance->user_id;
            if (!isset($attendancesByUser[$userId])) {
                $attendancesByUser[$userId] = [];
            }
            $dateStr = $attendance->attendance_date instanceof Carbon
                ? $attendance->attendance_date->format('Y-m-d')
                : Carbon::parse($attendance->attendance_date)->format('Y-m-d');
            $existing = $attendancesByUser[$userId][$dateStr] ?? null;
            if ($existing === null || ($attendance->check_in && $existing->check_in && $attendance->check_in < $existing->check_in)) {
                $attendancesByUser[$userId][$dateStr] = $attendance;
            }
        }

        // Pre-group leaves by user_id for faster lookup
        $leavesByUser = [];
        $leavesCountByUser = [];
        foreach ($leaves as $leave) {
            $userId = $leave->user_id;
            if (!isset($leavesByUser[$userId])) {
                $leavesByUser[$userId] = [];
                $leavesCountByUser[$userId] = ['cuti' => 0, 'izin' => 0, 'sakit' => 0];
            }
            // leave_date is already a date, just format it
            $dateStr = $leave->leave_date instanceof Carbon
                ? $leave->leave_date->format('Y-m-d')
                : Carbon::parse($leave->leave_date)->format('Y-m-d');
            $leavesByUser[$userId][$dateStr] = true;
            $leavesCountByUser[$userId][$leave->leave_type]++;
        }

        // Pre-calculate working days (exclude weekend) for faster alpha calculation
        $workingDays = [];
        if ($startDate && $endDate) {
            $holidaysSet = array_flip($holidays); // Convert to hash map for O(1) lookup
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeek; // 0 = Sunday, 6 = Saturday
                
                // Skip weekend (Saturday = 6, Sunday = 0)
                if ($dayOfWeek != 6 && $dayOfWeek != 0) {
                    // Skip holidays
                    if (!isset($holidaysSet[$dateStr])) {
                        $workingDays[] = $dateStr;
                    }
                }
                $currentDate->addDay();
            }
        }

        // Process data per user
        $summary = collect();

        foreach ($users as $user) {
            $userId = $user->id;
            $userAttendances = $attendancesByUser[$userId] ?? [];
            $userLeaves = $leavesByUser[$userId] ?? [];
            $userLeavesCount = $leavesCountByUser[$userId] ?? ['cuti' => 0, 'izin' => 0, 'sakit' => 0];

            $tepatWaktu = 0;
            $terlambat = 0;
            $wfo = 0;
            $wfh = 0;
            $wfa = 0;
            $cuti = $userLeavesCount['cuti'];
            $izin = $userLeavesCount['izin'];
            $sakit = $userLeavesCount['sakit'];
            $alpha = 0;

            // Count attendances
            foreach ($userAttendances as $dateStr => $attendance) {
                // Count by jenis absensi terakhir (log paling akhir)
                $workType = $attendance->latestWorkType();
                if ($workType === 'WFO') {
                    $wfo++;
                } elseif ($workType === 'WFH') {
                    $wfh++;
                } elseif ($workType === 'WFA') {
                    $wfa++;
                }

                // Count status: terlambat 0.25 (batas setting) atau telat WFO 50%
                if ($attendance->earliestCheckInTime() || $attendance->latestWorkType() === 'WFO') {
                    if ($attendance->isLateStatus($checkInEndTime)) {
                        $terlambat++;
                    } else {
                        $tepatWaktu++;
                    }
                }
            }

            // Calculate alpha using pre-calculated working days
            if (!empty($workingDays)) {
                foreach ($workingDays as $dateStr) {
                    // If no attendance and no leave, count as alpha
                    if (!isset($userAttendances[$dateStr]) && !isset($userLeaves[$dateStr])) {
                        $alpha++;
                    }
                }
            }

            $summary->push([
                'user' => $user,
                'tepat_waktu' => $tepatWaktu,
                'terlambat' => $terlambat,
                'wfo' => $wfo,
                'wfh' => $wfh,
                'wfa' => $wfa,
                'cuti' => $cuti,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpha' => $alpha,
            ]);
        }

        return $summary;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'NIK',
            'Nama',
            'Tepat Waktu',
            'Terlambat',
            'WFO',
            'WFH',
            'WFA',
            'Cuti',
            'Izin',
            'Sakit',
            'Alpha'
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row['user']->nik,
            $row['user']->name,
            $row['tepat_waktu'],
            $row['terlambat'],
            $row['wfo'],
            $row['wfh'],
            $row['wfa'],
            $row['cuti'],
            $row['izin'],
            $row['sakit'],
            $row['alpha'],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        if ($this->year !== null && $this->month !== null) {
            $monthName = Carbon::create($this->year, $this->month, 1)->locale('id')->isoFormat('MMMM YYYY');
            return 'Rekap Absensi ' . $monthName;
        } elseif ($this->year !== null) {
            return 'Rekap Absensi Tahun ' . $this->year;
        } elseif ($this->month !== null) {
            $monthName = Carbon::create(null, $this->month, 1)->locale('id')->isoFormat('MMMM');
            return 'Rekap Absensi Bulan ' . $monthName;
        } else {
            return 'Rekap Absensi Semua';
        }
    }
}
