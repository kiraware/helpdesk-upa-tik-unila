<?php

namespace App\Exports\Sheets;

use App\Enums\TicketStatus;
use App\Enums\UserEntity;
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

class RekapLayananSheet implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    private int $rowHeader = 3;

    private int $rowDataStart = 4;

    private int $rowTotal = 0;

    // Properti untuk menampung daftar enum
    private array $statuses = [];

    private array $entities = [];

    public function __construct(
        protected Carbon $startDate,
        protected Carbon $endDate,
        protected array $reportData,
        protected array $grandTotals,
    ) {
        // Mengambil semua cases dari Enum secara dinamis
        $this->statuses = TicketStatus::cases();
        $this->entities = UserEntity::cases();
    }

    public function title(): string
    {
        return '2. Rekap Layanan';
    }

    public function columnWidths(): array
    {
        // Kolom A & B tetap, sisanya lebar standar 12-14
        $widths = ['A' => 5, 'B' => 35];
        $totalCols = 2 + count($this->statuses) + 1 + count($this->entities) + 2;

        for ($i = 3; $i <= $totalCols; $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
            $widths[$colLetter] = 13;
        }

        return $widths;
    }

    public function array(): array
    {
        $gt = $this->grandTotals;
        $rows = [];

        // 1-2. Judul & Periode
        $rows[] = ['REKAPITULASI LAYANAN'];
        $rows[] = [$this->startDate->format('d F Y').' s.d. '.$this->endDate->format('d F Y')];

        // 3. Header Tabel Dinamis
        $header = ['No', 'Jenis Layanan', 'Total'];

        foreach ($this->statuses as $status) {
            $header[] = ucfirst($status->value);
        }

        foreach ($this->entities as $entity) {
            $header[] = ucfirst($entity->value);
        }

        $header[] = 'Jumlah Survei';
        $header[] = 'Rata-Rata CSI';
        $rows[] = $header;

        // 4+. Data Layanan
        foreach ($this->reportData as $idx => $item) {
            $row = [
                $idx + 1,
                $item['name'],
                (int) $item['total'],
            ];

            // Isi kolom status secara dinamis
            foreach ($this->statuses as $status) {
                $row[] = (int) ($item['statuses'][$status->value] ?? 0);
            }

            // Isi kolom entitas secara dinamis
            foreach ($this->entities as $entity) {
                $row[] = (int) ($item['entities'][$entity->value] ?? 0);
            }

            $row[] = (int) ($item['survey_count'] ?? 0);
            $row[] = isset($item['csi'])
                ? round($item['csi'], 2).'%'
                : '-';
            $rows[] = $row;
        }

        // Baris Grand Total Dinamis
        $totalRow = ['', 'TOTAL', (int) ($gt['total'] ?? 0)];

        foreach ($this->statuses as $status) {
            $totalRow[] = (int) ($gt['statuses'][$status->value] ?? 0);
        }

        foreach ($this->entities as $entity) {
            $totalRow[] = (int) ($gt[$entity->value] ?? 0);
        }

        $totalSurvey = collect($this->reportData)->sum('survey_count');

        $totalRow[] = $totalSurvey;
        $totalRow[] = '-';
        $rows[] = $totalRow;

        $this->rowTotal = count($rows);

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Hitung kolom terakhir secara dinamis (huruf kolom)
                $totalColumnCount = 2 + count($this->statuses) + 1 + count($this->entities) + 2;
                $maxCol = Coordinate::stringFromColumnIndex($totalColumnCount);

                $rt = $this->rowTotal;
                $rh = $this->rowHeader;

                // Style Judul
                $sheet->mergeCells("A1:{$maxCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Style Sub-judul
                $sheet->mergeCells("A2:{$maxCol}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '065F46']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Style Header Tabel
                $sheet->getStyle("A{$rh}:{$maxCol}{$rh}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '064E3B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // Zebra Striping & Data Style
                for ($r = $this->rowDataStart; $r <= $rt; $r++) {
                    $color = ($r % 2 === 0) ? 'ECFDF5' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:{$maxCol}{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1FAE5']]],
                    ]);
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Style Grand Total
                $sheet->getStyle("A{$rt}:{$maxCol}{$rt}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                ]);

                $sheet->getStyle("A{$rt}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Border Luar
                $sheet->getStyle("A{$rh}:{$maxCol}{$rt}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '065F46']]],
                ]);

                $sheet->freezePane('C4');
                $sheet->setAutoFilter("A{$rh}:{$maxCol}{$rh}");
            },
        ];
    }
}
