<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Form>
 */
class FormFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3); // Contoh: "Survei Kepuasan Kantin"

        return [
            'title' => rtrim($title, '.'), // Hapus titik di akhir kalimat
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(),
            'is_active' => fake()->boolean(80), // 80% kemungkinan aktif
        ];
    }
}
