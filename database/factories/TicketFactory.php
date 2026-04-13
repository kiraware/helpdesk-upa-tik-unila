<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
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

        // Mencari User dengan role USER, atau buat baru jika kosong
        $user = User::where('role', UserRole::USER)->inRandomOrder()->first()
                ?? User::factory()->create();

        return [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'assigned_to' => null,
            'priority' => fake()->randomElement(
                array_column(TicketPriority::cases(), 'value')
            ),
            'status' => TicketStatus::WAITING,
            'description' => fake()->paragraph(),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'assigned_at' => null,
            'closed_at' => null,
        ];
    }

    /**
     * State: Tiket yang sudah selesai (DONE)
     */
    public function closed(): static
    {
        return $this->state(function (array $attributes) {
            $staff = User::whereIn('role', [UserRole::ADMIN, UserRole::SUPERUSER])->inRandomOrder()->first()
                     ?? User::factory()->admin()->create();

            $assignedAt = fake()->dateTimeBetween('-1 month', '-1 week');

            return [
                'status' => TicketStatus::DONE,
                'assigned_to' => $staff->id,
                'assigned_at' => $assignedAt,
                'closed_at' => fake()->dateTimeBetween($assignedAt, 'now'),
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
