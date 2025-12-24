<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $service = Service::inRandomOrder()->first() ?? Service::factory()->create();
        $user = User::inRandomOrder()->first() ?? User::factory()->create();

        return [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'assigned_to' => null,
            'priority' => fake()->randomElement(
                array_column(TicketPriority::cases(), 'value')
            ),
            'status' => TicketStatus::WAITING,
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * State: Tiket yang sudah selesai (DONE)
     */
    public function closed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => TicketStatus::DONE,
                'assigned_to' => User::factory(), // Diambil staff dummy
                'assigned_at' => fake()->dateTimeBetween('-1 month', '-1 week'),
                'closed_at' => now(),
            ];
        });
    }

    /**
     * State: Tiket dari GUEST (Tanpa Login)
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
