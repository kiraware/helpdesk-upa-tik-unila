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
        // --- 1. FILTER RENTANG WAKTU ---
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

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
        $period = collect();
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $period[$current->format('Y-m-d')] = 0;
            $current->addDay();
        }

        $data = (clone $ticketsQuery)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $dailyTrend = array_merge($period->toArray(), $data);
        ksort($dailyTrend);

        $statusDist = [
            'waiting' => $stats['waiting'],
            'progress' => $stats['progress'],
            'done' => $stats['done'],
            'reject' => $stats['reject'],
        ];

        // --- 4. REKAP BERDASARKAN LAYANAN & ENTITAS ---
        $allTickets = (clone $ticketsQuery)->with(['service', 'user', 'guestDetail'])->get();

        $services = Service::all();
        $serviceStats = [];

        $emptyEntities = [
            'mahasiswa' => 0,
            'dosen' => 0,
            'tendik' => 0,
            'karyawan' => 0,
            'superuser' => 0,
            'tamu' => 0,
            'lainnya' => 0,
        ];

        $entityDist = $emptyEntities;

        foreach ($services as $service) {
            $serviceStats[$service->id] = [
                'name' => $service->name,
                'total' => 0,
                'done' => 0,
                'reject' => 0,
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

            // Deteksi Entitas
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

            $serviceStats[$serviceId]['entities'][$entityCode]++;
            $entityDist[$entityCode]++;
        }

        usort($serviceStats, fn ($a, $b) => $b['total'] <=> $a['total']);

        $chartData = [
            'services_labels' => collect($serviceStats)->pluck('name')->toArray(),
            'services_totals' => collect($serviceStats)->pluck('total')->toArray(),
            'entity_labels' => ['Mahasiswa', 'Dosen', 'Tendik', 'Karyawan', 'Superuser', 'Tamu', 'Lainnya'],
            'entity_totals' => [
                $entityDist['mahasiswa'],
                $entityDist['dosen'],
                $entityDist['tendik'],
                $entityDist['karyawan'],
                $entityDist['superuser'],
                $entityDist['tamu'],
                $entityDist['lainnya'],
            ],
        ];

        // --- 5. PENGHITUNGAN CSI GLOBAL ---
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

        // --- 6. KINERJA PER STAFF ---
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

        return view('reports.index', compact(
            'startDate',
            'endDate',
            'stats',
            'avgCSI',
            'csiPredicate',
            'staffPerformance',
            'dailyTrend',
            'statusDist',
            'serviceStats',
            'chartData'
        ));
    }

    // --- 7. EXPORT EXCEL ---
    public function export(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        $fileName = 'Laporan_Helpdesk_'.$startDate->format('d-M-Y').'_sd_'.$endDate->format('d-M-Y').'.xlsx';

        return Excel::download(new TicketReportExport($startDate, $endDate), $fileName);
    }
}
