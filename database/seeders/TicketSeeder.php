<?php

namespace Database\Seeders;

use App\Enums\TicketStatus;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada User dan Service dulu
        if (User::count() == 0 || Service::count() == 0) {
            $this->command->info('Skip TicketSeeder: User atau Service belum ada.');

            return;
        }

        // Ambil User Staff (misal admin Pusdatin yang dibuat di UserSeeder)
        $staff = User::where('email', 'helpdesk@unila.ac.id')->first() ?? User::first();

        // 1. Buat 5 Tiket WAITING (Baru Masuk)
        Ticket::factory(5)->create([
            'status' => TicketStatus::WAITING,
            'assigned_to' => null,
        ]);

        // 2. Buat 3 Tiket PROGRESS (Sedang dikerjakan Staff)
        Ticket::factory(3)->create([
            'status' => TicketStatus::PROGRESS,
            'assigned_to' => $staff->id,
            'assigned_at' => now()->subDays(1),
        ]);

        // 3. Buat 5 Tiket DONE (Selesai)
        Ticket::factory(5)->closed()->create([
            'assigned_to' => $staff->id,
        ]);

        // 4. Buat 2 Tiket dari GUEST (Tanpa Login)
        // Note: Idealnya diikuti dengan pembuatan GuestTicketDetail
        $guestTickets = Ticket::factory(2)->guest()->create([
            'status' => TicketStatus::WAITING,
        ]);
    }
}
