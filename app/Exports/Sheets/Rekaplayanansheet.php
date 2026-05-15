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

class RekapLayananSheet implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    // Marker baris untuk styling
    private int $rowHeader = 5;

    private int $rowDataStart = 6;

    private int $rowDataEnd = 0;

    private int $rowTotal = 0;

    public function __construct(
        protected Carbon $startDate,
        protected Carbon $endDate,
        protected array $reportData,
        protected array $grandTotals,
    ) {}

    public function title(): string
    {
        return '2. Rekap Layanan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,  'B' => 35, 'C' => 12, 'D' => 12, 'E' => 12,
            'F' => 12, 'G' => 12, 'H' => 14, 'I' => 14, 'J' => 14,
            'K' => 14, 'L' => 14, 'M' => 14, 'N' => 14, 'O' => 20,
        ];
    }

    public function array(): array
    {
        $gt = $this->grandTotals;
        $rows = [];

        // 1-2. Judul & Periode (Mengikuti struktur Trend)
        $rows[] = ['REKAPITULASI PELAYANAN HELPDESK TIK UNIVERSITAS LAMPUNG'];
        $rows[] = ['Periode: '.$this->startDate->format('d F Y').' s.d. '.$this->endDate->format('d F Y')];
        $rows[] = ['Dicetak: '.Carbon::now()->format('d F Y, H:i').' WIB'];
        $rows[] = ['']; // Baris 4 kosong

        // 5. Header Tabel
        $rows[] = [
            'No', 'Jenis Layanan', 'Total', 'Waiting', 'Progress', 'Done', 'Reject',
            'Mhs', 'Dosen', 'Tendik', 'Karyawan', 'S.User', 'Tamu', 'Lainnya', 'CSI Avg',
        ];

        // 6+. Data Layanan
        foreach ($this->reportData as $idx => $item) {
            $rows[] = [
                $idx + 1,
                $item['name'],
                (int) $item['total'],
                (int) $item['waiting'],
                (int) $item['progress'],
                (int) $item['done'],
                (int) $item['reject'],
                (int) ($item['entities']['mahasiswa'] ?? 0),
                (int) ($item['entities']['dosen'] ?? 0),
                (int) ($item['entities']['tendik'] ?? 0),
                (int) ($item['entities']['karyawan'] ?? 0),
                (int) ($item['entities']['superuser'] ?? 0),
                (int) ($item['entities']['tamu'] ?? 0),
                (int) ($item['entities']['lainnya'] ?? 0),
                isset($item['csi']) ? round($item['csi'], 2).'%' : '-',
            ];
        }

        $this->rowDataEnd = count($rows);

        // Baris Total Akhir
        $rows[] = [
            '', 'GRAND TOTAL',
            (int) ($gt['total'] ?? 0),
            (int) ($gt['waiting'] ?? 0),
            (int) ($gt['progress'] ?? 0),
            (int) ($gt['done'] ?? 0),
            (int) ($gt['reject'] ?? 0),
            (int) ($gt['mahasiswa'] ?? 0),
            (int) ($gt['dosen'] ?? 0),
            (int) ($gt['tendik'] ?? 0),
            (int) ($gt['karyawan'] ?? 0),
            (int) ($gt['superuser'] ?? 0),
            (int) ($gt['tamu'] ?? 0),
            (int) ($gt['lainnya'] ?? 0),
            '-',
        ];

        $this->rowTotal = count($rows);

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $maxCol = 'O';
                $rt = $this->rowTotal;
                $rh = $this->rowHeader;

                // ── STYLE JUDUL UTAMA (Baris 1) ──
                $sheet->mergeCells("A1:{$maxCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']], // Hijau Trend
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // ── STYLE SUB-JUDUL PERIODE (Baris 2) ──
                $sheet->mergeCells("A2:{$maxCol}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '065F46']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']], // Hijau Muda Trend
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── STYLE HEADER TABEL (Baris 5) ──
                $sheet->getStyle("A{$rh}:{$maxCol}{$rh}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '064E3B']], // Hijau Tua Trend
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension($rh)->setRowHeight(30);

                // ── STYLE DATA & ZEBRA STRIPING (Baris 6 s.d. rowTotal) ──
                for ($r = $this->rowDataStart; $r <= $rt; $r++) {
                    $color = ($r % 2 === 0) ? 'ECFDF5' : 'FFFFFF'; // Zebra Hijau Trend
                    $sheet->getStyle("A{$r}:{$maxCol}{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1FAE5']]],
                    ]);
                    // Kolom Nama Layanan (B) rata kiri
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // ── STYLE KHUSUS BARIS TOTAL (Baris Terakhir) ──
                $sheet->getStyle("A{$rt}:{$maxCol}{$rt}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']], // Highlight hijau muda untuk total
                ]);
                $sheet->mergeCells("A{$rt}:B{$rt}");

                // Border Luar Tebal (Sesuai gaya Trend)
                $sheet->getStyle("A{$rh}:{$maxCol}{$rt}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '065F46']]],
                ]);

                // Pengaturan Tambahan
                $sheet->freezePane('C6'); // Bekukan No & Layanan serta Header
                $sheet->setAutoFilter("A{$rh}:{$maxCol}{$rh}"); // Tambahkan Filter
            },
        ];
    }
}
