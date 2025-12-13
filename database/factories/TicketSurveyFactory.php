<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketSurvey>
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
        return [
            'ticket_id' => Ticket::factory(),

            // Generate angka antara 3-5 agar data terlihat "PUAS" (umumnya user memberi nilai baik)
            // Sesekali beri nilai 1-2
            'score_access' => fake()->numberBetween(3, 5),
            'score_speed' => fake()->numberBetween(3, 5),
            'score_solution' => fake()->numberBetween(2, 5),
            'score_attitude' => fake()->numberBetween(4, 5),
            'score_overall' => fake()->numberBetween(3, 5),

            'feedback_comment' => fake()->optional(0.7)->sentence(10), // 70% ada komentar
        ];
    }

    /**
     * State: Review Buruk (Untuk testing alert/evaluasi)
     */
    public function badReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'score_access' => fake()->numberBetween(1, 2),
            'score_speed' => fake()->numberBetween(1, 2),
            'score_solution' => fake()->numberBetween(1, 2),
            'score_attitude' => fake()->numberBetween(1, 2),
            'score_overall' => fake()->numberBetween(1, 2),
            'feedback_comment' => 'Pelayanan sangat lambat dan tidak solutif.',
        ]);
    }
}
