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

class DetailTiketSheet implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    public function __construct(
        protected Carbon $startDate,
        protected Carbon $endDate,
        protected $tickets,
    ) {}

    public function title(): string
    {
        return '7. Detail Tiket';
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = ['DETAIL SELURUH TIKET'];
        $rows[] = [$this->startDate->format('d F Y').' s.d. '.$this->endDate->format('d F Y')];
        $rows[] = [];
        $rows[] = [
            'No', 'Kode Tiket', 'Tanggal Masuk', 'Nama Pemohon', 'No. Identitas', 'Tamu?', 'Fakultas/Unit Kerja', 'Entitas',
            'Layanan', 'Petugas', 'Prioritas', 'Status',
            'Tanggal Ditugaskan', 'Tanggal Selesai', 'Durasi',
            'Skor Kepuasan (%)', 'Saran',
        ];

        foreach ($this->tickets as $idx => $t) {
            // --- LOGIKA DURASI ---
            $durationStr = '-';
            if ($t->assigned_at && $t->closed_at) {
                $diffMinutes = $t->assigned_at->diffInMinutes($t->closed_at);

                if ($diffMinutes > 0) {
                    $days = floor($diffMinutes / 1440);
                    $hours = floor(($diffMinutes % 1440) / 60);
                    $mins = $diffMinutes % 60;

                    $parts = [];
                    if ($days > 0) {
                        $parts[] = "{$days} hari";
                    }
                    if ($hours > 0) {
                        $parts[] = "{$hours} jam";
                    }
                    if ($mins > 0) {
                        $parts[] = "{$mins} menit";
                    }

                    $durationStr = implode(' ', $parts) ?: '0 menit';
                } else {
                    $durationStr = '0 menit';
                }
            }

            // Determine name
            $name = $t->user
                ? $t->user->name
                : ($t->guestDetail ? $t->guestDetail->full_name : 'Tamu');

            // Determine identity number & guest flag
            $identityNumber = '-';
            $isGuest = 'Tidak';
            if ($t->user) {
                $identityNumber = $t->user->identity_number ?? '-';
            } elseif ($t->guestDetail) {
                $identityNumber = $t->guestDetail->identity_number ?? '-';
                $isGuest = 'Ya';
            }

            // Determine faculty / work unit
            $department = '-';
            if ($t->user) {
                $department = $t->user->department?->name ?? '-';
            } elseif ($t->guestDetail) {
                $department = $t->guestDetail->department?->name ?? '-';
            }

            // Determine entity
            $entity = 'Lainnya';
            if ($t->user) {
                $entity = match ($t->user->entity?->value ?? '') {
                    'mahasiswa' => 'Mahasiswa',
                    'dosen' => 'Dosen',
                    'tendik' => 'Tendik',
                    'karyawan' => 'Karyawan',
                    'superuser' => 'Super User',
                    'tamu' => 'Tamu',
                    default => 'Lainnya',
                };
            } elseif ($t->guestDetail) {
                $entity = match ($t->guestDetail->entity_type?->value ?? '') {
                    'mahasiswa' => 'Mahasiswa',
                    'dosen' => 'Dosen',
                    'tendik' => 'Tendik',
                    default => 'Lainnya',
                };
            }

            // --- LOGIKA SKOR KEPUASAN PER TIKET ---
            // Catatan: Ini BUKAN CSI metodologis (yang membutuhkan agregasi banyak responden).
            // Ini adalah skor kepuasan tertimbang per-tiket tunggal:
            //   = Σ(satisfaction × importance) / (Σimportance × 5) × 100
            // Cukup disebut "Skor Kepuasan (%)" agar tidak menyesatkan secara metodologis.
            $csiScore = '-';
            $feedback = '-';

            if ($t->survey) {
                $feedback = $t->survey->feedback ?? '-';

                if ($t->survey->csi_score !== null) {
                    $csiScore = round((float) $t->survey->csi_score, 2);
                } elseif ($t->survey->answers && $t->survey->answers->count() > 0) {
                    $wScore = 0;
                    $imp = 0;
                    foreach ($t->survey->answers as $ans) {
                        if ($ans->satisfaction_score === null || $ans->importance_score === null) {
                            continue;
                        }
                        $wScore += $ans->satisfaction_score * $ans->importance_score;
                        $imp += $ans->importance_score;
                    }
                    $csiScore = $imp > 0 ? round(($wScore / $imp / 5) * 100, 2) : '-';
                }
            }

            $rows[] = [
                $idx + 1,
                '#'.$t->ticket_code,
                $t->created_at->format('d/m/Y H:i'),
                $name,
                $identityNumber,
                $isGuest,
                $department,
                $entity,
                $t->service->name ?? '-',
                $t->assignee->name ?? 'Belum Ditugaskan',
                $t->priority->name ?? '-',
                $t->status->name ?? '-',
                $t->assigned_at ? $t->assigned_at->format('d/m/Y H:i') : '-',
                $t->closed_at ? $t->closed_at->format('d/m/Y H:i') : '-',
                $durationStr,
                $csiScore,
                $feedback,
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Baris 1: Judul (A–Q = 17 kolom)
                $sheet->mergeCells('A1:Q1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Baris 2: Sub-judul periode
                $sheet->mergeCells('A2:Q2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '065F46']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris 3: Header kolom
                $sheet->getStyle('A3:Q3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '064E3B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(30);

                // Baris data
                $highestRow = $sheet->getHighestRow();
                $dataStart = 4;
                $dataEnd = $highestRow;

                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    $color = ($r % 2 === 0) ? 'ECFDF5' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:Q{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1FAE5']]],
                    ]);
                    // Nama (D) rata kiri
                    $sheet->getStyle("D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    // No. Identitas (E) rata kiri
                    $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    // Fakultas/Unit Kerja (G) rata kiri
                    $sheet->getStyle("G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    // Layanan (I) & Petugas (J) rata kiri
                    $sheet->getStyle("I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("J{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    // Saran (Q) rata kiri + wrap
                    $sheet->getStyle("Q{$r}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setWrapText(true);
                }

                // Border luar tabel
                $sheet->getStyle("A4:Q{$dataEnd}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '065F46']]],
                ]);

                $sheet->freezePane('C4');

                // Auto-filter: Tamu?(F) s.d. Status(L)
                $sheet->setAutoFilter('F3:L3');
            },
        ];
    }
}
