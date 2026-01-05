<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Configuration>
 */
class ConfigurationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'upa_head_name' => fake()->name().', S.T., M.T.',
            'upa_head_nip' => fake()->numerify('19##########1###'),
            'upa_head_position' => 'Kepala UPA TIK',
        ];
    }
}
