<?php

namespace Database\Factories;

use App\Models\FormQuestion;
use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormAnswer>
 */
class FormAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submission_id' => FormSubmission::factory(),
            'question_id' => FormQuestion::factory(),
            'answer_value' => fake()->sentence(),
        ];
    }
}
