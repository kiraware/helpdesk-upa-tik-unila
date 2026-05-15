<?php

namespace App\Exports\Sheets;

use App\Enums\TicketStatus;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StatusTiketSheet implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    /** Warna aksen per status */
    private array $statusColors = [
        'waiting' => 'F59E0B',
        'progress' => '3B82F6',
        'done' => '10B981',
        'reject' => 'EF4444',
    ];

    private array $statuses = [];

    private int $rowTotal = 0;

    public function __construct(
        protected Carbon $startDate,
        protected Carbon $endDate,
        protected array $reportData,   // dari prepareData() — per-layanan
        protected array $grandTotals,  // dari prepareData()
    ) {
        $this->statuses = TicketStatus::cases();
    }

    public function title(): string
    {
        return '4. Status Tiket';
    }

    public function columnWidths(): array
    {
        // A=No, B=Layanan, C=Total, D..G=per-status, H=% Selesai
        $totalCols = 3 + count($this->statuses) + 1;
        $widths = ['A' => 6, 'B' => 36];
        for ($i = 3; $i <= $totalCols; $i++) {
            $widths[Coordinate::stringFromColumnIndex($i)] = 16;
        }

        return $widths;
    }

    public function array(): array
    {
        $gt = $this->grandTotals;
        $rows = [];

        $rows[] = ['REKAPITULASI TIKET BERDASARKAN STATUS'];
        $rows[] = [$this->startDate->format('d F Y').' s.d. '.$this->endDate->format('d F Y')];
        $rows[] = [];

        // Header
        $header = ['No', 'Jenis Layanan', 'Total'];
        foreach ($this->statuses as $status) {
            $header[] = ucfirst($status->value);
        }
        $header[] = '% Selesai';
        $rows[] = $header;

        // Data per layanan
        foreach ($this->reportData as $idx => $item) {
            $total = (int) $item['total'];
            $done = (int) ($item['statuses']['done'] ?? 0);
            $doneRate = $total > 0 ? round(($done / $total) * 100, 1).'%' : '0%';

            $row = [$idx + 1, $item['name'], $total];
            foreach ($this->statuses as $status) {
                $row[] = (int) ($item['statuses'][$status->value] ?? 0);
            }
            $row[] = $doneRate;
            $rows[] = $row;
        }

        // Grand Total
        $gtTotal = (int) ($gt['total'] ?? 0);
        $gtDone = (int) ($gt['statuses']['done'] ?? 0);
        $gtDoneRate = $gtTotal > 0 ? round(($gtDone / $gtTotal) * 100, 1).'%' : '0%';

        $totalRow = ['', 'TOTAL', $gtTotal];
        foreach ($this->statuses as $status) {
            $totalRow[] = (int) ($gt['statuses'][$status->value] ?? 0);
        }
        $totalRow[] = $gtDoneRate;
        $rows[] = $totalRow;

        $this->rowTotal = count($rows);

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalCols = 3 + count($this->statuses) + 1;
                $maxCol = Coordinate::stringFromColumnIndex($totalCols);
                $rt = $this->rowTotal;

                // Baris 1: Judul
                $sheet->mergeCells("A1:{$maxCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F766E']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Baris 2: Sub-judul
                $sheet->mergeCells("A2:{$maxCol}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '0F766E']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CCFBF1']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris 4: Header tabel
                $sheet->getStyle("A4:{$maxCol}4")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '134E4A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(30);

                // Warna header per-kolom status (D–G)
                $statusCol = 4; // kolom D (index 4 = 1-based)
                foreach ($this->statuses as $status) {
                    $colLetter = Coordinate::stringFromColumnIndex($statusCol);
                    $hexColor = $this->statusColors[$status->value] ?? '6B7280';
                    $sheet->getStyle("{$colLetter}4")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $hexColor]],
                    ]);
                    $statusCol++;
                }

                // Baris data
                $dataStart = 5;
                $dataEnd = $rt - 1;

                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    $color = ($r % 2 === 0) ? 'F0FDFA' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:{$maxCol}{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCFBF1']]],
                    ]);
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Baris TOTAL
                $sheet->getStyle("A{$rt}:{$maxCol}{$rt}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F766E']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("B{$rt}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Border luar
                $sheet->getStyle("A4:{$maxCol}{$rt}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0F766E']]],
                ]);

                $sheet->freezePane('C5');
            },
        ];
    }
}
