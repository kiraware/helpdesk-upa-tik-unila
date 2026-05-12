<?php

namespace App\Exports;

use App\Enums\IdentityType;
use App\Enums\TicketStatus;
use App\Enums\UserEntity;
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

        $emptyEntities = [
            'mahasiswa' => 0,
            'dosen' => 0,
            'tendik' => 0,
            'karyawan' => 0,
            'superuser' => 0,
            'tamu' => 0,
            'lainnya' => 0,
        ];

        // Siapkan kerangka array per layanan
        foreach ($services as $service) {
            $reportData[$service->id] = [
                'name' => $service->name,
                'total' => 0,
                'done' => 0,
                'reject' => 0,
                'entities' => $emptyEntities,
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
            $entityCode = 'lainnya';
            if ($ticket->user) {
                $entityCode = match ($ticket->user->entity) {
                    UserEntity::MAHASISWA => 'mahasiswa',
                    UserEntity::DOSEN => 'dosen',
                    UserEntity::TENDIK => 'tendik',
                    UserEntity::KARYAWAN => 'karyawan',
                    UserEntity::SUPER_USER => 'superuser',
                    UserEntity::TAMU => 'tamu',
                    default => 'lainnya',
                };
            } elseif ($ticket->guestDetail) {
                $entityCode = match ($ticket->guestDetail->entity_type) {
                    IdentityType::MAHASISWA => 'mahasiswa',
                    IdentityType::DOSEN => 'dosen',
                    IdentityType::TENDIK => 'tendik',
                    default => 'lainnya',
                };
            }
            $reportData[$ticket->service_id]['entities'][$entityCode]++;
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
