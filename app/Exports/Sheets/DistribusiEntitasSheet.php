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

class DistribusiEntitasSheet implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    private array $entityLabels = [
        'mahasiswa' => 'Mahasiswa',
        'dosen' => 'Dosen',
        'tendik' => 'Tenaga Kependidikan (Tendik)',
        'karyawan' => 'Karyawan',
        'superuser' => 'Super User / Admin',
        'tamu' => 'Tamu',
        'lainnya' => 'Lainnya',
    ];

    public function __construct(
        protected Carbon $startDate,
        protected Carbon $endDate,
        protected array $entityDist,
    ) {}

    public function title(): string
    {
        return '3. Distribusi Entitas';
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 38, 'C' => 18, 'D' => 18];
    }

    public function array(): array
    {
        $rows = [];
        $total = array_sum($this->entityDist) ?: 1;

        $rows[] = ['DISTRIBUSI ENTITAS PENGGUNA'];
        $rows[] = [$this->startDate->format('d F Y').' s.d. '.$this->endDate->format('d F Y')];
        $rows[] = [];
        $rows[] = ['No', 'Entitas', 'Jumlah Tiket', 'Persentase (%)'];

        $no = 1;
        foreach ($this->entityLabels as $key => $label) {
            $cnt = (int) ($this->entityDist[$key] ?? 0);
            $pct = $total > 0 ? round(($cnt / $total) * 100, 2) : 0;
            $rows[] = [$no++, $label, $cnt, $pct.'%'];
        }

        // Baris total
        $grandTotal = array_sum($this->entityDist);
        $rows[] = ['', 'TOTAL', (int) $grandTotal, '100%'];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $maxCol = 'D';

                // Baris 1: Judul
                $sheet->mergeCells("A1:{$maxCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6D28D9']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Baris 2: Sub-judul
                $sheet->mergeCells("A2:{$maxCol}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '5B21B6']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EDE9FE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris 4: Header kolom tabel
                $sheet->getStyle('A4:D4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4C1D95']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(28);

                $highestRow = $sheet->getHighestRow();
                $dataStart = 5;
                $dataEnd = $highestRow - 1;
                $totalRow = $highestRow;

                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    $color = ($r % 2 === 0) ? 'F5F3FF' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']]],
                    ]);
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Baris TOTAL
                $sheet->getStyle("A{$totalRow}:D{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6D28D9']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("B{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Border luar tabel
                $sheet->getStyle("A4:D{$totalRow}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '6D28D9']]],
                ]);
            },
        ];
    }
}
