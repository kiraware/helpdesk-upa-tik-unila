<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketComment>
 */
class TicketCommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Randomly decide if it's internal note (20% chance)
        $isInternal = fake()->boolean(20);

        return [
            'ticket_id' => Ticket::factory(),

            // Jika internal note, PASTI dari user (staff).
            // Jika publik, bisa dari user atau null (guest).
            'user_id' => $isInternal
                ? User::factory()
                : (fake()->boolean() ? User::factory() : null),

            'message' => fake()->paragraph(rand(1, 3)),
            'is_internal_note' => $isInternal,
            'attachment_path' => null,
            'created_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
