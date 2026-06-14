<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guestServices = [
            'Lupa Password SSO' => 'Jika Anda juga lupa Username SSO, pelayanan hanya dapat dilakukan secara onsite di Gedung UPA TIK Lt 1 dengan membawa persyaratan identitas.',
            'Registrasi SSO' => null,
            'Email Resmi Unila' => "Persyaratan membuat akun email resmi Unila (khusus Unit Kerja / Jurnal / Seminar / Kegiatan):\n\nWajib melampirkan Surat Permohonan Email Resmi Unila yang ditujukan kepada Kepala UPA TIK Unila. Surat tersebut harus berisi deskripsi unit kerja/jurnal/seminar/kegiatan dan mencantumkan:\n- Usulan Username\n- NIP dan Nama Penanggung Jawab email\n\nDosen, Tenaga Kependidikan, dan Mahasiswa dapat langsung membuat tiket tanpa syarat lampiran ini.",
        ];

        $userServices = [
            'Jaringan Internet',
            'Website Down',
            'Sistem Informasi',
            'Siakadu',
            'Hosting dan Domain',
            'Tanda Tangan Elektronik',
            'Lainnya',
        ];

        foreach ($guestServices as $serviceName => $notes) {
            Service::firstOrCreate(
                ['name' => $serviceName],
                [
                    'is_active' => true,
                    'show_to_guest' => true,
                    'show_to_user' => false,
                    'notes' => $notes,
                ]
            );
        }

        foreach ($userServices as $serviceName) {
            Service::firstOrCreate(
                ['name' => $serviceName],
                [
                    'is_active' => true,
                    'show_to_guest' => false,
                    'show_to_user' => true,
                    'notes' => null,
                ]
            );
        }
    }
}
