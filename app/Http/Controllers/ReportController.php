<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        $ticketsQuery = Ticket::whereBetween('created_at', [$startDate, $endDate]);

        // STATS Global (Waiting, Progress, Done, Reject)
        $stats = [
            'total' => (clone $ticketsQuery)->count(),
            'waiting' => (clone $ticketsQuery)->where('status', TicketStatus::WAITING)->count(),
            'progress' => (clone $ticketsQuery)->where('status', TicketStatus::PROGRESS)->count(),
            'done' => (clone $ticketsQuery)->where('status', TicketStatus::DONE)->count(),
            'reject' => (clone $ticketsQuery)->where('status', TicketStatus::REJECT)->count(),
        ];
        $stats['completion_rate'] = $stats['total'] > 0
            ? round(($stats['done'] / $stats['total']) * 100, 1) : 0;

        // --- DATA UNTUK GRAFIK ---

        // 1. Data Tren Tiket Harian
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

        // 2. Data Komposisi Status
        $statusDist = [
            'waiting' => $stats['waiting'],
            'progress' => $stats['progress'],
            'done' => $stats['done'],
            'reject' => $stats['reject'],
        ];

        // -------------------------------------------------------------

        // --- PENGHITUNGAN CSI GLOBAL (Sesuai Jurnal) ---
        $ticketsWithSurvey = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('survey')
            ->with('survey.answers')
            ->get();

        $globalTotalWeightScore = 0;
        $globalTotalImportance = 0;

        foreach ($ticketsWithSurvey as $ticket) {
            if ($ticket->survey && $ticket->survey->answers) {
                foreach ($ticket->survey->answers as $answer) {
                    // Weight Score = Satisfaction x Importance
                    $globalTotalWeightScore += ($answer->satisfaction_score * $answer->importance_score);
                    $globalTotalImportance += $answer->importance_score;
                }
            }
        }

        $avgCSI = 0;
        if ($globalTotalImportance > 0) {
            // (Total WS / Total Importance) / 5 * 100
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
        // -----------------------------------------------

        // --- KINERJA PER STAFF (PENGHITUNGAN CSI PER STAFF) ---
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

                // Hitung CSI khusus Staff ini
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
                    $staffStar = $staffWeightScore / $staffImportance; // Skala 1-5 (Weighted Rating)
                    $staffCSI = ($staffStar / 5) * 100; // Persentase CSI
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

                return $b->csi_score <=> $a->csi_score; // Urutkan dari CSI tertinggi
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
            'statusDist'
        ));
    }
}
