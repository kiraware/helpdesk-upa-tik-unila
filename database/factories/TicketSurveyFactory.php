<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketSurvey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketSurvey>
 */
class TicketSurveyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rating = fake()->numberBetween(3, 5);

        $feedbacks = [
            'Pelayanan sangat cepat dan ramah, terima kasih!',
            'Sudah cukup baik, namun respon awal agak lambat.',
            'Solusi tepat sasaran. Masalah saya selesai dalam sekejap.',
            'Petugas sangat membantu menjelaskan teknisnya.',
            'Sistem helpdesk mudah digunakan.',
            'Terima kasih atas bantuannya.',
            'Sangat memuaskan.',
            'Perlu ditingkatkan lagi kecepatan penanganannya.',
        ];

        return [
            'ticket_id' => Ticket::factory(), // Default, akan di-override di Seeder
            'overall_rating' => $rating,
            'feedback' => fake()->randomElement($feedbacks),
            'csi_score' => 0, // Nanti dihitung ulang di Seeder agar akurat
        ];
    }
}
