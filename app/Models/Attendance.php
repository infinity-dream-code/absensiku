<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_date',
        'work_type',
        'notes',
        'image',
        'check_in',
        'check_out',
        'latitude',
        'longitude',
        'location_valid',
        'location_name',
        'is_validate',
        'is_telat_wfo',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'is_validate' => 'boolean',
        'is_telat_wfo' => 'boolean',
    ];
    public function punctualityStatus(?string $checkInEndTime = null): string
    {
        if (!$this->check_in) {
            return '-';
        }

        $date = $this->attendance_date instanceof Carbon
            ? $this->attendance_date->copy()
            : Carbon::parse($this->attendance_date);

        if ($date->isWeekend()) {
            return 'Tepat Waktu';
        }

        $checkInEndTime = $checkInEndTime
            ?: (Setting::getSettings()->check_in_end ?: '09:00:00');

        $checkInEnd = Carbon::parse(
            $date->format('Y-m-d') . ' ' . $checkInEndTime,
            'Asia/Jakarta'
        );
        $checkInTime = Carbon::parse($this->check_in, 'Asia/Jakarta');

        return $checkInTime->gt($checkInEnd) ? 'Terlambat' : 'Tepat Waktu';
    }

    public function isLate(?string $checkInEndTime = null): bool
    {
        return $this->punctualityStatus($checkInEndTime) === 'Terlambat';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    /**
     * Log absensi paling akhir (berdasarkan check_in_time).
     */
    public function latestLog(): ?AttendanceLog
    {
        if ($this->relationLoaded('logs')) {
            return $this->logs->sortByDesc(fn ($log) => $log->check_in_time)->first();
        }

        return $this->logs()->orderByDesc('check_in_time')->first();
    }

    /**
     * Jenis absensi terakhir (dari log paling akhir), fallback ke work_type record utama.
     */
    public function latestWorkType(): string
    {
        $latestLog = $this->latestLog();

        if ($latestLog && !empty($latestLog->status)) {
            return $latestLog->status;
        }

        return $this->work_type ?: '-';
    }

    /**
     * Catatan paling relevan: dari log terbaru yang ada isinya
     * (kalau absen terakhir kosong, pakai catatan absen sebelumnya).
     */
    public function latestNotes(): ?string
    {
        $logs = $this->relationLoaded('logs')
            ? $this->logs->sortByDesc(fn ($log) => $log->check_in_time)
            : $this->logs()->orderByDesc('check_in_time')->get();

        foreach ($logs as $log) {
            $notes = $log->notes;
            if ($notes !== null && trim((string) $notes) !== '') {
                return trim((string) $notes);
            }
        }

        $notes = $this->notes;
        return ($notes !== null && trim((string) $notes) !== '') ? trim((string) $notes) : null;
    }

    /**
     * Jam check-in paling awal hari itu dari absen nyata
     * (abaikan log "Validasi WFO admin" supaya jam 10:30 validasi tidak menimpa absen pagi).
     */
    public function earliestCheckInTime(): ?Carbon
    {
        $logs = $this->relationLoaded('logs') ? $this->logs : $this->logs()->get();

        $realLogTimes = collect();
        foreach ($logs as $log) {
            if (!$log->check_in_time) {
                continue;
            }
            if (trim((string) ($log->notes ?? '')) === 'Validasi WFO admin') {
                continue;
            }
            $realLogTimes->push(Carbon::parse($log->check_in_time, 'Asia/Jakarta'));
        }

        if ($realLogTimes->isNotEmpty()) {
            return $realLogTimes->sortBy(fn (Carbon $time) => $time->timestamp)->first();
        }

        if ($this->check_in) {
            return Carbon::parse($this->check_in, 'Asia/Jakarta');
        }

        $anyLog = $logs->filter(fn ($log) => $log->check_in_time)
            ->sortBy(fn ($log) => Carbon::parse($log->check_in_time)->timestamp)
            ->first();

        return $anyLog
            ? Carbon::parse($anyLog->check_in_time, 'Asia/Jakarta')
            : null;
    }

    /**
     * Sudah absen apa pun sebelum jam 10:00.
     */
    public function hasCheckInBeforeTen(): bool
    {
        $earliest = $this->earliestCheckInTime();
        return $earliest !== null && $earliest->format('H:i:s') < '10:00:00';
    }

    /**
     * Telat WFO potong 50% (is_telat_wfo):
     * Jenis terakhir WFO dan jam absen WFO > 10:30:00.
     * Contoh: WFA jam 8 + WFO 10:45 → telat 50%.
     * Contoh: WFA jam 8 + WFO 10:20 → tidak.
     */
    public static function resolveIsTelatWfo(bool $isWfo, ?Carbon $earliestCheckIn = null, ?Carbon $wfoCheckIn = null): bool
    {
        if (!$isWfo) {
            return false;
        }

        $time = $wfoCheckIn ?: $earliestCheckIn;
        if (!$time) {
            return false;
        }

        return $time->format('H:i:s') > '10:30:00';
    }

    /**
     * Apakah absen ini kena potong 50% final (WFO > 10:30, belum divalidasi admin).
     */
    public function isTelatWfoCut(): bool
    {
        $this->loadMissing('logs');

        if ($this->is_validate) {
            return false;
        }

        return self::resolveIsTelatWfo(
            $this->latestWorkType() === 'WFO',
            $this->earliestCheckInTime(),
            $this->latestWfoCheckInTime()
        );
    }

    /**
     * Telat hitungan 0.25:
     * Absen paling awal > batas check-in (setting check_in_end, biasanya 10:00).
     * Kalau sudah absen sebelum batas (mis. WFA jam 8 lalu WFO jam 10:xx) → tidak kena 0.25.
     */
    public function isLate025(string $checkInEndTime = '10:00:00'): bool
    {
        $earliest = $this->earliestCheckInTime();
        if (!$earliest) {
            return false;
        }

        $dateStr = $this->attendance_date instanceof Carbon
            ? $this->attendance_date->format('Y-m-d')
            : Carbon::parse($this->attendance_date)->format('Y-m-d');
        $checkInEnd = Carbon::parse($dateStr . ' ' . ($checkInEndTime ?: '10:00:00'), 'Asia/Jakarta');

        return $earliest->gt($checkInEnd);
    }

    /**
     * Status tampilan / hitung kolom Terlambat:
     * - Absen awal lewat batas check-in setting → terlambat (0.25)
     * - WFA/WFH pagi lalu WFO > 10:30 → terlambat juga + potong 50% final
     */
    public function isLateStatus(string $checkInEndTime = '10:00:00'): bool
    {
        return $this->isLate025($checkInEndTime) || $this->isTelatWfoCut();
    }

    public function statusLabel(string $checkInEndTime = '10:00:00'): ?string
    {
        $this->loadMissing('logs');

        if (!$this->earliestCheckInTime() && $this->latestWorkType() !== 'WFO') {
            return null;
        }

        return $this->isLateStatus($checkInEndTime) ? 'Terlambat' : 'Tepat Waktu';
    }

    /**
     * Jam absen WFO terakhir (abaikan log Validasi WFO admin).
     */
    public function latestWfoCheckInTime(): ?Carbon
    {
        $logs = $this->relationLoaded('logs') ? $this->logs : $this->logs()->get();

        $wfoTimes = collect();
        foreach ($logs as $log) {
            if (!$log->check_in_time) {
                continue;
            }
            if (strtoupper((string) $log->status) !== 'WFO') {
                continue;
            }
            if (trim((string) ($log->notes ?? '')) === 'Validasi WFO admin') {
                continue;
            }
            $wfoTimes->push(Carbon::parse($log->check_in_time, 'Asia/Jakarta'));
        }

        if ($wfoTimes->isNotEmpty()) {
            return $wfoTimes->sortByDesc(fn (Carbon $time) => $time->timestamp)->first();
        }

        if ($this->latestWorkType() === 'WFO' && $this->check_in) {
            return Carbon::parse($this->check_in, 'Asia/Jakarta');
        }

        return null;
    }

    /**
     * Sinkronkan work_type (dan is_telat_wfo) di record utama agar sama dengan absen terakhir.
     */
    public function syncFromLatestLog(bool $save = true): bool
    {
        $this->loadMissing('logs');

        $latest = $this->latestWorkType();
        if ($latest === '-') {
            return false;
        }

        $earliest = $this->earliestCheckInTime();

        // Validasi admin meng-clear potongan; selain itu telat jika jam WFO nyata > 10:30
        if ($this->is_validate) {
            $isTelat = false;
        } else {
            $wfoTime = $this->latestWfoCheckInTime();
            $isTelat = self::resolveIsTelatWfo($latest === 'WFO', $earliest, $wfoTime);
        }

        $changed = $this->work_type !== $latest || (bool) $this->is_telat_wfo !== $isTelat;
        if (!$changed) {
            // tetap boleh perbaiki check_in yang tertimpa 10:30
            if ($earliest && $this->check_in) {
                $current = Carbon::parse($this->check_in, 'Asia/Jakarta');
                if ($current->format('H:i:s') === '10:30:00' && $earliest->format('H:i:s') !== '10:30:00') {
                    $this->check_in = $earliest;
                    if ($save) {
                        $this->saveQuietly();
                    }
                    return true;
                }
            }
            return false;
        }

        $this->work_type = $latest;
        $this->is_telat_wfo = $isTelat;

        // Jika validasi admin sempat timpa check_in ke 10:30, kembalikan jam absen nyata
        if ($earliest && $this->check_in) {
            $current = Carbon::parse($this->check_in, 'Asia/Jakarta');
            if ($current->format('H:i:s') === '10:30:00' && $earliest->format('H:i:s') !== '10:30:00') {
                $this->check_in = $earliest;
            }
        }

        if ($save) {
            $this->saveQuietly();
        }

        return true;
    }
}
