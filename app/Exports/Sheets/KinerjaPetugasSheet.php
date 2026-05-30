<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KinerjaPetugasSheet implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    /** Warna aksen header kolom tiket */
    private array $columnColors = [
        'assigned' => '3B82F6',  // Ditugaskan — biru
        'done' => '10B981',  // Selesai     — hijau
        'reject' => 'EF4444',  // Ditolak     — merah
        'weekend' => '8B5CF6',  // Hari Libur  — ungu
        'offhour' => 'F59E0B',  // Di Luar Jam — amber
    ];

    public function __construct(
        protected Carbon $startDate,
        protected Carbon $endDate,
        protected array $staffData,
    ) {}

    public function title(): string
    {
        return '2. Kinerja Petugas';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['KINERJA PETUGAS HELPDESK'];

        $rows[] = [$this->startDate->format('d F Y').' s.d. '.$this->endDate->format('d F Y')];

        $rows[] = [
            'Catatan: Skor Ranking = CSI (85%) + Dedikasi Luar Jam (15%). '.
            'Tiket hari libur/weekend mendapat +2 poin; tiket di luar jam kerja mendapat +1 poin.',
        ];

        $rows[] = [
            'No',
            'Nama Petugas',
            'Ditugaskan',
            'Selesai',
            'Ditolak',
            'Tingkat Selesai (%)',
            'Rata-rata Waktu Penyelesaian',
            'Rating Bintang',
            'Skor CSI (%)',
            'Jumlah Survei',
            'Tiket Hari Libur',  // weekend_tickets
            'Tiket Luar Jam',    // offhour_tickets
            'Skor Dedikasi',     // dedikasi_score (0–10)
            'Skor Ranking',      // ranking_score (composite, untuk urutan)
        ];

        foreach ($this->staffData as $idx => $s) {
            $rows[] = [
                $idx + 1,
                $s['name'],
                (int) $s['assigned'],
                (int) $s['done'],
                (int) $s['reject'],
                $s['rate'],
                $s['avg_time'],
                $s['star'],
                $s['csi'],
                (int) $s['surveys'],
                (int) ($s['weekend_tickets'] ?? 0),
                (int) ($s['offhour_tickets'] ?? 0),
                $s['dedikasi_score'] ?? 0,
                $s['ranking_score'] ?? $s['csi'],
            ];
        }

        $rows[] = [
            '',
            'TOTAL',
            array_sum(array_column($this->staffData, 'assigned')),
            array_sum(array_column($this->staffData, 'done')),
            array_sum(array_column($this->staffData, 'reject')),
            '-', '-', '-', '-',
            array_sum(array_column($this->staffData, 'surveys')),
            array_sum(array_column($this->staffData, 'weekend_tickets')),
            array_sum(array_column($this->staffData, 'offhour_tickets')),
            '-', '-',
        ];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $maxCol = 'N'; // A–N = 14 kolom

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

                $sheet->mergeCells("A3:{$maxCol}3");
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 8, 'color' => ['rgb' => '92400E']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'wrapText' => true],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(24);

                $sheet->getStyle("A4:{$maxCol}4")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '064E3B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(32);

                $accentCols = [
                    'C' => 'assigned',
                    'D' => 'done',
                    'E' => 'reject',
                    'K' => 'weekend',
                    'L' => 'offhour',
                ];
                foreach ($accentCols as $col => $key) {
                    $sheet->getStyle("{$col}4")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $this->columnColors[$key]]],
                    ]);
                }

                $sheet->getStyle('M4')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6D28D9']],
                ]);
                $sheet->getStyle('N4')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
                ]);

                $highestRow = $sheet->getHighestRow();
                $dataStart = 5;
                $dataEnd = $highestRow - 1; // baris terakhir = total
                $totalRow = $highestRow;

                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    $color = ($r % 2 === 0) ? 'ECFDF5' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:{$maxCol}{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1FAE5']]],
                    ]);
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    $sheet->getStyle("K{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => ($r % 2 === 0) ? 'EDE9FE' : 'F5F3FF']],
                    ]);
                    $sheet->getStyle("L{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => ($r % 2 === 0) ? 'FEF9C3' : 'FEFCE8']],
                    ]);
                    $sheet->getStyle("M{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '5B21B6']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => ($r % 2 === 0) ? 'EDE9FE' : 'F5F3FF']],
                    ]);
                    $sheet->getStyle("N{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '1E40AF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => ($r % 2 === 0) ? 'DBEAFE' : 'EFF6FF']],
                    ]);
                }

                $sheet->getStyle("A{$totalRow}:{$maxCol}{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1FAE5']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle("B{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle("A5:{$maxCol}{$totalRow}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '065F46']]],
                ]);

                $sheet->freezePane('C5');
            },
        ];
    }
}
