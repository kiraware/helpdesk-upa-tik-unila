<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FormSeeder extends Seeder
{
    public function run(): void
    {
        // Contoh 1: Kuesioner Evaluasi Fasilitas IT (Aktif)
        $title1 = 'Evaluasi Fasilitas Laboratorium Komputer';
        Form::create([
            'title' => $title1,
            'slug' => Str::slug($title1),
            'description' => 'Mohon isi kuesioner ini untuk meningkatkan kualitas lab komputer di Gedung A.',
            'is_active' => true,
        ]);

        // Contoh 2: Pendaftaran Beta Tester (Non-Aktif / Sudah Tutup)
        $title2 = 'Pendaftaran Beta Tester Aplikasi Akademik V2';
        Form::create([
            'title' => $title2,
            'slug' => Str::slug($title2),
            'description' => 'Pendaftaran untuk mahasiswa yang ingin mencoba fitur baru SIAKADU.',
            'is_active' => false, // Sudah ditutup
        ]);

        // Tambahan dummy
        Form::factory(3)->create();
    }
}
