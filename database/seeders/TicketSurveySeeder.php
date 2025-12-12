<?php

namespace Database\Seeders;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketSurvey;
use Illuminate\Database\Seeder;

class TicketSurveySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cari Tiket yang statusnya DONE tapi BELUM punya Survey
        $unreviewedTickets = Ticket::doesntHave('survey')
            ->where('status', TicketStatus::DONE->value)
            ->get();

        if ($unreviewedTickets->isEmpty()) {
            $this->command->info('Tidak ada tiket DONE yang perlu diberi rating.');

            return;
        }

        // 2. Beri rating dummy untuk setiap tiket tersebut
        foreach ($unreviewedTickets as $ticket) {
            // Random: 10% kemungkinan dapat bad review
            $isBadReview = rand(1, 10) == 1;

            if ($isBadReview) {
                TicketSurvey::factory()->badReview()->create([
                    'ticket_id' => $ticket->id,
                ]);
            } else {
                TicketSurvey::factory()->create([
                    'ticket_id' => $ticket->id,
                ]);
            }
        }

        $count = $unreviewedTickets->count();
        $this->command->info("Berhasil mengisi survei CSI untuk {$count} tiket yang selesai.");
    }
}
