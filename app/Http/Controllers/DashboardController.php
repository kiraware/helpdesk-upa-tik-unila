<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Service;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === UserRole::SUPERUSER) {

            $stats = [
                'total' => Ticket::count(),
                'waiting' => Ticket::where('status', TicketStatus::WAITING)->count(),
                'progress' => Ticket::where('status', TicketStatus::PROGRESS)->count(),
                'done' => Ticket::where('status', TicketStatus::DONE)->count(),
                'reject' => Ticket::where('status', TicketStatus::REJECT)->count(),
            ];

            $recentTickets = Ticket::with([
                'user',
                'service',
            ])
                ->withCount('comments') // <-- TAMBAHAN
                ->latest()
                ->take(5)
                ->get();

            $serviceStats = Service::withCount('tickets')
                ->orderByDesc('tickets_count')
                ->take(5)
                ->get();

            return view('dashboard.superuser', compact(
                'stats',
                'recentTickets',
                'serviceStats'
            ));
        }

        if ($user->role === UserRole::ADMIN) {

            $stats = [
                'unassigned' => Ticket::whereNull('assigned_to')->count(),

                'my_tasks' => Ticket::where('assigned_to', $user->id)
                    ->where('status', TicketStatus::PROGRESS)
                    ->count(),
            ];

            $priorityTickets = Ticket::with([
                'user',
                'service',
                'guestDetail',
            ])
                ->withCount('comments')
                ->where(function ($q) {
                    $q->whereNull('assigned_to')
                        ->orWhere('status', TicketStatus::WAITING);
                })
                ->orderByRaw("
                    CASE status
                        WHEN 'waiting'  THEN 0
                        WHEN 'progress' THEN 1
                        ELSE 2
                    END ASC
                ")
                ->orderByRaw("
                    CASE priority
                        WHEN 'high'   THEN 0
                        WHEN 'medium' THEN 1
                        WHEN 'low'    THEN 2
                        ELSE 3
                    END ASC
                ")
                ->oldest()
                ->take(10)
                ->get();

            return view('dashboard.admin', compact(
                'stats',
                'priorityTickets'
            ));
        }

        $myStats = [
            'active' => Ticket::where('user_id', $user->id)
                ->whereIn('status', [
                    TicketStatus::WAITING,
                    TicketStatus::PROGRESS,
                ])
                ->count(),

            'completed' => Ticket::where('user_id', $user->id)
                ->where('status', TicketStatus::DONE)
                ->count(),
        ];

        $myRecentTickets = Ticket::where('user_id', $user->id)
            ->with([
                'service',
                'assignee',
            ])
            ->withCount('comments') // <-- TAMBAHAN
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.user', compact(
            'myStats',
            'myRecentTickets'
        ));
    }
}
