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

class RingkasanSheet implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    public function __construct(
        protected Carbon $startDate,
        protected Carbon $endDate,
        protected string $period,
        protected array $grandTotals,
        protected array $entityDist,
        protected array $staffData,
    ) {}

    public function title(): string
    {
        return '1. Ringkasan Eksekutif';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, 'B' => 35, 'C' => 18, 'D' => 18, 'E' => 18,
            'F' => 18, 'G' => 18, 'H' => 18, 'I' => 18,
        ];
    }

    public function array(): array
    {
        $gt = $this->grandTotals;
        $total = $gt['total'] ?: 1;
        $compRate = round(($gt['done'] / $total) * 100, 1);
        $rejectRate = round(($gt['reject'] / $total) * 100, 1);
        $periodLabel = match ($this->period) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'yearly' => 'Tahunan',
            default => 'Kustom',
        };

        $rows = [];

        // ---- HEADER LAPORAN ----
        $rows[] = ['LAPORAN HELPDESK TIK UNIVERSITAS LAMPUNG'];
        $rows[] = ['Ringkasan Eksekutif — Periode '.$periodLabel];
        $rows[] = [$this->startDate->format('d F Y').' s.d. '.$this->endDate->format('d F Y')];
        $rows[] = ['Dicetak pada: '.Carbon::now()->format('d F Y, H:i').' WIB'];
        $rows[] = [];

        // ---- RINGKASAN TIKET ----
        $rows[] = ['STATISTIK TIKET', '', '', '', ''];
        $rows[] = ['Keterangan', 'Jumlah', 'Persentase', '', ''];
        $rows[] = ['Total Tiket Masuk',   $gt['total'],    '100%',             '', ''];
        $rows[] = ['  - Menunggu',         $gt['waiting'],  $this->pct($gt['waiting'], $total), '', ''];
        $rows[] = ['  - Sedang Diproses',  $gt['progress'], $this->pct($gt['progress'], $total), '', ''];
        $rows[] = ['  - Selesai (Done)',   $gt['done'],     $this->pct($gt['done'], $total), '', ''];
        $rows[] = ['  - Ditolak (Reject)', $gt['reject'],   $this->pct($gt['reject'], $total), '', ''];
        $rows[] = ['Tingkat Penyelesaian', $compRate.'%', '', '', ''];
        $rows[] = ['Tingkat Penolakan',    $rejectRate.'%', '', '', ''];
        $rows[] = [];

        // ---- DISTRIBUSI ENTITAS ----
        $rows[] = ['DISTRIBUSI ENTITAS PENGGUNA', '', '', '', ''];
        $rows[] = ['Entitas', 'Jumlah Tiket', 'Persentase', '', ''];
        $entityLabels = [
            'mahasiswa' => 'Mahasiswa',
            'dosen' => 'Dosen',
            'tendik' => 'Tenaga Kependidikan (Tendik)',
            'karyawan' => 'Karyawan',
            'superuser' => 'Super User / Admin',
            'tamu' => 'Tamu',
            'lainnya' => 'Lainnya',
        ];
        $entTotal = array_sum($this->entityDist) ?: 1;
        foreach ($entityLabels as $key => $label) {
            $cnt = $this->entityDist[$key] ?? 0;
            $rows[] = [$label, $cnt, $this->pct($cnt, $entTotal), '', ''];
        }
        $rows[] = ['TOTAL', array_sum($this->entityDist), '100%', '', ''];
        $rows[] = [];

        // ---- KINERJA STAF ----
        $rows[] = ['KINERJA PETUGAS HELPDESK', '', '', '', '', '', '', ''];
        $rows[] = ['No', 'Nama Petugas', 'Tiket Ditugaskan', 'Tiket Selesai', 'Tiket Ditolak',
            'Tingkat Selesai', 'Rata-rata Waktu (Jam)', 'Rating Bintang', 'Skor CSI (%)'];
        $no = 1;
        foreach ($this->staffData as $s) {
            $rows[] = [
                $no++,
                $s['name'],
                $s['assigned'],
                $s['done'],
                $s['reject'],
                $s['rate'].'%',
                $s['avg_time'],
                $s['star'],
                $s['csi'].'%',
            ];
        }
        $rows[] = [];
        $rows[] = ['Catatan: Laporan ini digenerate otomatis oleh Sistem Helpdesk TIK UNILA.'];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Title styling
                $sheet->mergeCells('A1:I1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);

                $sheet->mergeCells('A2:I2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(22);

                $sheet->mergeCells('A3:I3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A4:I4');
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '6B7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Section headers styling — dynamically find rows
                $data = $this->array();
                foreach ($data as $rowIdx => $row) {
                    $excelRow = $rowIdx + 1;
                    $cellVal = $row[0] ?? '';

                    if (in_array($cellVal, [
                        'STATISTIK TIKET',
                        'DISTRIBUSI ENTITAS PENGGUNA',
                        'KINERJA PETUGAS HELPDESK',
                    ])) {
                        $sheet->mergeCells("A{$excelRow}:I{$excelRow}");
                        $sheet->getStyle("A{$excelRow}:I{$excelRow}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT,
                                'indent' => 1],
                        ]);
                        $sheet->getRowDimension($excelRow)->setRowHeight(20);
                    }

                    // Table header rows
                    if (in_array($cellVal, ['Keterangan', 'Entitas', 'No'])) {
                        $sheet->getStyle("A{$excelRow}:I{$excelRow}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }

                    // Total row styling
                    if ($cellVal === 'TOTAL' || $cellVal === 'Tingkat Penyelesaian') {
                        $sheet->getStyle("A{$excelRow}:I{$excelRow}")->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDF4']],
                        ]);
                    }

                    // Zebra striping for data rows
                    if ($excelRow > 8 && ! empty($cellVal) && $excelRow % 2 === 0) {
                        $currentFill = $sheet->getStyle("A{$excelRow}")->getFill();
                        if ($currentFill->getFillType() === Fill::FILL_NONE) {
                            $sheet->getStyle("A{$excelRow}:I{$excelRow}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
                            ]);
                        }
                    }
                }

                // Footer note
                $lastRow = count($data);
                $sheet->mergeCells("A{$lastRow}:I{$lastRow}");
                $sheet->getStyle("A{$lastRow}")->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '9CA3AF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Global border for all used cells
                $sheet->getStyle('A1:I'.$lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']],
                    ],
                ]);
            },
        ];
    }

    private function pct(int $val, int $total): string
    {
        return $total > 0 ? round(($val / $total) * 100, 1).'%' : '0%';
    }
}
