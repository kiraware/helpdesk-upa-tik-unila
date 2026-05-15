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
        $rows[] = [];
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
        ];

        foreach ($this->staffData as $idx => $s) {
            $rows[] = [
                $idx + 1,
                $s['name'],
                (int) $s['assigned'],
                (int) $s['done'],
                (int) $s['reject'],
                $s['rate'].'%',
                $s['avg_time'],
                $s['star'],
                $s['csi'].'%',
                (int) $s['surveys'],
            ];
        }

        // Baris total
        $rows[] = [
            '',
            'TOTAL',
            array_sum(array_column($this->staffData, 'assigned')),
            array_sum(array_column($this->staffData, 'done')),
            array_sum(array_column($this->staffData, 'reject')),
            '-',
            '-',
            '-',
            '-',
            array_sum(array_column($this->staffData, 'surveys')),
        ];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $maxCol = 'J'; // 10 kolom: A–J

                // Baris 1: Judul
                $sheet->mergeCells("A1:{$maxCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B45309']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Baris 2: Sub-judul periode
                $sheet->mergeCells("A2:{$maxCol}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'B45309']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris 4: Header kolom tabel (baris 3 kosong, header di baris 4 setelah array())
                // array() menghasilkan: baris 1=judul, 2=periode, 3=kosong, 4=header
                $sheet->getStyle("A4:{$maxCol}4")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '78350F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(32);

                // Baris data: 5 s.d. highestRow-1, baris total = highestRow
                $highestRow = $sheet->getHighestRow();
                $dataStart = 5;
                $dataEnd = $highestRow - 1;
                $totalRow = $highestRow;

                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    $color = ($r % 2 === 0) ? 'FEF9EE' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:{$maxCol}{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FDE68A']]],
                    ]);
                    // Nama petugas rata kiri
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    // Rata-rata waktu rata kiri
                    $sheet->getStyle("G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Baris TOTAL
                $sheet->getStyle("A{$totalRow}:{$maxCol}{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B45309']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("B{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Border luar tabel
                $sheet->getStyle("A4:{$maxCol}{$totalRow}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'B45309']]],
                ]);

                $sheet->freezePane('C5');
            },
        ];
    }
}
