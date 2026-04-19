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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // 1. Cek apakah user sedang login dan rolenya adalah 'user'
        if ($user && $user->role === UserRole::USER) {

            // 2. Cari 1 tiket milik user yang sudah selesai (DONE/REJECT) tapi BELUM disurvei
            $pendingTicket = Ticket::where('user_id', $user->id)
                ->whereIn('status', [TicketStatus::DONE->value, TicketStatus::REJECT->value])
                ->doesntHave('survey')
                ->oldest('closed_at')
                ->first();

            if ($pendingTicket) {
                // 3. Pengecualian Route (Mencegah Infinite Loop)
                // Kita harus mengizinkan user mengakses halaman form surveinya,
                // memproses submit survei, dan mengizinkan mereka untuk logout.
                $allowedRoutes = [
                    'tickets.show',
                    'tickets.survey.store',
                    'logout',
                ];

                $currentRoute = $request->route()->getName();

                if (! in_array($currentRoute, $allowedRoutes)) {
                    // Redirect paksa ke halaman detail tiket yang butuh disurvei
                    return redirect()->route('tickets.show', $pendingTicket)
                        ->withFragment('survey-section')
                        ->with('warning', 'Anda memiliki tiket yang sudah selesai. Mohon isi survei kepuasan terlebih dahulu untuk dapat melanjutkan.');
                }
            }
        }

        return $next($request);
    }
}
