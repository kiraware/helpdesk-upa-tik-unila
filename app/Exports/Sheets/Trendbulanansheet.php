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

        // Tampilkan semua 12 bulan dari tahun referensi (ambil dari startDate atau tahun berjalan).
        // Jika rentang periode melewati >1 tahun, iterasi per bulan dari startDate s.d. endDate.
        $current = $this->startDate->copy()->startOfMonth();
        $end = $this->endDate->copy()->startOfMonth();

        // Pastikan selalu menampilkan minimal Januari–Desember tahun startDate
        $yearStart = $this->startDate->copy()->startOfYear();
        $yearEnd = $this->startDate->copy()->endOfYear()->startOfMonth();

        // Jika rentang < 1 tahun dalam tahun yang sama, tampilkan semua bulan di tahun itu
        if ($this->startDate->year === $this->endDate->year) {
            $current = $yearStart->copy();
            $end = $yearEnd->copy();
        }

        $no = 1;
        while ($current <= $end) {
            $key = $current->format('Y-m');
            $data = $this->monthlyData[$key] ?? ['total' => 0, 'done' => 0, 'reject' => 0];
            $total = $data['total'] ?: 1;

            $rows[] = [
                $no++,
                $current->translatedFormat('F Y'),
                (int) $data['total'],
                (int) $data['done'],
                (int) $data['reject'],
                round(($data['done'] / $total) * 100, 1),
                round(($data['reject'] / $total) * 100, 1),
            ];

            $current->addMonth();
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

                // Baris 1: Judul utama
                $sheet->mergeCells('A1:G1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Baris 2: Sub-judul periode
                $sheet->mergeCells('A2:G2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '6D28D9']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EDE9FE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris 4: Header tabel kolom
                $sheet->getStyle('A3:G3')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4C1D95']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(25);

                // Hitung jumlah baris data dari isi sheet.
                // Baris 1=judul, 2=periode, 3=kosong, 4=header → data mulai baris 5.
                // Total row = baris terakhir sebelum akhir (ditandai kolom B = 'TOTAL').
                $highestRow = $sheet->getHighestRow();
                $dataStart = 4;
                $dataEnd = $highestRow - 1; // baris data terakhir (sebelum TOTAL)
                $totalRow = $highestRow;     // baris TOTAL

                // Style baris data (baris 5 s.d. dataEnd)
                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    $color = ($r % 2 === 0) ? 'F5F3FF' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
                    ]);
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Style baris TOTAL (baris terakhir tabel)
                $sheet->getStyle("A{$totalRow}:G{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                ]);
                $sheet->getStyle("B{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Border luar seluruh tabel (header s.d. total)
                $sheet->getStyle("A4:G{$totalRow}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '7C3AED']]],
                ]);

                $sheet->freezePane('A5');
            },
        ];
    }
}
