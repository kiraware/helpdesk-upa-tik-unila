<?php

namespace Database\Factories;

use App\Enums\FormQuestionType;
use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormQuestion>
 */
class FormQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Acak tipe pertanyaan
        $type = fake()->randomElement(FormQuestionType::cases());

        // Jika tipe butuh opsi (Radio/Checkbox), buat array opsi
        $options = match ($type) {
            FormQuestionType::RADIO, FormQuestionType::CHECKBOX => [
                fake()->word(),
                fake()->word(),
                fake()->word(),
            ],
            default => null,
        };

        return [
            'form_id' => Form::factory(),
            'question_text' => fake()->sentence().'?',
            'type' => $type,
            'options' => $options,
            'is_required' => fake()->boolean(80),
            'order' => fake()->numberBetween(1, 10),
        ];
    }
}
