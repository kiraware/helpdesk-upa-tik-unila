<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Database\Seeder;

class TicketAttachmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tickets = Ticket::all();

        // Jika tidak ada tiket, skip
        if ($tickets->isEmpty()) {
            $this->command->info('Tidak ada tiket ditemukan. Seeder dilewati.');

            return;
        }

        foreach ($tickets as $ticket) {
            // Random: Setiap tiket punya peluang punya 0-3 attachment
            $count = rand(0, 3);

            if ($count > 0) {
                TicketAttachment::factory($count)->create([
                    'ticket_id' => $ticket->id,
                ]);
            }
        }
    }
}
