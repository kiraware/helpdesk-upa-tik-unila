<?php

namespace App\Providers;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $user = auth()->user();

            if (! $user) {
                return;
            }

            // Tiket waiting
            $waitingCount = Ticket::where('status', TicketStatus::WAITING)->count();

            // Tiket assigned ke saya & masih progress
            $assignedProgressCount = Ticket::where('assigned_to', $user->id)
                ->where('status', TicketStatus::PROGRESS)
                ->count();

            $view->with([
                'waitingCount' => $waitingCount,
                'assignedProgressCount' => $assignedProgressCount,
            ]);
        });
    }
}
