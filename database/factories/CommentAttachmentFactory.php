<?php

namespace Database\Factories;

use App\Models\CommentAttachment;
use App\Models\TicketComment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommentAttachment>
 */
class CommentAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_comment_id' => TicketComment::factory(),
            'name' => $this->faker->word().'.pdf',
            'path' => 'comments/dummy/'.$this->faker->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'size' => $this->faker->numberBetween(1024, 10240), // 1KB - 10MB
        ];
    }
}
