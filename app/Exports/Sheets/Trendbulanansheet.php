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

class TrendBulananSheet implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    public function __construct(
        protected Carbon $startDate,
        protected Carbon $endDate,
        protected array $monthlyData,
    ) {}

    public function title(): string
    {
        return '3. Tren Bulanan';
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 24, 'C' => 16, 'D' => 16, 'E' => 16, 'F' => 20, 'G' => 20];
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = ['TREN TIKET BULANAN'];
        $rows[] = [$this->startDate->format('d F Y').' s.d. '.$this->endDate->format('d F Y')];
        $rows[] = [];
        $rows[] = ['No', 'Bulan', 'Total Tiket', 'Selesai', 'Ditolak', 'Tingkat Selesai (%)', 'Tingkat Tolak (%)'];

        $no = 1;
        foreach ($this->monthlyData as $data) {
            $total = $data['total'] ?: 1;
            $rows[] = [
                $no++,
                $data['label'],
                $data['total'],
                $data['done'],
                $data['reject'],
                round(($data['done'] / $total) * 100, 1),
                round(($data['reject'] / $total) * 100, 1),
            ];
        }

        // Summary row
        $grandTotal = array_sum(array_column($this->monthlyData, 'total'));
        $grandDone = array_sum(array_column($this->monthlyData, 'done'));
        $grandReject = array_sum(array_column($this->monthlyData, 'reject'));
        $gt = $grandTotal ?: 1;

        $rows[] = [
            '', 'TOTAL',
            $grandTotal, $grandDone, $grandReject,
            round(($grandDone / $gt) * 100, 1),
            round(($grandReject / $gt) * 100, 1),
        ];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:G1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->mergeCells('A2:G2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '6D28D9']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EDE9FE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle('A4:G4')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4C1D95']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $dataStart = 5;
                $dataEnd = $dataStart + count($this->monthlyData) - 1;

                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    $color = ($r % 2 === 0) ? 'F5F3FF' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
                    ]);
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                $totalRow = $dataEnd + 1;
                $sheet->getStyle("A{$totalRow}:G{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                ]);
                $sheet->getStyle("B{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->freezePane('A5');
            },
        ];
    }
}
