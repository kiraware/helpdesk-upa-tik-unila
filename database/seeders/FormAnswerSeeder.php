<?php

namespace Database\Seeders;

use App\Enums\FormQuestionType;
use App\Models\FormAnswer;
use App\Models\FormSubmission;
use Illuminate\Database\Seeder;

class FormAnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil semua Submisi yang BELUM punya jawaban
        // (Submisi ini dibuat di FormSubmissionSeeder sebelumnya)
        $submissions = FormSubmission::with('form.questions') // Eager load pertanyaan
            ->doesntHave('answers')
            ->get();

        if ($submissions->isEmpty()) {
            $this->command->info('Tidak ada submisi kosong yang perlu diisi jawaban.');

            return;
        }

        foreach ($submissions as $submission) {
            // Ambil pertanyaan-pertanyaan dari form terkait
            $questions = $submission->form->questions;

            foreach ($questions as $question) {
                // Generate jawaban sesuai tipe pertanyaan
                $answerValue = $this->generateAnswer($question);

                FormAnswer::create([
                    'submission_id' => $submission->id,
                    'question_id' => $question->id,
                    'answer_value' => $answerValue,
                ]);
            }
        }
    }

    /**
     * Helper untuk membuat jawaban palsu yang masuk akal
     */
    private function generateAnswer($question)
    {
        // $question->type sudah otomatis dicasting jadi Enum oleh Model
        switch ($question->type) {
            case FormQuestionType::SCALE:
                return (string) rand(3, 5); // Jawaban skala biasanya 3-5 (Netral/Puas)

            case FormQuestionType::RADIO:
                // Ambil satu opsi acak dari JSON options
                $opts = $question->options ?? ['Opsi A', 'Opsi B'];

                return $opts[array_rand($opts)];

            case FormQuestionType::CHECKBOX:
                // Ambil beberapa opsi acak
                $opts = $question->options ?? ['X', 'Y', 'Z'];
                $picked = fake()->randomElements($opts, rand(1, 2));

                return json_encode($picked); // Simpan sebagai JSON String

            case FormQuestionType::TEXTAREA:
                return fake()->paragraph();

            case FormQuestionType::DATE:
                return fake()->date();

            case FormQuestionType::TEXT:
            default:
                return fake()->sentence();
        }
    }
}
