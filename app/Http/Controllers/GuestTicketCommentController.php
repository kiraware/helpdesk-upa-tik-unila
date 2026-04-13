<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Models\CommentAttachment;
use App\Models\Ticket;
use App\Notifications\SystemNotification;
use App\Rules\ValidTurnstile;
use Illuminate\Http\Request;

class GuestTicketCommentController extends Controller
{
    /**
     * Menyimpan balasan komentar khusus dari Guest.
     */
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
            'cf-turnstile-response' => ['required', new ValidTurnstile],
        ]);

        // 1. KEAMANAN: Cek Status Tiket
        // Guest HANYA bisa membalas jika tiket berstatus Waiting atau Progress
        if (! in_array($ticket->status, [TicketStatus::WAITING, TicketStatus::PROGRESS])) {
            return back()->with('error', 'Komentar tidak dapat ditambahkan karena tiket ini sudah ditutup (Selesai/Ditolak).');
        }

        $comment = $ticket->comments()->create([
            'user_id' => null, // Guest
            'message' => $request->message,
        ]);

        $unassignedAttachments = CommentAttachment::whereNull('ticket_comment_id')->get();

        foreach ($unassignedAttachments as $attachment) {
            if (str_contains($comment->message, $attachment->url)) {
                $attachment->update(['ticket_comment_id' => $comment->id]);
            }
        }

        // 2. Jika sudah ada petugas -> Kirim ke Petugas
        if ($ticket->assigned_to) {
            $ticket->assignee->notify(new SystemNotification(
                'Balasan Tamu',
                "{$ticket->guestDetail->full_name} membalas tiket #{$ticket->ticket_code} yang Anda tangani.",
                route('tickets.show', $ticket),
                'info'
            ));
        }
        // Blok ELSE untuk notifikasi "Balasan Tamu (Unassigned)" ke admin dihapus di sini

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    /**
     * Handle upload file (Gambar/Dokumen) via Trix Editor khusus Guest.
     */
    public function storeEmbeddedFile(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:2048', 'mimes:jpg,jpeg,png,pdf,doc,docx,zip'],
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('comment-attachments', 'public');

            $attachment = CommentAttachment::create([
                'ticket_comment_id' => null,
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);

            return response()->json([
                'url' => $attachment->url,
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
