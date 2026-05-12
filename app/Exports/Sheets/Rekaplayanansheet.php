<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RekapLayananSheet implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    public function __construct(
        protected Carbon $startDate,
        protected Carbon $endDate,
        protected array $reportData,
        protected array $grandTotals,
    ) {}

    public function title(): string
    {
        return '2. Rekap Layanan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,  'B' => 32, 'C' => 10, 'D' => 10, 'E' => 10,
            'F' => 10, 'G' => 10, 'H' => 14, 'I' => 14, 'J' => 14,
            'K' => 14, 'L' => 14, 'M' => 14, 'N' => 14, 'O' => 20,
        ];
    }

    public function array(): array
    {
        $gt = $this->grandTotals;
        $total = $gt['total'] ?: 1;

        $rows = [];
        $rows[] = ['REKAPITULASI TIKET BERDASARKAN LAYANAN'];
        $rows[] = [$this->startDate->format('d F Y').' s.d. '.$this->endDate->format('d F Y')];
        $rows[] = [];

        // Header group row 1
        $rows[] = ['No', 'Layanan', 'Total', 'Selesai', 'Ditolak', 'Menunggu', 'Proses',
            'Mahasiswa', 'Dosen', 'Tendik', 'Karyawan', 'S.User', 'Tamu', 'Lainnya',
            '% dari Total'];

        $no = 1;
        foreach ($this->reportData as $svc) {
            $svcTotal = $svc['total'] ?: 1;
            $rows[] = [
                $no++,
                $svc['name'],
                $svc['total'],
                $svc['done'],
                $svc['reject'],
                $svc['waiting'],
                $svc['progress'],
                $svc['entities']['mahasiswa'],
                $svc['entities']['dosen'],
                $svc['entities']['tendik'],
                $svc['entities']['karyawan'],
                $svc['entities']['superuser'],
                $svc['entities']['tamu'],
                $svc['entities']['lainnya'],
                round(($svc['total'] / $total) * 100, 2).'%',
            ];
        }

        // Grand total row
        $rows[] = [
            '', 'TOTAL KESELURUHAN',
            $gt['total'], $gt['done'], $gt['reject'], $gt['waiting'], $gt['progress'],
            $gt['mahasiswa'], $gt['dosen'], $gt['tendik'],
            $gt['karyawan'], $gt['superuser'], $gt['tamu'], $gt['lainnya'],
            '100%',
        ];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Title
                $sheet->mergeCells('A1:O1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->mergeCells('A2:O2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '1E40AF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Table header
                $sheet->getStyle('A4:O4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                        'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '374151']]],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(30);

                // Color the entity columns
                $sheet->getStyle('H4:N4')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '064E3B']],
                ]);

                // Data rows
                $dataStart = 5;
                $dataEnd = $dataStart + count($this->reportData) - 1;

                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    $color = ($r % 2 === 0) ? 'F9FAFB' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:O{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
                    ]);
                    // Service name left-aligned
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Total row
                $totalRow = $dataEnd + 1;
                $sheet->getStyle("A{$totalRow}:O{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E3A5F']]],
                ]);
                $sheet->getStyle("B{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Freeze top rows
                $sheet->freezePane('A5');
            },
        ];
    }
}
