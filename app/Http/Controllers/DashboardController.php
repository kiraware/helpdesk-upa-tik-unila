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

            $statusCounts = Ticket::selectRaw("
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

            $recentTickets = Ticket::with([
                'user:id,name,avatar_path',
                'service:id,name',
            ])
                ->withCount('comments')
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

            $adminCounts = Ticket::selectRaw("
                COUNT(*) FILTER (WHERE assigned_to IS NULL) as unassigned,
                COUNT(*) FILTER (WHERE assigned_to = ? AND status = 'progress') as my_tasks
            ", [$user->id])->first();

            $stats = [
                'unassigned' => (int) $adminCounts->unassigned,
                'my_tasks' => (int) $adminCounts->my_tasks,
            ];

            $priorityTickets = Ticket::with([
                'user:id,name,avatar_path',
                'service:id,name',
                'guestDetail:id,ticket_id,full_name',
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

        $userCounts = Ticket::where('user_id', $user->id)->selectRaw("
            COUNT(*) FILTER (WHERE status IN ('waiting', 'progress')) as active,
            COUNT(*) FILTER (WHERE status = 'done') as completed
        ")->first();

        $myStats = [
            'active' => (int) $userCounts->active,
            'completed' => (int) $userCounts->completed,
        ];

        $myRecentTickets = Ticket::where('user_id', $user->id)
            ->with([
                'service:id,name',
                'assignee:id,name,avatar_path',
            ])
            ->withCount('comments')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.user', compact(
            'myStats',
            'myRecentTickets'
        ));
    }
}
