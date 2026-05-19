<?php

namespace App\Exports;

use App\Enums\IdentityType;
use App\Enums\TicketStatus;
use App\Enums\UserEntity;
use App\Enums\UserRole;
use App\Helpers\OffHoursHelper;
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
        [$tickets, $reportData, $grandTotals, $entityDist, $staffData] = $this->prepareData();

        return [
            new Sheets\RekapLayananSheet($this->startDate, $this->endDate, $reportData, $grandTotals),
            new Sheets\KinerjaPetugasSheet($this->startDate, $this->endDate, $staffData),
            new Sheets\DistribusiEntitasSheet($this->startDate, $this->endDate, $entityDist),
            new Sheets\StatusTiketSheet($this->startDate, $this->endDate, $reportData, $grandTotals),
            new Sheets\PrioritasTiketSheet($this->startDate, $this->endDate, $tickets),
            new Sheets\RekapBulananSheet($this->startDate, $this->endDate, $tickets),
            new Sheets\DetailTiketSheet($this->startDate, $this->endDate, $tickets),
        ];
    }

    private function prepareData(): array
    {
        $tickets = Ticket::with(['service', 'user.department', 'guestDetail.department', 'assignee', 'survey.answers'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $services = Service::all();

        $emptyStatuses = [];
        foreach (TicketStatus::cases() as $status) {
            $emptyStatuses[$status->value] = 0;
        }

        $emptyEntities = [
            'mahasiswa' => 0, 'dosen' => 0, 'tendik' => 0,
            'karyawan' => 0, 'superuser' => 0, 'tamu' => 0, 'lainnya' => 0,
        ];

        $reportData = [];
        $entityDist = $emptyEntities;
        $grandTotals = [
            'total' => 0,
            'statuses' => $emptyStatuses,
            '_csi_wScore' => 0,
            '_csi_imp' => 0,
        ] + $emptyEntities;

        foreach ($services as $service) {
            $reportData[$service->id] = [
                'name' => $service->name,
                'total' => 0,
                'statuses' => $emptyStatuses,
                'entities' => $emptyEntities,
                '_csi_wScore' => 0,
                '_csi_imp' => 0,
                '_survey_count' => 0,
            ];
        }

        foreach ($tickets as $ticket) {
            $serviceId = $ticket->service_id;
            if (isset($reportData[$serviceId])) {
                $reportData[$serviceId]['total']++;
                if ($ticket->status) {
                    $reportData[$serviceId]['statuses'][$ticket->status->value]++;
                }
            }

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

                if ($ticket->survey && $ticket->survey->answers && $ticket->survey->answers->count() > 0) {
                    $reportData[$serviceId]['_survey_count']++;
                    foreach ($ticket->survey->answers as $ans) {
                        if ($ans->satisfaction_score === null || $ans->importance_score === null) {
                            continue;
                        }
                        $reportData[$serviceId]['_csi_wScore'] += ($ans->satisfaction_score * $ans->importance_score);
                        $reportData[$serviceId]['_csi_imp'] += $ans->importance_score;
                    }
                }
            }

            $entityDist[$entityCode]++;
        }

        foreach ($reportData as &$svc) {
            $imp = (float) $svc['_csi_imp'];
            $wScore = (float) $svc['_csi_wScore'];
            $svc['csi'] = $imp > 0 ? round(($wScore / $imp / 5) * 100, 2) : 0;
            $svc['survey_count'] = $svc['_survey_count'];
            unset($svc['_csi_wScore'], $svc['_csi_imp'], $svc['_survey_count']);
        }
        unset($svc);

        usort($reportData, fn ($a, $b) => $b['total'] <=> $a['total']);

        foreach ($reportData as $svc) {
            $grandTotals['total'] += $svc['total'];
            foreach ($emptyStatuses as $key => $_) {
                $grandTotals['statuses'][$key] += $svc['statuses'][$key];
            }
            foreach ($emptyEntities as $key => $_) {
                $grandTotals[$key] += $svc['entities'][$key];
            }
        }
        unset($grandTotals['_csi_wScore'], $grandTotals['_csi_imp']);

        // ── Staff data dengan off-hours bonus ──────────────────────────
        $staffRaw = User::whereIn('role', [UserRole::ADMIN, UserRole::SUPERUSER])
            ->with(['assignedTickets' => function ($q) {
                $q->whereBetween('created_at', [$this->startDate, $this->endDate])
                    ->with('survey.answers');
            }])
            ->get()
            ->map(function ($user) {
                $ts = $user->assignedTickets;
                $total = $ts->count();
                $done = $ts->where('status', TicketStatus::DONE)->count();
                $reject = $ts->where('status', TicketStatus::REJECT)->count();

                // Rata-rata waktu dalam menit (untuk string deskriptif di Excel)
                $times = $ts->whereNotNull('assigned_at')->whereNotNull('closed_at')
                    ->map(fn ($t) => $t->assigned_at->diffInMinutes($t->closed_at));

                $avgMinutes = $times->count() > 0 ? (int) round($times->avg()) : 0;
                $avgTimeStr = '0 menit';
                if ($avgMinutes > 0) {
                    $days = floor($avgMinutes / 1440);
                    $hours = floor(($avgMinutes % 1440) / 60);
                    $mins = $avgMinutes % 60;
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
                    $avgTimeStr = implode(' ', $parts) ?: '0 menit';
                }

                $rate = $total > 0 ? round((($done + $reject) / $total) * 100) : 0;

                // CSI murni dari survei
                $wScore = 0;
                $imp = 0;
                $surveys = 0;
                foreach ($ts as $ticket) {
                    if (! $ticket->survey || ! $ticket->survey->answers || $ticket->survey->answers->count() === 0) {
                        continue;
                    }
                    $surveys++;
                    foreach ($ticket->survey->answers as $ans) {
                        if ($ans->satisfaction_score === null || $ans->importance_score === null) {
                            continue;
                        }
                        $wScore += ($ans->satisfaction_score * $ans->importance_score);
                        $imp += $ans->importance_score;
                    }
                }

                $star = $imp > 0 ? $wScore / $imp : 0;
                $csi = $imp > 0 ? ($star / 5) * 100 : 0;

                // Hitung bonus off-hours
                $dedikasiData = OffHoursHelper::calcDedikasi($ts);

                return [
                    'name' => $user->name,
                    'assigned' => (int) $total,
                    'done' => (int) $done,
                    'reject' => (int) $reject,
                    'rate' => (int) $rate,
                    'avg_time' => $avgTimeStr,
                    'star' => round($star, 2),
                    'csi' => round($csi, 2),
                    'surveys' => (int) $surveys,
                    'raw_points' => $dedikasiData['raw_points'],
                    'weekend_tickets' => $dedikasiData['weekend_count'],
                    'offhour_tickets' => $dedikasiData['offhour_count'],
                ];
            })
            ->values()
            ->toArray();

        // Terapkan normalisasi bonus & ranking_score
        $staffRaw = OffHoursHelper::applyRankingScore($staffRaw);

        // Sort berdasarkan ranking_score gabungan (bukan raw CSI)
        usort($staffRaw, function ($a, $b) {
            if ($a['ranking_score'] !== $b['ranking_score']) {
                return $b['ranking_score'] <=> $a['ranking_score'];
            }

            return $b['csi'] <=> $a['csi'];
        });

        $staffData = array_values($staffRaw);

        return [$tickets, $reportData, $grandTotals, $entityDist, $staffData];
    }
}
