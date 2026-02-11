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

        // Base Query dalam rentang waktu
        $ticketsQuery = Ticket::whereBetween('created_at', [$startDate, $endDate]);

        // --- A. CARD STATISTIK ---
        $stats = [
            'total' => (clone $ticketsQuery)->count(),
            'done' => (clone $ticketsQuery)->where('status', TicketStatus::DONE)->count(),
            'reject' => (clone $ticketsQuery)->where('status', TicketStatus::REJECT)->count(),
        ];
        // Pending = Waiting + Progress
        $stats['pending'] = $stats['total'] - ($stats['done'] + $stats['reject']);

        // Completion Rate
        $stats['completion_rate'] = $stats['total'] > 0
            ? round(($stats['done'] / $stats['total']) * 100, 1)
            : 0;

        // --- B. CSI (Customer Satisfaction Index) ---
        // Join ke ticket_surveys
        $csiData = Ticket::join('ticket_surveys', 'tickets.id', '=', 'ticket_surveys.ticket_id')
            ->whereBetween('tickets.created_at', [$startDate, $endDate])
            ->selectRaw('AVG(ticket_surveys.csi_score) as avg_score, COUNT(*) as count')
            ->first();

        $avgCSI = $csiData ? round($csiData->avg_score, 1) : 0;

        // --- C. RATA-RATA DURASI (Assigned -> Closed) ---
        // Hanya untuk tiket DONE yang punya assigned_at & closed_at
        $resolutionTimes = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', TicketStatus::DONE)
            ->whereNotNull('assigned_at')
            ->whereNotNull('closed_at')
            ->get()
            ->map(function ($ticket) {
                return $ticket->assigned_at->diffInHours($ticket->closed_at);
            });

        $avgResolutionTime = $resolutionTimes->count() > 0
            ? round($resolutionTimes->avg(), 1)
            : 0;

        // --- D. DATA CHART (Daily Trend) ---
        $dailyTrend = Ticket::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->pluck('count', 'date') // Hasil: ['2023-10-01' => 5, ...]
            ->toArray();

        // --- E. DATA CHART (Status Distribution) ---
        $statusDist = [
            'waiting' => (clone $ticketsQuery)->where('status', TicketStatus::WAITING)->count(),
            'progress' => (clone $ticketsQuery)->where('status', TicketStatus::PROGRESS)->count(),
            'done' => (clone $ticketsQuery)->where('status', TicketStatus::DONE)->count(),
            'reject' => (clone $ticketsQuery)->where('status', TicketStatus::REJECT)->count(),
        ];

        // --- F. KPI STAFF (Leaderboard) ---
        // Ambil Admin & Superuser
        $staffs = User::whereIn('role', [UserRole::ADMIN, UserRole::SUPERUSER])
            ->with(['assignedTickets' => function ($q) use ($startDate, $endDate) {
                // Filter tiket yang ditugaskan dalam periode ini (atau dibuat dalam periode ini)
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }, 'assignedTickets.survey'])
            ->get()
            ->map(function ($user) {
                $tickets = $user->assignedTickets;
                $doneCount = $tickets->where('status', TicketStatus::DONE)->count();
                $totalCount = $tickets->count();

                // Rata-rata Rating Bintang (overall_rating)
                $ratings = $tickets->flatMap->survey->pluck('overall_rating')->filter();
                $avgRating = $ratings->count() > 0 ? round($ratings->avg(), 1) : 0;

                return (object) [
                    'name' => $user->name,
                    'avatar' => $user->avatar_path,
                    'assigned' => $totalCount,
                    'done' => $doneCount,
                    'rate' => $totalCount > 0 ? round(($doneCount / $totalCount) * 100) : 0,
                    'rating_avg' => $avgRating,
                ];
            })
            ->sortByDesc('done') // Urutkan berdasarkan tiket selesai terbanyak
            ->values();

        return view('reports.index', compact(
            'startDate', 'endDate',
            'stats', 'avgCSI', 'avgResolutionTime',
            'dailyTrend', 'statusDist', 'staffs'
        ));
    }
}
