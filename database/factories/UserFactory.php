<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

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
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),

            // Data Dummy Helpdesk
            'identity_number' => fake()->numerify('##########'), // Random NIP/NPM
            'phone' => fake()->phoneNumber(),
            'role' => UserRole::USER, // Default user biasa
            'division_id' => null, // Default null
        ];
    }

    /**
     * State untuk membuat user sebagai ADMIN/STAFF
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::ADMIN,
            // Ambil random divisi id 1-5, atau buat baru jika kosong
            'division_id' => Division::inRandomOrder()->first()?->id ?? Division::factory(),
            'identity_number' => fake()->numerify('19##########'), // Format NIP
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
        ]);
    }
}
