<?php

namespace App\Exports;

use App\Enums\IdentityType;
use App\Enums\TicketStatus;
use App\Enums\UserEntity;
use App\Enums\UserRole;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TicketReportExport implements WithMultipleSheets
{
    use Exportable;

    protected Carbon $startDate;

    protected Carbon $endDate;

    protected string $period;

    public function __construct(Carbon $startDate, Carbon $endDate, string $period = 'custom')
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->period = $period;
    }

    public function sheets(): array
    {
        [$tickets, $reportData, $grandTotals, $entityDist, $staffData, $monthlyData] = $this->prepareData();

        return [
            new Sheets\RingkasanSheet($this->startDate, $this->endDate, $this->period, $grandTotals, $entityDist, $staffData),
            new Sheets\RekapLayananSheet($this->startDate, $this->endDate, $reportData, $grandTotals),
            new Sheets\TrendBulananSheet($this->startDate, $this->endDate, $monthlyData),
            new Sheets\DetailTiketSheet($this->startDate, $this->endDate, $tickets),
        ];
    }

    private function prepareData(): array
    {
        $tickets = Ticket::with(['service', 'user', 'guestDetail', 'assignee'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $services = Service::all();

        $emptyEntities = [
            'mahasiswa' => 0, 'dosen' => 0, 'tendik' => 0,
            'karyawan' => 0, 'superuser' => 0, 'tamu' => 0, 'lainnya' => 0,
        ];

        $reportData = [];
        $entityDist = $emptyEntities;
        $grandTotals = ['total' => 0, 'done' => 0, 'reject' => 0, 'waiting' => 0, 'progress' => 0] + $emptyEntities;
        $monthlyData = [];

        foreach ($services as $service) {
            $reportData[$service->id] = [
                'name' => $service->name,
                'total' => 0,
                'done' => 0,
                'reject' => 0,
                'waiting' => 0,
                'progress' => 0,
                'entities' => $emptyEntities,
            ];
        }

        foreach ($tickets as $ticket) {
            $serviceId = $ticket->service_id;
            if (isset($reportData[$serviceId])) {
                $reportData[$serviceId]['total']++;
                if ($ticket->status === TicketStatus::DONE) {
                    $reportData[$serviceId]['done']++;
                }
                if ($ticket->status === TicketStatus::REJECT) {
                    $reportData[$serviceId]['reject']++;
                }
                if ($ticket->status === TicketStatus::WAITING) {
                    $reportData[$serviceId]['waiting']++;
                }
                if ($ticket->status === TicketStatus::PROGRESS) {
                    $reportData[$serviceId]['progress']++;
                }
            }

            // Entity detection
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

            if (isset($reportData[$serviceId])) {
                $reportData[$serviceId]['entities'][$entityCode]++;
            }
            $entityDist[$entityCode]++;

            // Monthly trend
            $monthKey = $ticket->created_at->format('Y-m');
            if (! isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = ['label' => $ticket->created_at->format('F Y'), 'total' => 0, 'done' => 0, 'reject' => 0];
            }
            $monthlyData[$monthKey]['total']++;
            if ($ticket->status === TicketStatus::DONE) {
                $monthlyData[$monthKey]['done']++;
            }
            if ($ticket->status === TicketStatus::REJECT) {
                $monthlyData[$monthKey]['reject']++;
            }
        }

        ksort($monthlyData);
        usort($reportData, fn ($a, $b) => $b['total'] <=> $a['total']);

        foreach ($reportData as $svc) {
            $grandTotals['total'] += $svc['total'];
            $grandTotals['done'] += $svc['done'];
            $grandTotals['reject'] += $svc['reject'];
            $grandTotals['waiting'] += $svc['waiting'];
            $grandTotals['progress'] += $svc['progress'];
            foreach ($emptyEntities as $key => $_) {
                $grandTotals[$key] += $svc['entities'][$key];
            }
        }

        // Staff data
        $staffData = User::whereIn('role', [UserRole::ADMIN, UserRole::SUPERUSER])
            ->with(['assignedTickets' => function ($q) {
                $q->whereBetween('created_at', [$this->startDate, $this->endDate])
                    ->with('survey.answers');
            }])
            ->get()
            ->map(function ($user) {
                $ts = $user->assignedTickets;
                $total = $ts->count();
                $done = $ts->where('status', TicketStatus::DONE)->count();

                $times = $ts->whereNotNull('assigned_at')->whereNotNull('closed_at')
                    ->map(fn ($t) => $t->assigned_at->diffInHours($t->closed_at));

                $avgTime = $times->count() > 0 ? round($times->avg(), 1) : 0;
                $rate = $total > 0 ? round(($done / $total) * 100) : 0;

                $wScore = 0;
                $imp = 0;
                $surveys = 0;
                foreach ($ts as $ticket) {
                    if ($ticket->survey?->answers) {
                        $surveys++;
                        foreach ($ticket->survey->answers as $ans) {
                            $wScore += $ans->satisfaction_score * $ans->importance_score;
                            $imp += $ans->importance_score;
                        }
                    }
                }

                $star = $imp > 0 ? $wScore / $imp : 0;
                $csi = $imp > 0 ? ($star / 5) * 100 : 0;

                return [
                    'name' => $user->name,
                    'assigned' => $total,
                    'done' => $done,
                    'reject' => $ts->where('status', TicketStatus::REJECT)->count(),
                    'rate' => $rate,
                    'avg_time' => $avgTime,
                    'star' => round($star, 2),
                    'csi' => round($csi, 2),
                    'surveys' => $surveys,
                ];
            })
            ->sortByDesc('csi')
            ->values()
            ->toArray();

        return [$tickets, $reportData, $grandTotals, $entityDist, $staffData, $monthlyData];
    }
}
