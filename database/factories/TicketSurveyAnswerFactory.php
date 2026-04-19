<?php

namespace Database\Factories;

use App\Models\SurveyQuestion;
use App\Models\TicketSurvey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketSurveyAnswer>
 */
class TicketSurveyAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_survey_id' => TicketSurvey::factory(),
            'survey_question_id' => SurveyQuestion::factory(),
            'satisfaction_score' => fake()->numberBetween(3, 5),
            'importance_score' => fake()->numberBetween(4, 5),
        ];
    }
}
