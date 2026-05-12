<?php

namespace App\Http\Controllers;

use App\Enums\IdentityType;
use App\Enums\TicketStatus;
use App\Enums\UserEntity;
use App\Enums\UserRole;
use App\Exports\TicketReportExport;
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
        // --- 1. FILTER RENTANG WAKTU & PERIODE ---
        $period = $request->period ?? 'custom'; // daily, weekly, monthly, yearly, custom

        [$startDate, $endDate] = $this->resolveDateRange($request, $period);

        $ticketsQuery = Ticket::whereBetween('created_at', [$startDate, $endDate]);

        // --- 2. STATS GLOBAL ---
        $stats = [
            'total' => (clone $ticketsQuery)->count(),
            'waiting' => (clone $ticketsQuery)->where('status', TicketStatus::WAITING)->count(),
            'progress' => (clone $ticketsQuery)->where('status', TicketStatus::PROGRESS)->count(),
            'done' => (clone $ticketsQuery)->where('status', TicketStatus::DONE)->count(),
            'reject' => (clone $ticketsQuery)->where('status', TicketStatus::REJECT)->count(),
        ];
        $stats['completion_rate'] = $stats['total'] > 0
            ? round(($stats['done'] / $stats['total']) * 100, 1) : 0;

        // --- 3. DATA TREN HARIAN & STATUS ---
        $currentDate = $startDate->copy();
        $periodMap = collect();
        while ($currentDate <= $endDate) {
            $periodMap[$currentDate->format('Y-m-d')] = 0;
            $currentDate->addDay();
        }

        $rawData = (clone $ticketsQuery)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $dailyTrend = array_merge($periodMap->toArray(), $rawData);
        ksort($dailyTrend);

        // --- 4. TREN MINGGUAN & BULANAN untuk chart tambahan ---
        $weeklyTrend = $this->buildWeeklyTrend($ticketsQuery, $startDate, $endDate);
        $monthlyTrend = $this->buildMonthlyTrend($ticketsQuery, $startDate, $endDate);

        $statusDist = [
            'waiting' => $stats['waiting'],
            'progress' => $stats['progress'],
            'done' => $stats['done'],
            'reject' => $stats['reject'],
        ];

        // --- 5. REKAP BERDASARKAN LAYANAN & ENTITAS ---
        $allTickets = (clone $ticketsQuery)->with(['service', 'user', 'guestDetail'])->get();

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

        // Hitung completion rate per layanan
        foreach ($serviceStats as &$svc) {
            $svc['completion_rate'] = $svc['total'] > 0
                ? round(($svc['done'] / $svc['total']) * 100, 1) : 0;
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
            'monthly_labels' => array_keys($monthlyTrend),
            'monthly_totals' => array_values($monthlyTrend),
        ];

        // --- 6. PENGHITUNGAN CSI GLOBAL ---
        $ticketsWithSurvey = (clone $ticketsQuery)
            ->whereHas('survey')
            ->with('survey.answers')
            ->get();

        $globalTotalWeightScore = 0;
        $globalTotalImportance = 0;

        foreach ($ticketsWithSurvey as $ticket) {
            if ($ticket->survey && $ticket->survey->answers) {
                foreach ($ticket->survey->answers as $answer) {
                    $globalTotalWeightScore += ($answer->satisfaction_score * $answer->importance_score);
                    $globalTotalImportance += $answer->importance_score;
                }
            }
        }

        $avgCSI = 0;
        if ($globalTotalImportance > 0) {
            $avgCSI = (($globalTotalWeightScore / $globalTotalImportance) / 5) * 100;
        }
        $avgCSI = round($avgCSI, 2);

        $csiPredicate = match (true) {
            $avgCSI >= 81 => 'Sangat Puas',
            $avgCSI >= 66 => 'Puas',
            $avgCSI >= 51 => 'Cukup Puas',
            $avgCSI >= 35 => 'Kurang Puas',
            $avgCSI > 0 => 'Tidak Puas',
            default => 'Belum Ada Data',
        };

        // --- 7. KINERJA PER STAFF ---
        $staffPerformance = User::whereIn('role', [UserRole::ADMIN, UserRole::SUPERUSER])
            ->with(['assignedTickets' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->with('survey.answers');
            }])
            ->get()
            ->map(function ($user) {
                $assignedTickets = $user->assignedTickets;
                $totalCount = $assignedTickets->count();
                $doneCount = $assignedTickets->where('status', TicketStatus::DONE)->count();

                $userTimes = $assignedTickets
                    ->whereNotNull('assigned_at')
                    ->whereNotNull('closed_at')
                    ->map(fn ($t) => $t->assigned_at->diffInHours($t->closed_at));

                $avgUserTime = $userTimes->count() > 0 ? round($userTimes->avg(), 1) : 0;
                $rate = $totalCount > 0 ? round(($doneCount / $totalCount) * 100) : 0;

                $staffWeightScore = 0;
                $staffImportance = 0;
                $surveyCount = 0;

                foreach ($assignedTickets as $ticket) {
                    if ($ticket->survey && $ticket->survey->answers) {
                        $surveyCount++;
                        foreach ($ticket->survey->answers as $answer) {
                            $staffWeightScore += ($answer->satisfaction_score * $answer->importance_score);
                            $staffImportance += $answer->importance_score;
                        }
                    }
                }

                $staffCSI = 0;
                $staffStar = 0;

                if ($staffImportance > 0) {
                    $staffStar = $staffWeightScore / $staffImportance;
                    $staffCSI = ($staffStar / 5) * 100;
                }

                return (object) [
                    'name' => $user->name,
                    'avatar' => $user->avatar_path,
                    'assigned' => $totalCount,
                    'done' => $doneCount,
                    'rate' => $rate,
                    'avg_resolution_time' => $avgUserTime,
                    'rating_star' => round($staffStar, 2),
                    'csi_score' => round($staffCSI, 2),
                    'survey_count' => $surveyCount,
                ];
            })
            ->sort(function ($a, $b) {
                if ($a->csi_score == $b->csi_score) {
                    return $b->done <=> $a->done;
                }

                return $b->csi_score <=> $a->csi_score;
            })
            ->values();

        // --- 8. STATISTIK DURASI RESOLUSI (histogram) ---
        $durationStats = $this->buildDurationStats($allTickets);

        // --- 9. PRIORITAS BREAKDOWN ---
        $priorityStats = (clone $ticketsQuery)
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        return view('reports.index', compact(
            'startDate', 'endDate', 'period',
            'stats', 'avgCSI', 'csiPredicate',
            'staffPerformance', 'dailyTrend', 'statusDist',
            'serviceStats', 'chartData', 'weeklyTrend', 'monthlyTrend',
            'durationStats', 'priorityStats'
        ));
    }

    // --- EXPORT EXCEL ---
    public function export(Request $request)
    {
        $period = $request->period ?? 'custom';
        [$startDate, $endDate] = $this->resolveDateRange($request, $period);

        $fileName = 'Laporan_Helpdesk_'.$startDate->format('d-M-Y').'_sd_'.$endDate->format('d-M-Y').'.xlsx';

        return Excel::download(new TicketReportExport($startDate, $endDate, $period), $fileName);
    }

    // =========================================================
    // HELPERS
    // =========================================================

    private function resolveDateRange(Request $request, string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'daily' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'weekly' => [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
            ],
            'monthly' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
            'yearly' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
            ],
            default => [ // custom
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
            // Use to_char for Postgres instead of YEARWEEK
            ->selectRaw("to_char(created_at, 'IYYYIW') as yw, MIN(DATE(created_at)) as week_start, COUNT(*) as count")
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
            // Use to_char for Postgres instead of DATE_FORMAT
            ->selectRaw("to_char(created_at, 'YYYY-MM') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $result = [];
        foreach ($raw as $key => $val) {
            $label = Carbon::parse($key.'-01')->format('M Y');
            $result[$label] = $val;
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
