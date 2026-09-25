<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\RefBulan;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class KpiCalculator
{
    /**
     * Bobot per role (urutan selaras string fungsi di seeder).
     *
     * @var array<string, array<int, int>>
     */
    public const WEIGHTS = [
        'Developer' => [25, 20, 15, 10, 20, 10],
        'Finance' => [20, 20, 15, 15, 20, 10],
        'HR & Admin' => [20, 20, 15, 15, 20, 10],
        'Support & Implementasi' => [10, 15, 15, 15, 15, 20, 10],
    ];

    /**
     * @return array<int, array{nama:string,bobot:int}>
     */
    public static function indicatorsForRole(Role $role): array
    {
        if (!empty($role->kpi_indikator)) {
            $rows = [];
            foreach ($role->kpi_indikator as $item) {
                $nama = trim((string) ($item['nama'] ?? ''));
                if ($nama === '') {
                    continue;
                }
                $rows[] = [
                    'nama' => $nama,
                    'bobot' => (int) ($item['bobot'] ?? 0),
                ];
            }

            if (!empty($rows)) {
                return $rows;
            }
        }

        $names = $role->indikatorList();
        $weights = self::WEIGHTS[$role->role] ?? [];

        $rows = [];
        foreach ($names as $i => $name) {
            $rows[] = [
                'nama' => $name,
                'bobot' => (int) ($weights[$i] ?? 0),
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array{nama:string,bobot:int,is_individu?:bool,id?:int}>
     */
    public static function indicatorsForUser(User $user, Role $role): array
    {
        $overrides = $user->kpi_bobot_individu ?? [];

        $rows = array_map(function ($row) use ($overrides) {
            $row['is_individu'] = false;
            if (isset($overrides[$row['nama']])) {
                $row['bobot'] = (int) $overrides[$row['nama']];
            }

            return $row;
        }, self::indicatorsForRole($role));

        foreach ($user->kpi_indikator_individu ?? [] as $i => $custom) {
            $rows[] = [
                'nama' => (string) ($custom['nama'] ?? ''),
                'bobot' => (int) ($custom['bobot'] ?? 0),
                'is_individu' => true,
                'index' => $i,
            ];
        }

        return $rows;
    }

    /**
     * @param array<int, array{nama:string,bobot:int,is_individu?:bool,index?:int}> $indicators
     * @param array<int, int|string|null> $bobotByIndex
     * @return array<int, array{nama:string,bobot:int,is_individu?:bool,index?:int}>
     */
    public static function applyBobotOverrides(array $indicators, array $bobotByIndex): array
    {
        foreach ($indicators as $i => $indicator) {
            if (array_key_exists($i, $bobotByIndex) && $bobotByIndex[$i] !== null && $bobotByIndex[$i] !== '') {
                $indicators[$i]['bobot'] = max(0, (int) $bobotByIndex[$i]);
            }
        }

        return $indicators;
    }

    /**
     * @param array<int, array{nama:string,bobot:int,is_individu?:bool,index?:int}> $indicators
     * @param array<int, int|string|null> $bobotByIndex
     */
    public static function persistBobotForUser(User $user, array $indicators, array $bobotByIndex): void
    {
        $roleOverrides = $user->kpi_bobot_individu ?? [];
        $individuList = $user->kpi_indikator_individu ?? [];

        foreach ($indicators as $i => $indicator) {
            if (!array_key_exists($i, $bobotByIndex)) {
                continue;
            }

            $bobot = max(0, (int) ($bobotByIndex[$i] ?? 0));

            if (!empty($indicator['is_individu'])) {
                $idx = $indicator['index'] ?? null;
                if ($idx !== null && isset($individuList[$idx])) {
                    if ($bobot > 0) {
                        $individuList[$idx]['bobot'] = $bobot;
                    } else {
                        unset($individuList[$idx]['bobot']);
                    }
                }
            } else {
                $roleOverrides[$indicator['nama']] = $bobot;
            }
        }

        $user->update([
            'kpi_bobot_individu' => $roleOverrides,
            'kpi_indikator_individu' => array_values($individuList),
        ]);
    }

    public static function parseSkor(mixed $value): float
    {
        if (is_string($value)) {
            $value = str_replace(',', '.', trim($value));
        }

        return round((float) $value, 1);
    }

    public static function formatSkor(mixed $value): string
    {
        $skor = self::parseSkor($value);
        if ($skor <= 0) {
            return '';
        }

        return rtrim(rtrim(number_format($skor, 1, ',', ''), '0'), ',');
    }

    public static function nilaiAkhir(int $bobot, float $skor): float
    {
        return round(($bobot * $skor) / 10, 1);
    }

    /**
     * @param array<int, array{nama:string,bobot:int}> $indicators
     * @param array<int, int|float|string> $scoresByIndex
     * @return array{details: array<int, array{nama_indikator:string,bobot:int,skor:float,nilai_akhir:float}>, skor_akhir: float, kategori: string}
     */
    public static function calculateFromIndicators(array $indicators, array $scoresByIndex): array
    {
        $details = [];
        $skorAkhir = 0.0;

        foreach ($indicators as $i => $indicator) {
            $skor = self::parseSkor($scoresByIndex[$i] ?? 0);
            $nilai = self::nilaiAkhir((int) $indicator['bobot'], $skor);
            $details[] = [
                'nama_indikator' => $indicator['nama'],
                'bobot' => $indicator['bobot'],
                'skor' => $skor,
                'nilai_akhir' => $nilai,
            ];
            $skorAkhir += $nilai;
        }

        $skorAkhir = round($skorAkhir, 1);

        return [
            'details' => $details,
            'skor_akhir' => $skorAkhir,
            'kategori' => self::kategori($skorAkhir),
        ];
    }

    /**
     * @param array<int, array{bobot:int,skor:int}> $rows
     * @return array{details: array<int, array{nama_indikator:string,bobot:int,skor:int,nilai_akhir:float}>, skor_akhir: float, kategori: string}
     */
    public static function calculate(Role $role, array $scoresByIndex): array
    {
        return self::calculateFromIndicators(self::indicatorsForRole($role), $scoresByIndex);
    }

    public static function kategori(float $skorAkhir): string
    {
        if ($skorAkhir >= 90) {
            return 'Sangat Baik';
        }
        if ($skorAkhir >= 80) {
            return 'Baik';
        }
        if ($skorAkhir >= 70) {
            return 'Cukup';
        }
        if ($skorAkhir >= 60) {
            return 'Kurang';
        }

        return 'Tidak Memenuhi';
    }

    public static function kategoriTindakLanjut(string $kategori): string
    {
        return match ($kategori) {
            'Sangat Baik' => 'Bonus maksimal, kandidat promosi',
            'Baik' => 'Bonus normal',
            'Cukup' => 'Coaching & monitoring',
            'Kurang' => 'Surat pembinaan SP1',
            default => 'Evaluasi kontrak',
        };
    }

    public static function monthName(int $month): string
    {
        $names = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $names[$month] ?? (string) $month;
    }

    /**
     * Index indikator bernama "Absensi" (case-insensitive), atau null.
     *
     * @param array<int, array{nama:string,bobot:int}> $indicators
     */
    public static function absensiIndex(array $indicators): ?int
    {
        foreach ($indicators as $i => $indicator) {
            if (strcasecmp(trim((string) ($indicator['nama'] ?? '')), 'Absensi') === 0) {
                return (int) $i;
            }
        }

        return null;
    }

    /**
     * Rekap kinerja absensi per bulan (sama rumus Summary Absensi).
     *
     * @return array{
     *     percent: float,
     *     percent_final: float,
     *     alpha: int,
     *     late: int,
     *     worked_days: int,
     *     working_days: int,
     *     is_telat_wfo: bool
     * }
     */
    public static function attendancePerformance(int $userId, int $bulan, int $tahun): array
    {
        $settings = Setting::getSettings();
        $checkInEndTime = $settings->check_in_end ?: '10:00:00';

        $startOfMonth = Carbon::create($tahun, $bulan, 1, 0, 0, 0, 'Asia/Jakarta');
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $holidays = Holiday::whereYear('date', $tahun)
            ->whereMonth('date', $bulan)
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();
        $holidaysSet = array_flip($holidays);

        $workingDates = [];
        $cursor = $startOfMonth->copy();
        while ($cursor->lte($endOfMonth)) {
            $dateStr = $cursor->format('Y-m-d');
            if ($cursor->dayOfWeek !== Carbon::SATURDAY && $cursor->dayOfWeek !== Carbon::SUNDAY) {
                if (!isset($holidaysSet[$dateStr])) {
                    $workingDates[] = $dateStr;
                }
            }
            $cursor->addDay();
        }

        $ref = RefBulan::where('year', $tahun)->where('month_code', $bulan)->first();
        $denominator = $ref && $ref->working_days
            ? (int) $ref->working_days
            : count($workingDates);
        if ($denominator <= 0) {
            $denominator = 1;
        }

        $attendances = Attendance::with('logs')
            ->where('user_id', $userId)
            ->whereYear('attendance_date', $tahun)
            ->whereMonth('attendance_date', $bulan)
            ->whereNotNull('check_in')
            ->get();

        $byDate = [];
        $isTelatWfo = false;
        foreach ($attendances as $attendance) {
            $dateStr = $attendance->attendance_date instanceof Carbon
                ? $attendance->attendance_date->format('Y-m-d')
                : Carbon::parse($attendance->attendance_date)->format('Y-m-d');

            $existing = $byDate[$dateStr] ?? null;
            if ($existing === null || ($attendance->check_in && $existing->check_in && $attendance->check_in < $existing->check_in)) {
                $byDate[$dateStr] = $attendance;
            }

            if (in_array($dateStr, $workingDates, true) && $attendance->isTelatWfoCut()) {
                $isTelatWfo = true;
            }
        }

        $leaves = Leave::where('user_id', $userId)
            ->whereYear('leave_date', $tahun)
            ->whereMonth('leave_date', $bulan)
            ->get();

        $leaveDates = [];
        foreach ($leaves as $leave) {
            $dateStr = $leave->leave_date instanceof Carbon
                ? $leave->leave_date->format('Y-m-d')
                : Carbon::parse($leave->leave_date)->format('Y-m-d');
            $leaveDates[$dateStr] = true;
        }

        $alpha = 0;
        $late = 0;
        $workedDays = 0;
        foreach ($workingDates as $dateStr) {
            $attendance = $byDate[$dateStr] ?? null;
            if ($attendance) {
                $workedDays++;
                if ($attendance->isLateStatus($checkInEndTime)) {
                    $late++;
                }
            } elseif (!empty($leaveDates[$dateStr])) {
                $workedDays++;
            } else {
                $alpha++;
            }
        }

        $alphaPercentLoss = 0.0;
        if ($alpha > 0) {
            $alphaPercentLoss = floor(($alpha / $denominator) * 100);
        }

        $latePercentLoss = 0.0;
        if ($late > 0) {
            $latePercentRounded = floor((($late / $denominator) * 100) * 10) / 10;
            $latePercentLoss = $latePercentRounded * 0.25;
        }

        $totalLoss = min(100, $alphaPercentLoss + $latePercentLoss);
        $percent = round(100 - $totalLoss, 1);
        $percentFinal = ($isTelatWfo && $percent > 50) ? 50.0 : $percent;

        return [
            'percent' => $percent,
            'percent_final' => $percentFinal,
            'alpha' => $alpha,
            'late' => $late,
            'worked_days' => $workedDays,
            'working_days' => $denominator,
            'is_telat_wfo' => $isTelatWfo,
        ];
    }

    /**
     * Skor indikator Absensi KPI = Perhitungan Final (%) / 10 (skala 1–10).
     *
     * @return array{
     *     percent: float,
     *     percent_final: float,
     *     skor: float,
     *     alpha: int,
     *     late: int,
     *     worked_days: int,
     *     working_days: int,
     *     is_telat_wfo: bool
     * }
     */
    public static function attendanceScore(int $userId, int $bulan, int $tahun): array
    {
        $performance = self::attendancePerformance($userId, $bulan, $tahun);

        // 100% → skor 10 (full), 75% → 7,5, dst.
        $skor = round($performance['percent_final'] / 10, 1);
        $skor = max(1, min(10, $skor));

        return array_merge($performance, ['skor' => $skor]);
    }
}
