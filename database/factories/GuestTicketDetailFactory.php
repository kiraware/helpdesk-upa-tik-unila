<?php

namespace Database\Factories;

use App\Enums\IdentityType;
use App\Models\Department;
use App\Models\GuestTicketDetail;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuestTicketDetail>
 */
class GuestTicketDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory()->guest(),
            'full_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'identity_number' => fake()->numerify('##########'),
            'department_id' => Department::inRandomOrder()->first()?->id ?? Department::factory(),
            'entity_type' => fake()->randomElement(IdentityType::cases()),
            'photo_identity_path' => 'uploads/identities/dummy_ktp.jpg',
            'photo_selfie_path' => 'uploads/identities/dummy_selfie.jpg',
        ];
    }
}
