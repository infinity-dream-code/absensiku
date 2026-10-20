<?php

namespace App\Exports;

use App\Models\Overtime;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class OvertimeExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Overtime::with('user')
            ->orderBy('overtime_date', 'desc')
            ->orderBy('start_time', 'desc');

        if (!empty($this->filters['year'])) {
            $query->whereYear('overtime_date', (int) $this->filters['year']);
        }

        if (!empty($this->filters['month'])) {
            $query->whereMonth('overtime_date', (int) $this->filters['month']);
        }

        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', (int) $this->filters['user_id']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Hari',
            'NIP',
            'Nama',
            'Lokasi',
            'Jenis Lembur',
            'Jam Mulai',
            'Jam Selesai',
            'Durasi',
            'Catatan',
        ];
    }

    public function map($overtime): array
    {
        return [
            $overtime->overtime_date->format('d/m/Y'),
            $overtime->overtime_date->locale('id')->isoFormat('dddd'),
            $overtime->user->nip ?? '-',
            $overtime->user->name ?? '-',
            $overtime->work_type ?: '-',
            $overtime->scheduleTypeLabel(),
            Carbon::parse($overtime->start_time)->format('H:i'),
            Carbon::parse($overtime->end_time)->format('H:i'),
            $overtime->durationLabel(),
            $overtime->notes ?: '-',
        ];
    }

    public function title(): string
    {
        return 'Export Lembur';
    }
}
