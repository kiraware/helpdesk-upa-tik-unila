<?php

namespace App\Exports\Sheets;

use App\Enums\TicketPriority;
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

class PrioritasTiketSheet implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    /** Warna aksen per prioritas */
    private array $priorityColors = [
        'low' => '22C55E',
        'medium' => 'F59E0B',
        'high' => 'EF4444',
    ];

    /** Warna aksen header per status */
    private array $statusColors = [
        'waiting' => 'F59E0B',
        'progress' => '3B82F6',
        'done' => '10B981',
        'reject' => 'EF4444',
    ];

    private array $priorities = [];

    private array $statuses = [];

    private int $rowTotal = 0;

    public function __construct(
        protected Carbon $startDate,
        protected Carbon $endDate,
        protected $tickets,           // Collection ticket mentah
    ) {
        $this->priorities = TicketPriority::cases();
        $this->statuses = TicketStatus::cases();
    }

    public function title(): string
    {
        return '5. Prioritas Tiket';
    }

    public function columnWidths(): array
    {
        $totalCols = 1 + 1 + count($this->statuses) + 1;
        $widths = ['A' => 18];
        for ($i = 2; $i <= $totalCols; $i++) {
            $widths[Coordinate::stringFromColumnIndex($i)] = 16;
        }

        return $widths;
    }

    public function array(): array
    {
        $rows = [];

        $data = [];
        foreach ($this->priorities as $p) {
            $data[$p->value] = ['total' => 0];
            foreach ($this->statuses as $s) {
                $data[$p->value][$s->value] = 0;
            }
        }

        $grandTotal = 0;
        $grandStatus = [];
        foreach ($this->statuses as $s) {
            $grandStatus[$s->value] = 0;
        }

        foreach ($this->tickets as $ticket) {
            $pKey = $ticket->priority?->value ?? null;
            $sKey = $ticket->status?->value ?? null;

            if ($pKey && isset($data[$pKey])) {
                $data[$pKey]['total']++;
                if ($sKey && isset($data[$pKey][$sKey])) {
                    $data[$pKey][$sKey]++;
                }
            }

            $grandTotal++;
            if ($sKey && isset($grandStatus[$sKey])) {
                $grandStatus[$sKey]++;
            }
        }

        $rows[] = ['REKAPITULASI TIKET BERDASARKAN PRIORITAS'];
        $rows[] = [$this->startDate->format('d F Y').' s.d. '.$this->endDate->format('d F Y')];
        $rows[] = [];

        $header = ['Prioritas', 'Total'];
        foreach ($this->statuses as $s) {
            $header[] = ucfirst($s->value);
        }
        $header[] = '% Selesai';
        $rows[] = $header;

        foreach ($this->priorities as $p) {
            $pTotal = $data[$p->value]['total'];
            $pDone = $data[$p->value]['done'] ?? 0;
            $pReject = $data[$p->value]['reject'] ?? 0;
            $doneRate = $pTotal > 0 ? round((($pDone + $pReject) / $pTotal) * 100, 1) : '0';

            $row = [ucfirst($p->value), $pTotal];
            foreach ($this->statuses as $s) {
                $row[] = $data[$p->value][$s->value];
            }
            $row[] = $doneRate;
            $rows[] = $row;
        }

        $gtDone = $grandStatus['done'] ?? 0;
        $gtReject = $grandStatus['reject'] ?? 0;
        $gtDoneRate = $grandTotal > 0 ? round((($gtDone + $gtReject) / $grandTotal) * 100, 1) : '0';

        $totalRow = ['TOTAL', $grandTotal];
        foreach ($this->statuses as $s) {
            $totalRow[] = $grandStatus[$s->value];
        }
        $totalRow[] = $gtDoneRate;
        $rows[] = $totalRow;

        $this->rowTotal = count($rows) - 1;

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalCols = 1 + 1 + count($this->statuses) + 1;
                $maxCol = Coordinate::stringFromColumnIndex($totalCols);
                $rt = $this->rowTotal;

                $sheet->mergeCells("A1:{$maxCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->mergeCells("A2:{$maxCol}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '065F46']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle("A3:{$maxCol}3")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '064E3B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(30);

                $statusColIdx = 3; // kolom C = index 3
                foreach ($this->statuses as $status) {
                    $colLetter = Coordinate::stringFromColumnIndex($statusColIdx);
                    $hex = $this->statusColors[$status->value] ?? '6B7280';
                    $sheet->getStyle("{$colLetter}3")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $hex]],
                    ]);
                    $statusColIdx++;
                }

                $dataStart = 4;
                $dataEnd = $rt - 1;

                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    $color = ($r % 2 === 0) ? 'ECFDF5' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:{$maxCol}{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1FAE5']]],
                    ]);
                }

                $sheet->getStyle("A{$rt}:{$maxCol}{$rt}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle("A4:{$maxCol}{$rt}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '065F46']]],
                ]);
            },
        ];
    }
}
