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

        // Ambil staff khusus (admin/superuser) untuk assigned_to
        $staff = User::whereIn('role', ['admin', 'superuser'])->first() ?? User::first();

        // 1. Tiket Baru (WAITING)
        Ticket::factory(10)->create([
            'status' => TicketStatus::WAITING,
            'assigned_to' => null,
        ]);

        // 2. Tiket Sedang Diproses (PROGRESS)
        Ticket::factory(5)->create([
            'status' => TicketStatus::PROGRESS,
            'assigned_to' => $staff->id,
            'assigned_at' => now()->subHours(5),
        ]);

        // 3. Tiket Selesai (DONE)
        Ticket::factory(10)->closed()->create([
            'assigned_to' => $staff->id,
        ]);

        // 4. Tiket Ditolak (REJECT)
        Ticket::factory(3)->create([
            'status' => TicketStatus::REJECT,
            'assigned_to' => $staff->id,
            'assigned_at' => now()->subDays(2),
            'closed_at' => now()->subDay(),
        ]);

        // 5. Tiket GUEST (Tanpa User ID)
        Ticket::factory(4)->guest()->create([
            'status' => TicketStatus::WAITING,
        ]);
    }
}
