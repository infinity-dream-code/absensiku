<?php

namespace App\Exports;

use App\Models\KpiAssessment;
use App\Services\KpiCalculator;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KpiAssessmentExport implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    protected KpiAssessment $assessment;

    protected int $tableHeaderRow = 8;

    protected int $tableDataStartRow = 9;

    protected int $tableTotalRow = 9;

    public function __construct(KpiAssessment $assessment)
    {
        $this->assessment = $assessment;
    }

    public function array(): array
    {
        $a = $this->assessment;
        $roleName = $a->role->role ?? '-';
        $periode = KpiCalculator::monthName($a->bulan) . ' ' . $a->tahun;
        $rows = [];

        $rows[] = ['FORM PENILAIAN KPI DIVISI ' . strtoupper($roleName)];
        $rows[] = [''];
        $rows[] = ['Nama Karyawan', $a->user->name ?? '-', '', '', 'SKOR AKHIR', $a->skor_akhir];
        $rows[] = ['Jabatan', $roleName, '', '', 'Kategori', $a->kategori];
        $rows[] = ['Nama Penilai', $a->penilai->name ?? '-', '', '', 'Tindak Lanjut', KpiCalculator::kategoriTindakLanjut((string) $a->kategori)];
        $rows[] = ['Periode Penilaian', $periode];
        $rows[] = [''];
        $rows[] = ['No', 'Indikator KPI', 'Bobot (%)', 'Skor (1-10)', 'Nilai Akhir'];

        $this->tableHeaderRow = count($rows);
        $this->tableDataStartRow = $this->tableHeaderRow + 1;

        $individuNames = collect($a->user->kpi_indikator_individu ?? [])
            ->map(fn ($item) => mb_strtolower(trim((string) ($item['nama'] ?? ''))))
            ->filter()
            ->values();

        $isIndividu = function (string $nama) use ($individuNames): bool {
            return $individuNames->contains(mb_strtolower(trim($nama)));
        };

        $roleDetails = $a->details->filter(fn ($detail) => !$isIndividu($detail->nama_indikator));
        $individuDetails = $a->details->filter(fn ($detail) => $isIndividu($detail->nama_indikator));

        $totalBobot = 0;
        $totalSkor = 0;
        $totalNilai = 0.0;
        $rowNo = 0;

        foreach ($roleDetails as $detail) {
            $rowNo++;
            $rows[] = [
                $rowNo,
                $detail->nama_indikator,
                $detail->bobot,
                $detail->skor,
                $detail->nilai_akhir,
            ];
            $totalBobot += $detail->bobot;
            $totalSkor += $detail->skor;
            $totalNilai += $detail->nilai_akhir;
        }

        if ($individuDetails->isNotEmpty()) {
            $rows[] = ['', 'INDIKATOR INDIVIDU', '', '', ''];

            foreach ($individuDetails as $detail) {
                $rowNo++;
                $rows[] = [
                    $rowNo,
                    $detail->nama_indikator,
                    $detail->bobot > 0 ? $detail->bobot : '-',
                    $detail->skor,
                    $detail->nilai_akhir > 0 ? $detail->nilai_akhir : '-',
                ];
                $totalBobot += $detail->bobot;
                $totalSkor += $detail->skor;
                $totalNilai += $detail->nilai_akhir;
            }
        }

        $rows[] = ['', 'TOTAL', $totalBobot, $totalSkor, round($totalNilai, 1)];
        $this->tableTotalRow = count($rows);

        $definedIndividu = collect($a->user->kpi_indikator_individu ?? [])->filter(
            fn ($item) => trim((string) ($item['nama'] ?? '')) !== ''
        );

        if ($definedIndividu->isNotEmpty()) {
            $rows[] = [''];
            $rows[] = ['DAFTAR INDIKATOR INDIVIDU KARYAWAN'];
            $rows[] = ['No', 'Nama Indikator', 'Bobot (%)'];
            $idx = 0;
            foreach ($definedIndividu as $item) {
                $idx++;
                $rows[] = [
                    $idx,
                    $item['nama'],
                    !empty($item['bobot']) ? $item['bobot'] : '-',
                ];
            }
        }

        $rows[] = [''];
        $rows[] = ['REKOMENDASI / UMPAN BALIK'];
        $rows[] = [$a->rekomendasi ?: '-'];
        $rows[] = [''];
        $rows[] = ['Keterangan Skor 1-10'];
        $rows[] = ['1-2 = Sangat Kurang', '3-4 = Kurang', '5-6 = Cukup', '7-8 = Baik', '9-10 = Sangat Baik'];
        $rows[] = [''];
        $rows[] = ['Kategori & Tindak Lanjut'];
        $rows[] = ['≥90 Sangat Baik — Bonus maksimal, kandidat promosi'];
        $rows[] = ['80–89 Baik — Bonus normal'];
        $rows[] = ['70–79 Cukup — Coaching & monitoring'];
        $rows[] = ['60–69 Kurang — Surat pembinaan SP1'];
        $rows[] = ['<60 Tidak Memenuhi — Evaluasi kontrak'];
        $rows[] = [''];
        $rows[] = ['', '', '', 'Semarang, ' . Carbon::now('Asia/Jakarta')->locale('id')->isoFormat('D MMMM YYYY')];
        $rows[] = ['', '', '', 'Manager ' . $roleName];
        $rows[] = [''];
        $rows[] = ['', '', '', $a->penilai->name ?? ''];

        return $rows;
    }

    public function title(): string
    {
        return 'KPI';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C5B0E0');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('E3:F5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('E3')->getFont()->setBold(true);
                $sheet->getStyle('F3')->getFont()->setBold(true)->setSize(16);

                $headerRow = $this->tableHeaderRow;
                $dataStart = $this->tableDataStartRow;
                $totalRow = $this->tableTotalRow;

                $sheet->getStyle('A' . $headerRow . ':E' . $headerRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $headerRow . ':E' . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C5B0E0');
                $sheet->getStyle('A' . $headerRow . ':E' . $headerRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                if ($totalRow > $dataStart) {
                    $sheet->getStyle('A' . $dataStart . ':E' . $totalRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                $sheet->getStyle('A' . $totalRow . ':E' . $totalRow)->getFont()->setBold(true);

                $rekRow = $totalRow + 2;
                $sheet->mergeCells('A' . $rekRow . ':E' . $rekRow);
                $sheet->getStyle('A' . $rekRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $rekRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C5B0E0');
            },
        ];
    }
}
