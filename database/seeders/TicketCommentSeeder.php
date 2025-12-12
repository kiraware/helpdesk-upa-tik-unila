<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil beberapa tiket yang statusnya PROGRESS atau DONE
        $tickets = Ticket::whereIn('status', ['progress', 'done'])->limit(5)->get();

        // Ambil satu staff admin untuk simulasi balasan
        $staff = User::where('role', 'admin')->first() ?? User::factory()->create();

        foreach ($tickets as $ticket) {
            // 1. Komentar Pertama: Pelapor menambahkan info susulan (jika guest/user)
            TicketComment::factory()->create([
                'ticket_id' => $ticket->id,
                'user_id' => $ticket->user_id, // Pengirimnya si pemilik tiket (bisa null/guest)
                'message' => 'Mohon maaf, berikut saya lampirkan screenshot tambahan errornya.',
                'created_at' => $ticket->created_at->addMinutes(10),
            ]);

            // 2. Komentar Kedua: Staff merespon (Public)
            TicketComment::factory()->create([
                'ticket_id' => $ticket->id,
                'user_id' => $staff->id,
                'message' => 'Baik, laporan Anda sedang kami pelajari. Mohon ditunggu.',
                'is_internal_note' => false,
                'created_at' => $ticket->created_at->addMinutes(30),
            ]);

            // 3. Komentar Ketiga: Staff membuat catatan internal (Rahasia)
            TicketComment::factory()->create([
                'ticket_id' => $ticket->id,
                'user_id' => $staff->id,
                'message' => 'Sepertinya ini bug di API SSO, perlu eskalasi ke Tim Infrastruktur.',
                'is_internal_note' => true, // User tidak akan melihat ini
                'created_at' => $ticket->created_at->addMinutes(35),
            ]);
        }
    }
}
