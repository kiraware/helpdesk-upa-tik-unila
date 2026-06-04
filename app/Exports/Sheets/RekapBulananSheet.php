<?php

namespace App\Exports\Sheets;

use App\Models\Service;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RekapBulananSheet implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    protected array $rowMap = [];

    public function __construct(
        protected Carbon $startDate,
        protected Carbon $endDate,
        protected $tickets,
    ) {}

    public function title(): string
    {
        return '6. Rekap Bulanan';
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 30];
    }

    public function array(): array
    {
        $services = Service::all();
        $year = $this->startDate->year;
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $dataT1 = [];
        $grandTotalsT1 = array_fill(1, 12, 0);
        $grandTotalsT1['total'] = 0;

        foreach ($services as $service) {
            $dataT1[$service->id] = array_fill(1, 12, 0);
            $dataT1[$service->id]['total'] = 0;
        }

        foreach ($this->tickets as $ticket) {
            $m = $ticket->created_at->month;
            $sId = $ticket->service_id;

            if (! isset($dataT1[$sId])) {
                continue;
            }

            $dataT1[$sId][$m]++;
            $dataT1[$sId]['total']++;
            $grandTotalsT1[$m]++;
            $grandTotalsT1['total']++;
        }

        $rows = [];

        $rows[] = ['REKAPITULASI TIKET BULANAN']; // Baris 1: Judul Utama
        $rows[] = [$this->startDate->format('d F Y').' s.d. '.$this->endDate->format('d F Y')]; // Baris 2: Sub-judul
        $rows[] = []; // Baris 3: Spasi Kosong

        $headerT1 = ['No', 'Layanan'];
        foreach ($months as $mName) {
            $headerT1[] = $mName;
        }
        $headerT1[] = 'Ringkasan '.$year;

        $this->rowMap['t1_header'] = count($rows) + 1; // Baris 4: Header
        $rows[] = $headerT1;

        $this->rowMap['t1_start'] = count($rows) + 1; // Baris 5: Data dimulai
        $no = 1;
        foreach ($services as $service) {
            $row = [$no++, $service->name];
            for ($m = 1; $m <= 12; $m++) {
                $row[] = $dataT1[$service->id][$m];
            }
            $row[] = $dataT1[$service->id]['total'];
            $rows[] = $row;
        }

        $rowTotalT1 = ['', 'TOTAL'];
        for ($m = 1; $m <= 12; $m++) {
            $rowTotalT1[] = $grandTotalsT1[$m];
        }
        $rowTotalT1[] = $grandTotalsT1['total'];
        $rows[] = $rowTotalT1;
        $this->rowMap['t1_total'] = count($rows); // Baris terakhir (Total)

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:O1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->mergeCells('A2:O2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '065F46']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $t1HRow = 3;
                $sheet->getStyle('A3:O3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '064E3B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(30);

                $t1Start = 4;
                $t1End = $this->rowMap['t1_total'] - 1;

                for ($r = $t1Start; $r <= $t1End; $r++) {
                    $color = ($r % 2 === 0) ? 'ECFDF5' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:O{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1FAE5']]],
                    ]);
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                $sheet->getStyle("A{$t1End}:O{$t1End}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                ]);

                $sheet->getStyle("A3:O{$t1End}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '065F46']]],
                ]);

                $sheet->freezePane('C4');
            },
        ];
    }
}
