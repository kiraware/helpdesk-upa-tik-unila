<?php

namespace Database\Factories;

use App\Enums\UserEntity;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Division;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'username_sso' => fake()->unique()->userName(),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),

            // Data User
            'identity_number' => fake()->numerify('##########'),
            'phone' => fake()->phoneNumber(),
            'avatar_path' => null,
            'role' => UserRole::USER,
            'entity' => fake()->randomElement(array_column(UserEntity::cases(), 'value')),
            'division_id' => null,
            'department_id' => Department::inRandomOrder()->first()?->id ?? Department::factory(),
        ];
    }

    /**
     * State untuk membuat user sebagai ADMIN/STAFF
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::ADMIN,
            'division_id' => Division::inRandomOrder()->first()?->id ?? Division::factory(),
            'department_id' => null,
            'identity_number' => fake()->numerify('19##########'),
        ]);
    }

    /**
     * State untuk membuat user sebagai SUPERUSER
     */
    public function superuser(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::SUPERUSER,
            'division_id' => null,
            'department_id' => null,
        ]);
    }
}
