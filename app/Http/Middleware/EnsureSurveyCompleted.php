<?php

namespace App\Http\Middleware;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSurveyCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->role === UserRole::USER) {

            $pendingTicket = Ticket::where('user_id', $user->id)
                ->whereIn('status', [TicketStatus::DONE->value, TicketStatus::REJECT->value])
                ->doesntHave('survey')
                ->oldest('closed_at')
                ->first();

            if ($pendingTicket) {
                $allowedRoutes = [
                    'tickets.show',
                    'tickets.survey.store',
                    'logout',
                    'api.notifications',
                    'api.ticket.counts',
                ];

                $currentRoute = $request->route()->getName();

                if (! in_array($currentRoute, $allowedRoutes)) {
                    return redirect()->route('tickets.show', $pendingTicket)
                        ->withFragment('survey-section')
                        ->with('warning', 'Anda memiliki tiket yang sudah selesai. Mohon isi survei kepuasan terlebih dahulu untuk dapat melanjutkan.');
                }
            }
        }

        return $next($request);
    }
}
