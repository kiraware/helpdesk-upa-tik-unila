<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormSubmission>
 */
class FormSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            // Random: 50% user login, 50% anonim
            'user_id' => fake()->boolean() ? User::factory() : null,
            'ip_address' => fake()->ipv4(),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
