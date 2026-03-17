<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupOrphanAttachments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-orphan-attachments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan file attachment Trix yang tidak tertaut pada tiket/komentar setelah 24 jam';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Kita set batas waktu 24 jam yang lalu
        $batasWaktu = Carbon::now()->subHours(24);
        $totalDihapus = 0;

        $this->info('Memulai pembersihan attachment orphan...');

        // 1. Bersihkan Ticket Attachment
        $orphanTicketAttachments = TicketAttachment::whereNull('ticket_id')
            ->where('created_at', '<', $batasWaktu)
            ->get();

        foreach ($orphanTicketAttachments as $attachment) {
            // Hapus file fisik dari storage public
            if (Storage::disk('public')->exists($attachment->path)) {
                Storage::disk('public')->delete($attachment->path);
            }
            // Hapus data dari database
            $attachment->delete();
            $totalDihapus++;
        }

        // 2. Bersihkan Comment Attachment
        $orphanCommentAttachments = CommentAttachment::whereNull('ticket_comment_id')
            ->where('created_at', '<', $batasWaktu)
            ->get();

        foreach ($orphanCommentAttachments as $attachment) {
            // Hapus file fisik dari storage public
            if (Storage::disk('public')->exists($attachment->path)) {
                Storage::disk('public')->delete($attachment->path);
            }
            // Hapus data dari database
            $attachment->delete();
            $totalDihapus++;
        }

        $this->info("Selesai! Berhasil menghapus {$totalDihapus} file orphan.");
    }
}
