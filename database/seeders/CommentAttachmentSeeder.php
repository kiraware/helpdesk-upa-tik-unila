<?php

namespace Database\Seeders;

use App\Models\CommentAttachment;
use App\Models\TicketComment;
use Illuminate\Database\Seeder;

class CommentAttachmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $comments = TicketComment::all();

        if ($comments->isEmpty()) {
            $this->command->info('Tidak ada komentar ditemukan. Seeder dilewati.');

            return;
        }

        foreach ($comments as $comment) {
            // Random: 70% kemungkinan tidak ada attachment, 30% ada 1-2 attachment
            $shouldHaveAttachment = rand(1, 10) > 7;

            if ($shouldHaveAttachment) {
                CommentAttachment::factory(rand(1, 2))->create([
                    'ticket_comment_id' => $comment->id,
                ]);
            }
        }
    }
}
