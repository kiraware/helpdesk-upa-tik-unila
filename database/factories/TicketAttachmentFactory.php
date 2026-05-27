<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketAttachment>
 */
class TicketAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'name' => $this->faker->word().'.jpg',
            'path' => 'tickets/dummy/'.$this->faker->uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => $this->faker->numberBetween(1024, 20480), // 1KB - 20MB
        ];
    }
}
