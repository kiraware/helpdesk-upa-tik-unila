<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Database\Seeder;

class FormSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil Form "Evaluasi Fasilitas"
        $formLab = Form::where('slug', 'like', '%evaluasi-fasilitas%')->first();

        // 2. Ambil beberapa user acak untuk jadi responden
        $users = User::inRandomOrder()->limit(5)->get();

        if ($formLab) {
            // Skenario A: 5 User Login mengisi kuesioner
            foreach ($users as $user) {
                FormSubmission::create([
                    'form_id' => $formLab->id,
                    'user_id' => $user->id,
                    'ip_address' => '192.168.1.'.rand(10, 99),
                    'created_at' => now()->subDays(rand(1, 7)),
                ]);
            }

            // Skenario B: 3 Orang Anonim mengisi kuesioner
            FormSubmission::factory(3)->create([
                'form_id' => $formLab->id,
                'user_id' => null, // Anonim
            ]);
        }

        // Dummy data untuk form lainnya
        FormSubmission::factory(5)->create();
    }
}
