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
        $questions = SurveyQuestion::active()->get();

        if ($questions->isEmpty()) {
            $this->command->error('Error: Survey Questions belum ada.');

            return;
        }

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
            $survey = TicketSurvey::factory()->create([
                'ticket_id' => $ticket->id,
                'csi_score' => 0,
            ]);

            $totalWeightScore = 0;
            $totalImportance = 0;

            foreach ($questions as $question) {
                $baseScore = $survey->overall_rating;

                // Generate skor
                $satScore = fake()->numberBetween(max(1, $baseScore - 1), 5);
                $impScore = fake()->numberBetween(3, 5);

                TicketSurveyAnswer::factory()->create([
                    'ticket_survey_id' => $survey->id,
                    'survey_question_id' => $question->id,
                    'satisfaction_score' => $satScore,
                    'importance_score' => $impScore,
                ]);

                // Hitung bobot per pertanyaan (Kepuasan x Kepentingan)
                $totalWeightScore += ($satScore * $impScore);
                $totalImportance += $impScore;
            }

            // Hitung persentase CSI per tiket
            // Skor maksimal terjadi jika semua nilai kepuasan adalah 5
            $maxPossibleScore = $totalImportance * 5;
            $csi = ($maxPossibleScore > 0) ? ($totalWeightScore / $maxPossibleScore) * 100 : 0;

            $survey->update([
                'csi_score' => round($csi, 2),
            ]);
        }
    }
}
