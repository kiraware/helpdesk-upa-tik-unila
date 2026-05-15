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
        // A=Prioritas, B=Total, C..F=per-status, G=% Selesai
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

        // Hitung distribusi: priority → status → count
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

        // Header
        $header = ['Prioritas', 'Total'];
        foreach ($this->statuses as $s) {
            $header[] = ucfirst($s->value);
        }
        $header[] = '% Selesai';
        $rows[] = $header;

        // Data per prioritas
        foreach ($this->priorities as $p) {
            $pTotal = $data[$p->value]['total'];
            $pDone = $data[$p->value]['done'] ?? 0;
            $doneRate = $pTotal > 0 ? round(($pDone / $pTotal) * 100, 1).'%' : '0%';

            $row = [ucfirst($p->value), $pTotal];
            foreach ($this->statuses as $s) {
                $row[] = $data[$p->value][$s->value];
            }
            $row[] = $doneRate;
            $rows[] = $row;
        }

        // Grand total
        $gtDone = $grandStatus['done'] ?? 0;
        $gtDoneRate = $grandTotal > 0 ? round(($gtDone / $grandTotal) * 100, 1).'%' : '0%';

        $totalRow = ['TOTAL', $grandTotal];
        foreach ($this->statuses as $s) {
            $totalRow[] = $grandStatus[$s->value];
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
                $totalCols = 1 + 1 + count($this->statuses) + 1;
                $maxCol = Coordinate::stringFromColumnIndex($totalCols);
                $rt = $this->rowTotal;

                // Baris 1: Judul
                $sheet->mergeCells("A1:{$maxCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC2626']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Baris 2: Sub-judul
                $sheet->mergeCells("A2:{$maxCol}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'B91C1C']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris 4: Header tabel
                $sheet->getStyle("A4:{$maxCol}4")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '991B1B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(30);

                // Baris data: 5 s.d. rt-1
                $dataStart = 5;
                $dataEnd = $rt - 1;

                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    // Ambil nilai prioritas dari kolom A untuk warna aksen
                    $pVal = strtolower($sheet->getCell("A{$r}")->getValue());
                    $accent = $this->priorityColors[$pVal] ?? 'E5E7EB';

                    // Baris data dengan strip prioritas di kolom A
                    $sheet->getStyle("A{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $accent]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);

                    // Kolom B–maxCol
                    $color = ($r % 2 === 0) ? 'FFF7F7' : 'FFFFFF';
                    $sheet->getStyle("B{$r}:{$maxCol}{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FECACA']]],
                    ]);
                }

                // Baris TOTAL
                $sheet->getStyle("A{$rt}:{$maxCol}{$rt}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC2626']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // Border luar
                $sheet->getStyle("A4:{$maxCol}{$rt}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'DC2626']]],
                ]);
            },
        ];
    }
}
