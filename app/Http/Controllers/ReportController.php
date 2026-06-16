<?php

namespace App\Http\Controllers;

use App\Enums\IdentityType;
use App\Enums\TicketStatus;
use App\Enums\UserEntity;
use App\Enums\UserRole;
use App\Exports\TicketReportExport;
use App\Helpers\OffHoursHelper;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->period ?? 'custom';

        [$startDate, $endDate] = $this->resolveDateRange($request, $period);

        $ticketsQuery = Ticket::whereBetween('tickets.created_at', [$startDate, $endDate]);

        $statusCounts = (clone $ticketsQuery)->selectRaw("
            COUNT(*) as total,
            COUNT(*) FILTER (WHERE status = 'waiting') as waiting,
            COUNT(*) FILTER (WHERE status = 'progress') as progress,
            COUNT(*) FILTER (WHERE status = 'done') as done,
            COUNT(*) FILTER (WHERE status = 'reject') as reject
        ")->first();

        $stats = [
            'total' => (int) $statusCounts->total,
            'waiting' => (int) $statusCounts->waiting,
            'progress' => (int) $statusCounts->progress,
            'done' => (int) $statusCounts->done,
            'reject' => (int) $statusCounts->reject,
        ];
        $stats['completion_rate'] = $stats['total'] > 0
            ? round((($stats['done'] + $stats['reject']) / $stats['total']) * 100, 1) : 0;

        $currentDate = $startDate->copy();
        $periodMap = collect();
        while ($currentDate <= $endDate) {
            $periodMap[$currentDate->format('Y-m-d')] = 0;
            $currentDate->addDay();
        }

        $rawData = (clone $ticketsQuery)
            ->selectRaw('DATE(tickets.created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $dailyTrend = array_merge($periodMap->toArray(), $rawData);
        ksort($dailyTrend);

        $weeklyTrend = $this->buildWeeklyTrend($ticketsQuery, $startDate, $endDate);
        $monthlyTrend = $this->buildMonthlyTrend($ticketsQuery, $startDate, $endDate);

        $monthlyTrendFlat = array_combine(
            array_map(fn ($v) => $v['label'], $monthlyTrend),
            array_map(fn ($v) => $v['total'], $monthlyTrend)
        );

        $statusDist = [
            'waiting' => $stats['waiting'],
            'progress' => $stats['progress'],
            'done' => $stats['done'],
            'reject' => $stats['reject'],
        ];

        $allTickets = (clone $ticketsQuery)
            ->with(['service:id,name', 'user:id,entity', 'guestDetail:id,ticket_id,entity_type'])
            ->select(['id', 'service_id', 'user_id', 'status', 'assigned_at', 'closed_at', 'created_at'])
            ->get();

        $services = Service::all();
        $serviceStats = [];
        $emptyEntities = [
            'mahasiswa' => 0, 'dosen' => 0, 'tendik' => 0,
            'karyawan' => 0, 'superuser' => 0, 'tamu' => 0, 'lainnya' => 0,
        ];
        $entityDist = $emptyEntities;

        foreach ($services as $service) {
            $serviceStats[$service->id] = [
                'name' => $service->name,
                'total' => 0,
                'done' => 0,
                'reject' => 0,
                'waiting' => 0,
                'progress' => 0,
                'entities' => $emptyEntities,
            ];
        }

        foreach ($allTickets as $ticket) {
            $serviceId = $ticket->service_id;
            if (! isset($serviceStats[$serviceId])) {
                continue;
            }

            $serviceStats[$serviceId]['total']++;
            if ($ticket->status === TicketStatus::DONE) {
                $serviceStats[$serviceId]['done']++;
            }
            if ($ticket->status === TicketStatus::REJECT) {
                $serviceStats[$serviceId]['reject']++;
            }
            if ($ticket->status === TicketStatus::WAITING) {
                $serviceStats[$serviceId]['waiting']++;
            }
            if ($ticket->status === TicketStatus::PROGRESS) {
                $serviceStats[$serviceId]['progress']++;
            }

            $entityCode = $this->detectEntity($ticket);
            $serviceStats[$serviceId]['entities'][$entityCode]++;
            $entityDist[$entityCode]++;
        }

        usort($serviceStats, fn ($a, $b) => $b['total'] <=> $a['total']);

        foreach ($serviceStats as &$svc) {
            $svc['completion_rate'] = $svc['total'] > 0
                ? round((($svc['done'] + $svc['reject']) / $svc['total']) * 100, 1) : 0;
        }
        unset($svc);

        $chartData = [
            'services_labels' => collect($serviceStats)->pluck('name')->toArray(),
            'services_totals' => collect($serviceStats)->pluck('total')->toArray(),
            'services_done' => collect($serviceStats)->pluck('done')->toArray(),
            'services_reject' => collect($serviceStats)->pluck('reject')->toArray(),
            'entity_labels' => ['Mahasiswa', 'Dosen', 'Tendik', 'Karyawan', 'Superuser', 'Tamu', 'Lainnya'],
            'entity_totals' => array_values($entityDist),
            'weekly_labels' => array_keys($weeklyTrend),
            'weekly_totals' => array_values($weeklyTrend),
            'monthly_labels' => array_values(array_map(fn ($v) => $v['label'], $monthlyTrend)),
            'monthly_totals' => array_values(array_map(fn ($v) => $v['total'], $monthlyTrend)),
            'monthly_done' => array_values(array_map(fn ($v) => $v['done'], $monthlyTrend)),
            'monthly_reject' => array_values(array_map(fn ($v) => $v['reject'], $monthlyTrend)),
        ];

        $surveyedTicketsCount = (clone $ticketsQuery)
            ->has('survey')
            ->count();

        // 2. Ambil akumulasi skor kepuasan dan kepentingan per pertanyaan unik
        $answersData = \DB::table('tickets')
            ->join('ticket_surveys', 'tickets.id', '=', 'ticket_surveys.ticket_id')
            ->join('ticket_survey_answers', 'ticket_surveys.id', '=', 'ticket_survey_answers.ticket_survey_id')
            ->whereBetween('tickets.created_at', [$startDate, $endDate])
            ->select('ticket_survey_answers.survey_question_id')
            ->selectRaw('SUM(ticket_survey_answers.satisfaction_score) as total_sat')
            ->selectRaw('SUM(ticket_survey_answers.importance_score) as total_imp')
            ->groupBy('ticket_survey_answers.survey_question_id')
            ->get();

        $avgCSI = 0;
        if ($surveyedTicketsCount > 0 && $answersData->isNotEmpty()) {
            $sumProduct = 0;
            $sumAllImportance = 0;

            foreach ($answersData as $row) {
                $sumProduct += ($row->total_imp * $row->total_sat);
                $sumAllImportance += $row->total_imp;
            }

            if ($sumAllImportance > 0) {
                $avgCSI = ($sumProduct / (5 * $surveyedTicketsCount * $sumAllImportance)) * 100;
            }
        }
        $avgCSI = round($avgCSI, 2);

        $csiPredicate = match (true) {
            $avgCSI > 80 => 'Sangat Puas',
            $avgCSI > 60 => 'Puas',
            $avgCSI > 40 => 'Cukup Puas',
            $avgCSI > 20 => 'Tidak Puas',
            $avgCSI > 0 => 'Sangat Tidak Puas',
            default => 'Belum Ada Data',
        };

        $staffRaw = User::whereIn('role', [UserRole::ADMIN, UserRole::SUPERUSER])
            ->select(['id', 'name', 'avatar_path'])
            ->with(['assignedTickets' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->select(['id', 'assigned_to', 'status', 'assigned_at', 'closed_at', 'created_at'])
                    ->with('survey.answers');
            }])
            ->get()
            ->map(function ($user) {
                $assignedTickets = $user->assignedTickets;
                $totalCount = $assignedTickets->count();
                $doneCount = $assignedTickets->where('status', TicketStatus::DONE)->count();
                $rejectCount = $assignedTickets->where('status', TicketStatus::REJECT)->count();

                $userTimes = $assignedTickets
                    ->whereNotNull('assigned_at')
                    ->whereNotNull('closed_at')
                    ->map(fn ($t) => $t->assigned_at->diffInHours($t->closed_at));

                $avgUserTime = $userTimes->count() > 0 ? round($userTimes->avg(), 1) : 0;
                $rate = $totalCount > 0
                    ? round((($doneCount + $rejectCount) / $totalCount) * 100)
                    : 0;

                $staffAnswers = [];
                $surveyCount = 0;

                foreach ($assignedTickets as $ticket) {
                    if ($ticket->survey && $ticket->survey->answers) {
                        $surveyCount++;
                        foreach ($ticket->survey->answers as $answer) {
                            $qId = $answer->survey_question_id;
                            if (! isset($staffAnswers[$qId])) {
                                $staffAnswers[$qId] = ['sat' => 0, 'imp' => 0];
                            }
                            $staffAnswers[$qId]['sat'] += $answer->satisfaction_score;
                            $staffAnswers[$qId]['imp'] += $answer->importance_score;
                        }
                    }
                }

                $staffCSI = 0;
                $staffStar = 0;

                if ($surveyCount > 0 && ! empty($staffAnswers)) {
                    $staffSumProduct = 0;
                    $staffSumAllImp = 0;

                    foreach ($staffAnswers as $qId => $scores) {
                        $staffSumProduct += ($scores['imp'] * $scores['sat']);
                        $staffSumAllImp += $scores['imp'];
                    }

                    if ($staffSumAllImp > 0) {
                        $staffCSI = ($staffSumProduct / (5 * $surveyCount * $staffSumAllImp)) * 100;
                        $staffStar = ($staffSumProduct / ($surveyCount * $staffSumAllImp));
                    }
                }

                $dedikasiData = OffHoursHelper::calcDedikasi($assignedTickets);

                return [
                    'name' => $user->name,
                    'avatar' => $user->avatar_path,
                    'assigned' => $totalCount,
                    'done' => $doneCount,
                    'rate' => $rate,
                    'avg_resolution_time' => $avgUserTime,
                    'rating_star' => round($staffStar, 2),
                    'csi_score' => round($staffCSI, 2),
                    'survey_count' => $surveyCount,
                    'csi' => round($staffCSI, 2),   // alias untuk applyRankingScore()
                    'raw_points' => $dedikasiData['raw_points'],
                    'weekend_tickets' => $dedikasiData['weekend_count'],
                    'offhour_tickets' => $dedikasiData['offhour_count'],
                    'onhour_tickets' => $dedikasiData['onhour_count'],
                ];
            })
            ->values()
            ->toArray();

        $staffRaw = OffHoursHelper::applyRankingScore($staffRaw);

        usort($staffRaw, function ($a, $b) {
            if ($a['ranking_score'] !== $b['ranking_score']) {
                return $b['ranking_score'] <=> $a['ranking_score'];
            }
            if ($a['csi_score'] !== $b['csi_score']) {
                return $b['csi_score'] <=> $a['csi_score'];
            }

            return $b['done'] <=> $a['done'];
        });

        $staffPerformance = collect(array_map(fn ($s) => (object) $s, $staffRaw));

        $durationStats = $this->buildDurationStats($allTickets);

        $priorityStats = (clone $ticketsQuery)
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        $ticketStatuses = TicketStatus::cases();
        $userEntities = UserEntity::cases();

        return view('reports.index', compact(
            'startDate', 'endDate', 'period',
            'stats', 'avgCSI', 'csiPredicate',
            'staffPerformance', 'dailyTrend', 'statusDist',
            'serviceStats', 'chartData', 'weeklyTrend', 'monthlyTrendFlat',
            'durationStats', 'priorityStats',
            'ticketStatuses', 'userEntities'
        ));
    }

    public function export(Request $request)
    {
        $period = $request->period ?? 'custom';
        [$startDate, $endDate] = $this->resolveDateRange($request, $period);

        $fileName = 'Laporan_Helpdesk_'.$startDate->format('d-M-Y').'_sd_'.$endDate->format('d-M-Y').'.xlsx';

        return Excel::download(new TicketReportExport($startDate, $endDate, $period), $fileName);
    }

    private function resolveDateRange(Request $request, string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'daily' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'weekly' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'monthly' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'yearly' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [
                $request->start_date
                    ? Carbon::parse($request->start_date)->startOfDay()
                    : $now->copy()->startOfMonth(),
                $request->end_date
                    ? Carbon::parse($request->end_date)->endOfDay()
                    : $now->copy()->endOfDay(),
            ],
        };
    }

    private function buildWeeklyTrend($baseQuery, Carbon $startDate, Carbon $endDate): array
    {
        $raw = (clone $baseQuery)
            ->selectRaw("to_char(tickets.created_at, 'IYYYIW') as yw, MIN(DATE(tickets.created_at)) as week_start, COUNT(*) as count")
            ->groupBy('yw')
            ->orderBy('yw')
            ->get();

        $result = [];
        foreach ($raw as $row) {
            $label = Carbon::parse($row->week_start)->format('d M');
            $result[$label] = $row->count;
        }

        return $result;
    }

    private function buildMonthlyTrend($baseQuery, Carbon $startDate, Carbon $endDate): array
    {
        $raw = (clone $baseQuery)
            ->selectRaw("
                to_char(tickets.created_at, 'YYYY-MM') as ym,
                COUNT(*) as total,
                COUNT(*) FILTER (WHERE status = 'done') as done,
                COUNT(*) FILTER (WHERE status = 'reject') as reject
            ")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        $result = [];
        foreach ($raw as $row) {
            $label = Carbon::parse($row->ym.'-01')->format('M Y');
            $result[$row->ym] = [
                'label' => $label,
                'total' => (int) $row->total,
                'done' => (int) $row->done,
                'reject' => (int) $row->reject,
            ];
        }

        return $result;
    }

    private function detectEntity($ticket): string
    {
        if ($ticket->user) {
            return match ($ticket->user->entity) {
                UserEntity::MAHASISWA => 'mahasiswa',
                UserEntity::DOSEN => 'dosen',
                UserEntity::TENDIK => 'tendik',
                UserEntity::KARYAWAN => 'karyawan',
                UserEntity::SUPER_USER => 'superuser',
                UserEntity::TAMU => 'tamu',
                default => 'lainnya',
            };
        }

        if ($ticket->guestDetail) {
            return match ($ticket->guestDetail->entity_type) {
                IdentityType::MAHASISWA => 'mahasiswa',
                IdentityType::DOSEN => 'dosen',
                IdentityType::TENDIK => 'tendik',
                default => 'lainnya',
            };
        }

        return 'lainnya';
    }

    private function buildDurationStats($tickets): array
    {
        $buckets = ['< 1 Jam' => 0, '1–4 Jam' => 0, '4–8 Jam' => 0, '1 Hari' => 0, '> 1 Hari' => 0];

        foreach ($tickets as $ticket) {
            if (! $ticket->assigned_at || ! $ticket->closed_at) {
                continue;
            }
            $hours = $ticket->assigned_at->diffInHours($ticket->closed_at);

            if ($hours < 1) {
                $buckets['< 1 Jam']++;
            } elseif ($hours <= 4) {
                $buckets['1–4 Jam']++;
            } elseif ($hours <= 8) {
                $buckets['4–8 Jam']++;
            } elseif ($hours <= 24) {
                $buckets['1 Hari']++;
            } else {
                $buckets['> 1 Hari']++;
            }
        }

        return $buckets;
    }
}
