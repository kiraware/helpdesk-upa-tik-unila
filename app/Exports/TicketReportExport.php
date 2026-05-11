<?php

namespace App\Exports;

use App\Enums\TicketStatus;
use App\Models\Service;
use App\Models\Ticket;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TicketReportExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $startDate;

    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        // Eager load data tiket beserta relasinya
        $tickets = Ticket::with(['service', 'user', 'guestDetail', 'assignee'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();

        $services = Service::all();
        $reportData = [];

        // Siapkan kerangka array per layanan
        foreach ($services as $service) {
            $reportData[$service->id] = [
                'name' => $service->name,
                'total' => 0, 'done' => 0, 'reject' => 0,
                'T' => 0, 'D' => 0, 'M' => 0, 'L' => 0,
            ];
        }

        // Isi data ke array
        foreach ($tickets as $ticket) {
            if (! isset($reportData[$ticket->service_id])) {
                continue;
            }

            // Hitung total dan status
            $reportData[$ticket->service_id]['total']++;
            if ($ticket->status === TicketStatus::DONE) {
                $reportData[$ticket->service_id]['done']++;
            }
            if ($ticket->status === TicketStatus::REJECT) {
                $reportData[$ticket->service_id]['reject']++;
            }

            // Hitung entitas pengguna
            $entityCode = 'L'; // Default: Lainnya
            if ($ticket->user) {
                $e = strtolower($ticket->user->entity->value ?? '');
                if (in_array($e, ['karyawan', 'tendik'])) {
                    $entityCode = 'T';
                } elseif ($e === 'dosen') {
                    $entityCode = 'D';
                } elseif ($e === 'mahasiswa') {
                    $entityCode = 'M';
                }
            } elseif ($ticket->guestDetail) {
                $e = strtolower($ticket->guestDetail->entity_type->value ?? '');
                if ($e === 'tendik') {
                    $entityCode = 'T';
                } elseif ($e === 'dosen') {
                    $entityCode = 'D';
                } elseif ($e === 'mahasiswa') {
                    $entityCode = 'M';
                }
            }
            $reportData[$ticket->service_id][$entityCode]++;
        }

        // Urutkan dari total tiket terbanyak
        usort($reportData, fn ($a, $b) => $b['total'] <=> $a['total']);

        // Lempar data ke view excel.blade.php
        return view('reports.excel', [
            'reportData' => $reportData,
            'tickets' => $tickets,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }

    // Styling khusus untuk Excel (Opsional, agar header tebal)
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
