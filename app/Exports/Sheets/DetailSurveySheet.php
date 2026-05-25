<?php

namespace App\Exports\Sheets;

use App\Models\SurveyQuestion;
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

class DetailSurveySheet implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    /** Daftar pertanyaan aktif, diambil sekali dan di-cache */
    private array $questions = [];

    /** Jumlah kolom total (dihitung setelah questions di-load) */
    private int $totalCols = 0;

    /** Baris data terakhir (sebelum total) */
    private int $lastDataRow = 4;

    /** Jumlah tiket bersurvei */
    private int $surveyedCount = 0;

    // Kolom-kolom tetap di kiri
    private const FIXED_COLS = 6; // No, Kode Tiket, Tanggal, Pemohon, Layanan, Petugas

    // Kolom-kolom tetap di kanan (setelah semua aspek)
    private const TAIL_COLS = 3; // CSI, Rating Bintang, Saran

    /**
     * Warna aksen bergantian per aspek (siklus 5 warna).
     *
     * Kunci:
     *  - header     : gelap untuk baris-3 nama aspek (teks putih)
     *  - sat_header : medium untuk baris-4 kolom Kepuasan (teks putih, lebih terang dari header)
     *  - imp_header : terang untuk baris-4 kolom Kepentingan (teks gelap)
     *  - sat        : baris data Kepuasan (lebih gelap dari imp, agar terbedakan)
     *  - imp        : baris data Kepentingan (paling terang)
     */
    private array $aspectColors = [
        // Hijau
        ['header' => '065F46', 'sat_header' => '059669', 'imp_header' => 'D1FAE5', 'sat' => 'A7F3D0', 'imp' => 'ECFDF5'],
        // Biru
        ['header' => '1E40AF', 'sat_header' => '2563EB', 'imp_header' => 'DBEAFE', 'sat' => 'BFDBFE', 'imp' => 'EFF6FF'],
        // Ungu
        ['header' => '6D28D9', 'sat_header' => '7C3AED', 'imp_header' => 'EDE9FE', 'sat' => 'DDD6FE', 'imp' => 'F5F3FF'],
        // Amber
        ['header' => '92400E', 'sat_header' => 'B45309', 'imp_header' => 'FEF3C7', 'sat' => 'FDE68A', 'imp' => 'FFFBEB'],
        // Pink/Magenta
        ['header' => '9D174D', 'sat_header' => 'BE185D', 'imp_header' => 'FCE7F3', 'sat' => 'FBCFE8', 'imp' => 'FDF2F8'],
    ];

    public function __construct(
        protected Carbon $startDate,
        protected Carbon $endDate,
        protected $tickets,
    ) {
        // Load pertanyaan aktif urut sort_order — sama seperti yang ditampilkan ke pengguna
        $this->questions = SurveyQuestion::active()->get()->all();
        $this->totalCols = self::FIXED_COLS + (count($this->questions) * 2) + self::TAIL_COLS;
    }

    public function title(): string
    {
        return '8. Detail Survei';
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 5,   // No
            'B' => 14,  // Kode Tiket
            'C' => 16,  // Tanggal Tiket
            'D' => 24,  // Nama Pemohon
            'E' => 26,  // Layanan
            'F' => 22,  // Petugas
        ];

        // Setiap pasang kolom aspek: Kepuasan | Kepentingan (masing-masing lebar 36)
        $colIdx = self::FIXED_COLS + 1;
        foreach ($this->questions as $q) {
            $widths[Coordinate::stringFromColumnIndex($colIdx)] = 36; // Kepuasan
            $widths[Coordinate::stringFromColumnIndex($colIdx + 1)] = 36; // Kepentingan
            $colIdx += 2;
        }

        // Kolom ekor
        $widths[Coordinate::stringFromColumnIndex($colIdx)] = 13; // CSI (%)
        $widths[Coordinate::stringFromColumnIndex($colIdx + 1)] = 12; // Rating Bintang
        $widths[Coordinate::stringFromColumnIndex($colIdx + 2)] = 36; // Saran

        return $widths;
    }

    public function array(): array
    {
        $rows = [];

        // ── Baris 1: Judul ───────────────────────────────────────────
        $rows[] = $this->emptyRow('DETAIL JAWABAN SURVEI KEPUASAN');

        // ── Baris 2: Sub-judul periode ───────────────────────────────
        $rows[] = $this->emptyRow(
            $this->startDate->format('d F Y').' s.d. '.$this->endDate->format('d F Y')
        );

        // ── Baris 3: Header baris-1 ──────────────────────────────────
        // Kolom tetap kiri (A–F): label, akan di-merge ke bawah (baris 3–4)
        $header1 = ['No', 'Kode Tiket', 'Tanggal Tiket', 'Nama Pemohon', 'Layanan', 'Petugas'];

        // Nama aspek (akan di-merge 2 kolom ke kanan)
        foreach ($this->questions as $q) {
            $header1[] = $q->aspect_name; // kolom pertama aspek (akan di-merge)
            $header1[] = '';              // kolom kedua aspek (di-merge, dikosongkan)
        }

        // Kolom tetap kanan (akan di-merge ke bawah)
        $header1[] = 'Skor Kepuasan (%)';
        $header1[] = 'Rating Bintang';
        $header1[] = 'Saran / Feedback';
        $rows[] = $header1;

        // ── Baris 4: Header baris-2 (sub-header) ────────────────────
        // Kolom tetap kiri dikosongkan (karena di-merge ke bawah di registerEvents)
        $header2 = array_fill(0, self::FIXED_COLS, '');

        // Per aspek: Kepuasan | Kepentingan
        foreach ($this->questions as $q) {
            $header2[] = $q->satisfaction_question;
            $header2[] = $q->importance_question;
        }

        // Kolom tetap kanan dikosongkan (di-merge ke bawah)
        $header2[] = '';
        $header2[] = '';
        $header2[] = '';
        $rows[] = $header2;

        // ── Baris 5+: Data ───────────────────────────────────────────
        $no = 1;
        foreach ($this->tickets as $ticket) {
            if (! $ticket->survey) {
                continue;
            }

            $survey = $ticket->survey;
            $answers = $survey->answers ?? collect();

            if ($answers->isEmpty()) {
                continue;
            }

            $this->surveyedCount++;

            // Info tiket
            $requesterName = $ticket->user
                ? $ticket->user->name
                : ($ticket->guestDetail ? $ticket->guestDetail->full_name : 'Tamu');

            $serviceName = $ticket->service->name ?? '-';
            $assigneeName = $ticket->assignee->name ?? 'Belum Ditugaskan';
            $ticketDate = $ticket->created_at->format('d/m/Y H:i');
            $ticketCode = '#'.$ticket->ticket_code;
            $feedback = $survey->feedback ?? '-';
            $starRating = $survey->overall_rating ?? '-';

            // CSI per tiket
            $csiScore = '-';
            if ($survey->csi_score !== null) {
                $csiScore = round((float) $survey->csi_score, 2);
            } else {
                $wScore = 0;
                $imp = 0;
                foreach ($answers as $ans) {
                    if ($ans->satisfaction_score === null || $ans->importance_score === null) {
                        continue;
                    }
                    $wScore += $ans->satisfaction_score * $ans->importance_score;
                    $imp += $ans->importance_score;
                }
                $csiScore = $imp > 0 ? round(($wScore / $imp / 5) * 100, 2) : '-';
            }

            // Index jawaban by question_id untuk lookup O(1)
            $answerMap = [];
            foreach ($answers as $ans) {
                $answerMap[$ans->survey_question_id] = $ans;
            }

            // Bangun baris: info tiket + skor per pertanyaan (urut sort_order) + CSI + bintang + saran
            $row = [
                $no++,
                $ticketCode,
                $ticketDate,
                $requesterName,
                $serviceName,
                $assigneeName,
            ];

            foreach ($this->questions as $q) {
                $ans = $answerMap[$q->id] ?? null;
                $row[] = $ans->satisfaction_score ?? '-'; // Kepuasan
                $row[] = $ans->importance_score ?? '-'; // Kepentingan
            }

            $row[] = $csiScore;
            $row[] = $starRating;
            $row[] = $feedback;

            $rows[] = $row;
        }

        $this->lastDataRow = count($rows) + 1; // +1 untuk baris total

        // ── Baris total ───────────────────────────────────────────────
        // Label + count digabung di kolom B agar kolom C..maxCol aman di-merge
        // tanpa menghilangkan nilai surveyedCount.
        $totalRow = ['', "TOTAL TIKET DISURVEI: {$this->surveyedCount}"];
        for ($i = 2; $i < $this->totalCols; $i++) {
            $totalRow[] = '';
        }
        $rows[] = $totalRow;

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $maxCol = Coordinate::stringFromColumnIndex($this->totalCols);
                $highestRow = $sheet->getHighestRow();
                $totalRow = $highestRow;
                $dataStart = 5;      // data mulai baris 5
                $dataEnd = $totalRow - 1;

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

                // ── Header 2 baris (baris 3 & 4) ────────────────────────

                // Style dasar untuk seluruh area header (baris 3–4)
                $sheet->getStyle("A3:{$maxCol}4")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '064E3B']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0F766E']]],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(22);
                $sheet->getRowDimension(4)->setRowHeight(72); // tinggi untuk wrap teks pertanyaan panjang

                // Merge kolom tetap kiri (A–F) secara vertikal (baris 3–4)
                foreach (range(1, self::FIXED_COLS) as $ci) {
                    $col = Coordinate::stringFromColumnIndex($ci);
                    $sheet->mergeCells("{$col}3:{$col}4");
                }

                // Merge kolom tetap kanan (CSI, Bintang, Saran) secara vertikal (baris 3–4)
                $tailStart = self::FIXED_COLS + (count($this->questions) * 2) + 1;
                foreach (range($tailStart, $this->totalCols) as $ci) {
                    $col = Coordinate::stringFromColumnIndex($ci);
                    $sheet->mergeCells("{$col}3:{$col}4");
                }

                // Warna kolom ekor pada header
                $tailColors = [
                    Coordinate::stringFromColumnIndex($tailStart) => '10B981', // CSI — hijau
                    Coordinate::stringFromColumnIndex($tailStart + 1) => 'F59E0B', // Bintang — amber
                    Coordinate::stringFromColumnIndex($tailStart + 2) => '6B7280', // Saran — abu
                ];
                foreach ($tailColors as $col => $hex) {
                    $sheet->getStyle("{$col}3:{$col}4")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $hex]],
                    ]);
                }

                // Merge & warnai setiap pasang kolom aspek (baris 3 = nama aspek, baris 4 = sub-pertanyaan)
                $colIdx = self::FIXED_COLS + 1;
                $aspectIdx = 0;
                foreach ($this->questions as $q) {
                    $colSat = Coordinate::stringFromColumnIndex($colIdx);
                    $colImp = Coordinate::stringFromColumnIndex($colIdx + 1);

                    $colors = $this->aspectColors[$aspectIdx % count($this->aspectColors)];

                    // Baris 3: merge 2 kolom untuk nama aspek
                    $sheet->mergeCells("{$colSat}3:{$colImp}3");
                    $sheet->getStyle("{$colSat}3")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colors['header']]],
                        'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    ]);

                    // Baris 4: 2 kolom terpisah untuk teks pertanyaan Kepuasan & Kepentingan
                    // Baris 4 kolom Kepuasan: warna medium (sat_header) + teks putih
                    // Lebih terang dari header baris-3, tapi tetap gelap cukup agar
                    // bisa dibedakan dari kolom Kepentingan di sebelahnya.
                    $sheet->getStyle("{$colSat}4")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colors['sat_header']]],
                        'font' => ['italic' => true, 'bold' => false, 'size' => 8, 'color' => ['rgb' => 'FFFFFF']],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_TOP,
                            'wrapText' => true,
                        ],
                    ]);
                    // Baris 4 kolom Kepentingan: warna terang (imp_header) + teks gelap
                    // Kontras jelas terhadap kolom Kepuasan di sebelah kiri.
                    $sheet->getStyle("{$colImp}4")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colors['imp_header']]],
                        'font' => ['italic' => true, 'bold' => false, 'size' => 8, 'color' => ['rgb' => '1F2937']],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_TOP,
                            'wrapText' => true,
                        ],
                    ]);

                    $colIdx += 2;
                    $aspectIdx += 1;
                }

                // ── Baris data ──────────────────────────────────────────
                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    $baseColor = ($r % 2 === 0) ? 'ECFDF5' : 'FFFFFF';

                    $sheet->getStyle("A{$r}:{$maxCol}{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $baseColor]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1FAE5']]],
                    ]);

                    // Kolom teks → rata kiri
                    foreach (['D', 'E', 'F'] as $col) {
                        $sheet->getStyle("{$col}{$r}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }

                    // Saran → rata kiri + wrap
                    $sarCol = Coordinate::stringFromColumnIndex($this->totalCols);
                    $sheet->getStyle("{$sarCol}{$r}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setWrapText(true);

                    // Highlight warna tiap pasang kolom aspek
                    $ci2 = self::FIXED_COLS + 1;
                    $ai2 = 0;
                    foreach ($this->questions as $q) {
                        $colors = $this->aspectColors[$ai2 % count($this->aspectColors)];
                        $colSat2 = Coordinate::stringFromColumnIndex($ci2);
                        $colImp2 = Coordinate::stringFromColumnIndex($ci2 + 1);

                        $sheet->getStyle("{$colSat2}{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colors['sat']]],
                            'font' => ['bold' => true],
                        ]);
                        $sheet->getStyle("{$colImp2}{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colors['imp']]],
                        ]);

                        $ci2 += 2;
                        $ai2 += 1;
                    }

                    // Kolom Skor Kepuasan (%) — highlight hijau + bold
                    $csiCol = Coordinate::stringFromColumnIndex($tailStart);
                    $sheet->getStyle("{$csiCol}{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '065F46']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => ($r % 2 === 0) ? 'D1FAE5' : 'ECFDF5']],
                    ]);

                    // Kolom Rating Bintang — highlight amber ringan agar konsisten dengan header
                    $starCol = Coordinate::stringFromColumnIndex($tailStart + 1);
                    $sheet->getStyle("{$starCol}{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '92400E']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => ($r % 2 === 0) ? 'FDE68A' : 'FEF3C7']],
                    ]);
                }

                // ── Baris TOTAL ─────────────────────────────────────────
                // Kolom A = kosong, B = label + count (tidak di-merge).
                // Merge mulai C sampai maxCol agar nilai di B tetap visible.
                $sheet->mergeCells("C{$totalRow}:{$maxCol}{$totalRow}");
                $sheet->getStyle("A{$totalRow}:{$maxCol}{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1FAE5']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle("B{$totalRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // ── Border luar tabel ───────────────────────────────────
                $sheet->getStyle("A3:{$maxCol}{$totalRow}")->applyFromArray([
                    'borders' => [
                        'outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '065F46']],
                    ],
                ]);

                // ── Auto-filter ─────────────────────────────────────────
                // Filter pada kolom Layanan (E) dan Petugas (F) di baris header bawah (4)
                $sheet->setAutoFilter('E4:F4');

                // ── Freeze pane: A–F tetap, scroll mulai kolom G, baris 5 ─
                $sheet->freezePane('C5');
            },
        ];
    }

    /** Helper: buat baris dengan nilai di kolom pertama, sisanya kosong */
    private function emptyRow(string $firstValue): array
    {
        $row = array_fill(0, $this->totalCols, '');
        $row[0] = $firstValue;

        return $row;
    }
}
