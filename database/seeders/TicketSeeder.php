<?php

namespace Database\Seeders;

use App\Models\GuestTicketDetail;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed tiket untuk pengguna yang sudah login (Authenticated Users)
        // Ini akan menggunakan user_id yang ada atau membuat user baru dari factory
        Ticket::factory()->count(15)->create();

        // 2. Seed tiket yang sudah selesai (Status DONE)
        Ticket::factory()->closed()->count(10)->create();

        // 3. Seed tiket untuk tamu (Guest Users)
        // GuestTicketDetailFactory secara otomatis akan membuat tiket tamu melalui state guest()
        GuestTicketDetail::factory()->count(15)->create();
    }
}
