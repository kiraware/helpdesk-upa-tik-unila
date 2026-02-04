<?php

namespace Database\Seeders;

use App\Enums\TicketStatus;
use App\Models\SurveyQuestion;
use App\Models\Ticket;
use App\Models\TicketSurvey;
use App\Models\TicketSurveyAnswer;
use Illuminate\Database\Seeder;

class TicketSurveySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil semua pertanyaan aktif
        $questions = SurveyQuestion::active()->get();

        if ($questions->isEmpty()) {
            $this->command->error('Error: Survey Questions belum ada. Jalankan SurveyQuestionSeeder terlebih dahulu.');

            return;
        }

        // 2. Ambil Tiket status DONE yang BELUM punya survei
        // Kita ambil misal 50 tiket saja agar tidak terlalu lama
        $tickets = Ticket::where('status', TicketStatus::DONE)
            ->doesntHave('survey')
            ->inRandomOrder()
            ->take(50)
            ->get();

        if ($tickets->isEmpty()) {
            $this->command->info('Tidak ada tiket DONE yang tersedia untuk disurvei.');

            return;
        }

        $this->command->info('Membuat survei untuk '.$tickets->count().' tiket...');

        foreach ($tickets as $ticket) {
            // A. Buat Header Survei dulu
            $survey = TicketSurvey::factory()->create([
                'ticket_id' => $ticket->id,
                // Kita kosongkan CSI dulu, nanti diupdate
                'csi_score' => 0,
            ]);

            $totalScore = 0;
            $maxScore = $questions->count() * 5; // Skor maksimal (Jumlah pertanyaan * 5)

            // B. Buat Jawaban untuk SETIAP pertanyaan
            foreach ($questions as $question) {
                // Generate skor acak yang "mirip" dengan overall_rating agar konsisten
                // Misal overall 5, jawabannya berkisar 4-5.
                $baseScore = $survey->overall_rating;
                $score = fake()->numberBetween(max(1, $baseScore - 1), 5);

                TicketSurveyAnswer::factory()->create([
                    'ticket_survey_id' => $survey->id,
                    'survey_question_id' => $question->id,
                    'score' => $score,
                ]);

                $totalScore += $score;
            }

            // C. Hitung CSI Score (Rumus: Total Skor / Skor Maksimal * 100)
            // Contoh: (18 / 20) * 100 = 90.00
            $csi = ($totalScore / $maxScore) * 100;

            // D. Update Header Survei
            $survey->update(['csi_score' => $csi]);
        }
    }
}
