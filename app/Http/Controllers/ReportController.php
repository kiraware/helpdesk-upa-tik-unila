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
        // 1. Filter Waktu (Default: Awal Bulan ini s/d Hari ini)
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

        // CSI & Predikat Global
        $avgCSI = Ticket::join('ticket_surveys', 'tickets.id', '=', 'ticket_surveys.ticket_id')
            ->whereBetween('tickets.created_at', [$startDate, $endDate])
            ->avg('ticket_surveys.csi_score');
        $avgCSI = $avgCSI ? round($avgCSI, 1) : 0;

        $csiPredicate = match (true) {
            $avgCSI >= 81 => 'Sangat Puas',
            $avgCSI >= 61 => 'Puas',
            $avgCSI >= 41 => 'Cukup Puas',
            $avgCSI >= 21 => 'Kurang Puas',
            default => 'Tidak Puas',
        };

        // Avg Resolution Time Global
        $resolutionTimes = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', TicketStatus::DONE)
            ->whereNotNull('assigned_at')
            ->whereNotNull('closed_at')
            ->get()
            ->map(fn ($t) => $t->assigned_at->diffInHours($t->closed_at));
        $avgResolutionTime = $resolutionTimes->count() > 0 ? round($resolutionTimes->avg(), 1) : 0;

        // Chart Data
        $dailyTrend = Ticket::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->pluck('count', 'date')
            ->toArray();

        $statusDist = [
            'waiting' => $stats['waiting'], 'progress' => $stats['progress'],
            'done' => $stats['done'], 'reject' => $stats['reject'],
        ];

        // --- KPI STAFF ---
        $staffs = User::whereIn('role', [UserRole::ADMIN, UserRole::SUPERUSER])
            ->with(['assignedTickets' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }, 'assignedTickets.survey'])
            ->get()
            ->map(function ($user) {
                $tickets = $user->assignedTickets;
                $totalCount = $tickets->count();
                $doneCount = $tickets->where('status', TicketStatus::DONE)->count();
                $surveys = $tickets->map->survey->filter();

                // Hitung Rata-rata Waktu Penyelesaian Per User
                // Hanya tiket DONE yang punya assigned_at & closed_at
                $userTimes = $tickets->where('status', TicketStatus::DONE)
                    ->whereNotNull('assigned_at')
                    ->whereNotNull('closed_at')
                    ->map(fn ($t) => $t->assigned_at->diffInHours($t->closed_at));

                $avgUserTime = $userTimes->count() > 0 ? round($userTimes->avg(), 1) : 0;

                $rate = $totalCount > 0 ? round(($doneCount / $totalCount) * 100) : 0;
                $avgStar = $surveys->count() > 0 ? round($surveys->avg('overall_rating'), 1) : 0;
                $avgScore = $surveys->count() > 0 ? round($surveys->avg('csi_score'), 1) : 0;

                return (object) [
                    'name' => $user->name,
                    'avatar' => $user->avatar_path,
                    'assigned' => $totalCount,
                    'done' => $doneCount,
                    'rate' => $rate,
                    'avg_resolution_time' => $avgUserTime, // <--- Data Baru
                    'rating_star' => $avgStar,
                    'csi_score' => $avgScore,
                    'survey_count' => $surveys->count(),
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
            'startDate', 'endDate',
            'stats', 'avgCSI', 'csiPredicate', 'avgResolutionTime',
            'dailyTrend', 'statusDist', 'staffs'
        ));
    }
}
