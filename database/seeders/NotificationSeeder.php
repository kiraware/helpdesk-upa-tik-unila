<?php

namespace Database\Seeders;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kirim ke User Pertama (misal Superuser)
        $user = User::first();

        if ($user) {
            $user->notify(new SystemNotification(
                'Selamat Datang!',
                'Sistem notifikasi baru telah berhasil diinstal. Klik untuk melihat dashboard.',
                route('dashboard'),
                'success'
            ));

            $user->notify(new SystemNotification(
                'Tiket Baru Masuk',
                'Ada tiket baru #A7B9K2 dari Divisi Keuangan yang butuh penanganan.',
                route('tickets.index'),
                'info'
            ));

            $user->notify(new SystemNotification(
                'Peringatan Sistem',
                'Laporan bulanan belum digenerate. Harap segera proses.',
                route('reports.index'),
                'error'
            ));
        }
    }
}
