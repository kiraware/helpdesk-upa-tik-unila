<?php

namespace Database\Seeders;

use App\Enums\FormQuestionType;
use App\Models\Form;
use App\Models\FormQuestion;
use Illuminate\Database\Seeder;

class FormQuestionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil Form "Evaluasi Fasilitas Laboratorium" (dibuat di FormSeeder)
        // Kita pakai where like untuk mencari slug/judulnya
        $formLab = Form::where('slug', 'like', '%evaluasi-fasilitas%')->first();

        if ($formLab) {
            $questions = [
                [
                    'text' => 'Berapa kali Anda menggunakan Lab Komputer dalam seminggu?',
                    'type' => FormQuestionType::RADIO,
                    'options' => ['1-2 kali', '3-5 kali', '> 5 kali'],
                    'req' => true,
                ],
                [
                    'text' => 'Bagaimana kecepatan internet di Lab Komputer?',
                    'type' => FormQuestionType::SCALE, // Skala 1-5
                    'options' => null,
                    'req' => true,
                ],
                [
                    'text' => 'Perangkat lunak (software) apa yang sering bermasalah?',
                    'type' => FormQuestionType::CHECKBOX,
                    'options' => ['Microsoft Office', 'SPSS', 'Browser', 'Matlab', 'Lainnya'],
                    'req' => false,
                ],
                [
                    'text' => 'Jelaskan kendala teknis yang paling sering Anda alami:',
                    'type' => FormQuestionType::TEXTAREA,
                    'options' => null,
                    'req' => true,
                ],
            ];

            foreach ($questions as $index => $q) {
                FormQuestion::create([
                    'form_id' => $formLab->id,
                    'question_text' => $q['text'],
                    'type' => $q['type'],
                    'options' => $q['options'],
                    'is_required' => $q['req'],
                    'order' => $index + 1, // Urutan 1, 2, 3...
                ]);
            }
        }

        // 2. Data Dummy Tambahan untuk form lain
        // Jika ada form lain, isi dengan factory acak
        $otherForms = Form::where('id', '!=', $formLab?->id)->get();
        foreach ($otherForms as $form) {
            FormQuestion::factory(5)->create(['form_id' => $form->id]);
        }
    }
}
