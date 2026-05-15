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

        // Baris 1: Judul
        $rows[] = ['DISTRIBUSI ENTITAS PENGGUNA'];

        // Baris 2: Sub-judul periode
        $rows[] = [$this->startDate->format('d F Y').' s.d. '.$this->endDate->format('d F Y')];

        // Baris 3: Header — langsung tanpa baris kosong (pola DetailTiketSheet)
        $rows[] = ['No', 'Entitas', 'Jumlah Tiket', 'Persentase (%)'];

        // Baris 4+: Data
        $no = 1;
        foreach ($this->entityLabels as $key => $label) {
            $cnt = (int) ($this->entityDist[$key] ?? 0);
            $pct = $total > 0 ? round(($cnt / $total) * 100, 2) : 0;
            $rows[] = [$no++, $label, $cnt, $pct];
        }

        // Baris total
        $rows[] = ['', 'TOTAL', (int) array_sum($this->entityDist), 100];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $maxCol = 'D';

                // ── Baris 1: Judul ──────────────────────────────────────
                $sheet->mergeCells("A1:{$maxCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // ── Baris 2: Sub-judul periode ──────────────────────────
                $sheet->mergeCells("A2:{$maxCol}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '065F46']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── Baris 3: Header kolom tabel ─────────────────────────
                $sheet->getStyle("A3:{$maxCol}3")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '064E3B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(30);

                // ── Baris data ──────────────────────────────────────────
                $highestRow = $sheet->getHighestRow();
                $dataStart = 4;
                $dataEnd = $highestRow - 1;
                $totalRow = $highestRow;

                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    $color = ($r % 2 === 0) ? 'ECFDF5' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:{$maxCol}{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1FAE5']]],
                    ]);
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // ── Baris TOTAL ─────────────────────────────────────────
                $sheet->getStyle("A{$totalRow}:{$maxCol}{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1FAE5']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle("B{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // ── Border luar tabel ───────────────────────────────────
                $sheet->getStyle("A4:{$maxCol}{$totalRow}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '065F46']]],
                ]);

                $sheet->freezePane('C4');
            },
        ];
    }
}
