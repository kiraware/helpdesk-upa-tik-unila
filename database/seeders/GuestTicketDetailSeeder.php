<?php

namespace Database\Seeders;

use App\Models\GuestTicketDetail;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class GuestTicketDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil semua tiket yang:
        //    - user_id nya NULL (artinya tiket guest)
        //    - BELUM punya data di tabel guest_ticket_details
        $orphanGuestTickets = Ticket::doesntHave('guestDetail')
            ->whereNull('user_id')
            ->get();

        if ($orphanGuestTickets->isEmpty()) {
            $this->command->info('Tidak ada tiket guest kosong yang perlu diisi detail.');

            return;
        }

        // 2. Buatkan detail untuk setiap tiket tersebut
        foreach ($orphanGuestTickets as $ticket) {
            GuestTicketDetail::factory()->create([
                'ticket_id' => $ticket->id,
            ]);
        }

        $count = $orphanGuestTickets->count();
    }
}
