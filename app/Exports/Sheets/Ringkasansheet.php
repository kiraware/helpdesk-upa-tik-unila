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
    // Row markers — set during array() build, used in registerEvents()
    private int $rowHeader = 1;

    private int $rowStatSection = 0;

    private int $rowStatHeader = 0;

    private int $rowStatStart = 0;

    private int $rowStatEnd = 0;

    private int $rowEntSection = 0;

    private int $rowEntHeader = 0;

    private int $rowEntStart = 0;

    private int $rowEntEnd = 0;

    private int $rowStaffSection = 0;

    private int $rowStaffHeader = 0;

    private int $rowStaffStart = 0;

    private int $rowStaffEnd = 0;

    private int $rowFooter = 0;

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
        return '1. Ringkasan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,  'B' => 36, 'C' => 18, 'D' => 18, 'E' => 18,
            'F' => 18, 'G' => 22, 'H' => 18, 'I' => 18,
        ];
    }

    public function array(): array
    {
        $gt = $this->grandTotals;
        $total = $gt['total'] ?: 1;
        $compRate = round(($gt['done'] / $total) * 100, 1);
        $rejectRate = round(($gt['reject'] / $total) * 100, 1);

        $rows = [];

        // ── HEADER (Row 1-5) ──
        $rows[] = ['LAPORAN HELPDESK TIK UNIVERSITAS LAMPUNG'];
        $rows[] = ['Ringkasan Eksekutif — Periode '.($this->period === 'custom' ? 'Kustom' : ucfirst($this->period))];
        $rows[] = [$this->startDate->format('d F Y').' s.d. '.$this->endDate->format('d F Y')];
        $rows[] = ['Dicetak: '.Carbon::now()->format('d F Y, H:i').' WIB'];
        $rows[] = [''];

        // ── STATISTIK TIKET (Row 6-14) ──
        $this->rowStatSection = count($rows) + 1;
        $rows[] = ['STATISTIK TIKET'];
        $this->rowStatHeader = count($rows) + 1;
        $rows[] = ['Keterangan', 'Jumlah', 'Persentase'];

        $this->rowStatStart = count($rows) + 1;
        // Gunakan (int) untuk memastikan 0 tampil sebagai angka, bukan field kosong
        $rows[] = ['Total Tiket Masuk',    (int) $gt['total'],    '100%'];
        $rows[] = ['  Menunggu',           (int) $gt['waiting'],  $this->pct((int) $gt['waiting'], $total)];
        $rows[] = ['  Sedang Diproses',    (int) $gt['progress'], $this->pct((int) $gt['progress'], $total)];
        $rows[] = ['  Selesai (Done)',     (int) $gt['done'],     $this->pct((int) $gt['done'], $total)];
        $rows[] = ['  Ditolak (Reject)',   (int) $gt['reject'],   $this->pct((int) $gt['reject'], $total)];
        $rows[] = ['Tingkat Penyelesaian', $compRate.'%', '-'];
        $rows[] = ['Tingkat Penolakan',    $rejectRate.'%', '-'];
        $this->rowStatEnd = count($rows);
        $rows[] = [''];

        // ── DISTRIBUSI ENTITAS (Row 16-25) ──
        $this->rowEntSection = count($rows) + 1;
        $rows[] = ['DISTRIBUSI ENTITAS PENGGUNA'];
        $this->rowEntHeader = count($rows) + 1;
        $rows[] = ['Entitas', 'Jumlah Tiket', 'Persentase'];

        $this->rowEntStart = count($rows) + 1;
        $entTotal = array_sum($this->entityDist) ?: 1;
        $entityLabels = [
            'mahasiswa' => 'Mahasiswa',
            'dosen' => 'Dosen',
            'tendik' => 'Tenaga Kependidikan (Tendik)',
            'karyawan' => 'Karyawan',
            'superuser' => 'Super User / Admin',
            'tamu' => 'Tamu',
            'lainnya' => 'Lainnya',
        ];
        foreach ($entityLabels as $key => $label) {
            $cnt = (int) ($this->entityDist[$key] ?? 0);
            $rows[] = [$label, $cnt, $this->pct($cnt, $entTotal)];
        }
        $this->rowEntEnd = count($rows);
        $rows[] = ['TOTAL', (int) array_sum($this->entityDist), '100%'];
        $rows[] = [''];

        // ── KINERJA STAF ──
        $this->rowStaffSection = count($rows) + 1;
        $rows[] = ['KINERJA PETUGAS HELPDESK'];
        $this->rowStaffHeader = count($rows) + 1;
        $rows[] = ['No', 'Nama Petugas', 'Ditugaskan', 'Selesai', 'Ditolak',
            'Tingkat Selesai', 'Rata-rata Waktu', 'Rating ⭐', 'Skor CSI (%)'];

        $this->rowStaffStart = count($rows) + 1;
        foreach ($this->staffData as $idx => $s) {
            $rows[] = [
                $idx + 1,
                $s['name'],
                (int) $s['assigned'],
                (int) $s['done'],
                (int) $s['reject'],
                $s['rate'].'%',
                $s['avg_time'], // Ini sudah berupa string format "5 jam 20 menit"
                $s['star'],
                $s['csi'].'%',
            ];
        }
        $this->rowStaffEnd = count($rows);
        $rows[] = [''];

        $this->rowFooter = count($rows) + 1;
        $rows[] = ['Catatan: Laporan ini digenerate otomatis oleh Sistem Helpdesk TIK UNILA.'];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $this->rowFooter;
                $maxCol = 'I';

                // ── Helper styles ──────────────────────────────────────
                $sectionStyle = fn (string $hex) => [
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $hex]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1, 'vertical' => Alignment::VERTICAL_CENTER],
                ];
                $headerStyle = fn (string $hex) => [
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $hex]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
                ];
                $dataRowStyle = fn (bool $even) => [
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $even ? 'F9FAFB' : 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
                ];

                // ── HEADER ROWS 1-4 ────────────────────────────────────
                $sheet->mergeCells("A1:{$maxCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(34);

                $sheet->mergeCells("A2:{$maxCol}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(22);

                $sheet->mergeCells("A3:{$maxCol}3");
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1E40AF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells("A4:{$maxCol}4");
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '9CA3AF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── STATISTIK TIKET ────────────────────────────────────
                $rs = $this->rowStatSection;
                // PERBAIKAN: Header section di merge cuma dari A ke C
                $sheet->mergeCells("A{$rs}:C{$rs}");
                $sheet->getStyle("A{$rs}:C{$rs}")->applyFromArray($sectionStyle('059669'));
                $sheet->getRowDimension($rs)->setRowHeight(22);

                $rsh = $this->rowStatHeader;
                $sheet->getStyle("A{$rsh}:C{$rsh}")->applyFromArray($headerStyle('065F46'));
                $sheet->getRowDimension($rsh)->setRowHeight(22);

                for ($r = $this->rowStatStart; $r <= $this->rowStatEnd; $r++) {
                    $even = ($r - $this->rowStatStart) % 2 === 1;
                    $sheet->getStyle("A{$r}:C{$r}")->applyFromArray($dataRowStyle($even));
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("A{$r}")->getFont()->setBold(
                        in_array($sheet->getCell("A{$r}")->getValue(), ['Total Tiket Masuk', 'Tingkat Penyelesaian', 'Tingkat Penolakan'])
                    );
                }

                // ── ENTITAS ────────────────────────────────────────────
                $re = $this->rowEntSection;
                // PERBAIKAN: Header section di merge cuma dari A ke C
                $sheet->mergeCells("A{$re}:C{$re}");
                $sheet->getStyle("A{$re}:C{$re}")->applyFromArray($sectionStyle('7C3AED'));
                $sheet->getRowDimension($re)->setRowHeight(22);

                $reh = $this->rowEntHeader;
                $sheet->getStyle("A{$reh}:C{$reh}")->applyFromArray($headerStyle('4C1D95'));
                $sheet->getRowDimension($reh)->setRowHeight(22);

                for ($r = $this->rowEntStart; $r <= $this->rowEntEnd; $r++) {
                    $even = ($r - $this->rowEntStart) % 2 === 1;
                    $sheet->getStyle("A{$r}:C{$r}")->applyFromArray($dataRowStyle($even));
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Entity TOTAL row
                $rowET = $this->rowEntEnd + 1;
                $sheet->getStyle("A{$rowET}:C{$rowET}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("A{$rowET}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // ── KINERJA STAF ───────────────────────────────────────
                $rstf = $this->rowStaffSection;
                $sheet->mergeCells("A{$rstf}:{$maxCol}{$rstf}");
                $sheet->getStyle("A{$rstf}:{$maxCol}{$rstf}")->applyFromArray($sectionStyle('B45309'));
                $sheet->getRowDimension($rstf)->setRowHeight(22);

                $rsth = $this->rowStaffHeader;
                $sheet->getStyle("A{$rsth}:{$maxCol}{$rsth}")->applyFromArray($headerStyle('78350F'));
                $sheet->getRowDimension($rsth)->setRowHeight(28);

                for ($r = $this->rowStaffStart; $r <= $this->rowStaffEnd; $r++) {
                    $even = ($r - $this->rowStaffStart) % 2 === 1;
                    $sheet->getStyle("A{$r}:{$maxCol}{$r}")->applyFromArray($dataRowStyle($even));
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // ── FOOTER ─────────────────────────────────────────────
                $rf = $this->rowFooter;
                $sheet->mergeCells("A{$rf}:{$maxCol}{$rf}");
                $sheet->getStyle("A{$rf}")->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '9CA3AF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── OUTER BORDER entire sheet ──────────────────────────
                $sheet->getStyle("A1:{$maxCol}{$lastRow}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E40AF']]],
                ]);
            },
        ];
    }

    private function pct(int $val, int $total): string
    {
        if ($total <= 0) {
            return '0%';
        }

        return round(($val / $total) * 100, 1).'%';
    }
}
