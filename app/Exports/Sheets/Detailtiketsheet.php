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
        return '4. Detail Tiket';
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = ['DETAIL SELURUH TIKET'];
        $rows[] = [$this->startDate->format('d F Y').' s.d. '.$this->endDate->format('d F Y')];
        $rows[] = [];
        $rows[] = [
            'No', 'Kode Tiket', 'Tanggal Masuk', 'Nama Pemohon', 'Entitas',
            'Layanan', 'Petugas', 'Prioritas', 'Status',
            'Tgl Ditugaskan', 'Tgl Selesai', 'Durasi (Jam)',
        ];

        foreach ($this->tickets as $idx => $t) {
            $duration = 0;
            if ($t->assigned_at && $t->closed_at) {
                $duration = $t->assigned_at->diffInHours($t->closed_at);
            }

            // Determine name
            $name = $t->user ? $t->user->name
                : ($t->guestDetail ? $t->guestDetail->full_name : 'Tamu');

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
                    'mahasiswa' => 'Mahasiswa (Tamu)',
                    'dosen' => 'Dosen (Tamu)',
                    'tendik' => 'Tendik (Tamu)',
                    default => 'Tamu',
                };
            }

            $rows[] = [
                $idx + 1,
                '#'.$t->ticket_code,
                $t->created_at->format('d/m/Y H:i'),
                $name,
                $entity,
                $t->service->name ?? '-',
                $t->assignee->name ?? 'Belum Ditugaskan',
                $t->priority->name ?? '-',
                $t->status->name ?? '-',
                $t->assigned_at ? $t->assigned_at->format('d/m/Y H:i') : '-',
                $t->closed_at ? $t->closed_at->format('d/m/Y H:i') : '-',
                $duration,
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:L1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->mergeCells('A2:L2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '065F46']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle('A4:L4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '064E3B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(30);

                $totalRows = count($this->tickets);
                $dataStart = 5;
                $dataEnd = $dataStart + $totalRows - 1;

                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    $color = ($r % 2 === 0) ? 'ECFDF5' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:L{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1FAE5']]],
                    ]);
                    // Name column left-aligned
                    $sheet->getStyle("D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                $sheet->freezePane('A5');

                // Auto-filter on header
                $sheet->setAutoFilter('A4:L4');
            },
        ];
    }
}
