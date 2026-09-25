<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Setting;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Satu baris per user per hari: pakai CHECK-IN PALING AWAL (status & jam masuk), check_out paling akhir.
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Attendance::with('user')->orderBy('attendance_date', 'desc')->orderBy('check_in', 'asc');

        // Filter by date
        if (isset($this->filters['date_from']) && $this->filters['date_from']) {
            $query->whereDate('attendance_date', '>=', $this->filters['date_from']);
        }
        if (isset($this->filters['date_to']) && $this->filters['date_to']) {
            $query->whereDate('attendance_date', '<=', $this->filters['date_to']);
        }

        // Filter by year and month
        if (isset($this->filters['year']) && isset($this->filters['month'])) {
            $query->whereYear('attendance_date', $this->filters['year'])
                ->whereMonth('attendance_date', $this->filters['month']);
        }

        // Filter by work type
        if (isset($this->filters['work_type']) && $this->filters['work_type'] !== 'all') {
            $query->where('work_type', $this->filters['work_type']);
        }

        // Filter karyawan (dropdown)
        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', (int) $this->filters['user_id']);
        }

        // Search by name or NIP (legacy)
        if (isset($this->filters['search']) && $this->filters['search']) {
            $search = $this->filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $all = $query->get();
        // Group by user_id + date, ambil record CHECK-IN PALING AWAL; jam pulang = check_out paling akhir hari itu
        $grouped = $all->groupBy(function ($a) {
            return $a->user_id . '_' . $a->attendance_date->format('Y-m-d');
        });
        $result = $grouped->map(function ($items) {
            $earliest = $items->sortBy('check_in')->first();
            $latestCheckOut = $items->filter(fn($a) => $a->check_out)->max('check_out');
            if ($latestCheckOut !== null) {
                $earliest->setAttribute('check_out', $latestCheckOut);
            }
            return $earliest;
        })->sortByDesc(function ($a) {
            return $a->attendance_date->format('Y-m-d') . ' ' . ($a->check_in ? $a->check_in->format('H:i:s') : '');
        })->values();

        return $result;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Tanggal',
            'NIP',
            'Nama',
            'Jenis Kerja',
            'Jam Masuk',
            'Jam Pulang',
            'Status',
            'Keterangan'
        ];
    }

    /**
     * @param Attendance $attendance
     * @return array
     */
    public function map($attendance): array
    {
        $settings = Setting::getSettings();
        // Sabtu/Minggu selalu Tepat Waktu (hanya catatan, tidak dihitung terlambat)
        $status = $attendance->punctualityStatus($settings->check_in_end ?: '09:00:00');

        // Keterangan (hanya notes/catatan dari absensi)
        $keterangan = $attendance->notes ?: '-';

        return [
            $attendance->attendance_date->format('d/m/Y'),
            $attendance->user->nip ?? '-',
            $attendance->user->name,
            $attendance->work_type,
            $attendance->check_in ? Carbon::parse($attendance->check_in, 'Asia/Jakarta')->format('H:i:s') : '-',
            $attendance->check_out ? Carbon::parse($attendance->check_out, 'Asia/Jakarta')->format('H:i:s') : '-',
            $status,
            $keterangan ?: '-'
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Export Absensi';
    }
}
