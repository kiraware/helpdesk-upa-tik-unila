<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\CommentAttachment;
use App\Models\Ticket;
use App\Notifications\SystemNotification;
use App\Rules\ValidRecaptcha;
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
            'g-recaptcha-response' => ['required', new ValidRecaptcha],
        ]);

        // 1. KEAMANAN: Cek Status Tiket
        // Guest HANYA bisa membalas jika tiket berstatus Waiting atau Progress
        if (! in_array($ticket->status, [TicketStatus::WAITING, TicketStatus::PROGRESS])) {
            return back()->with('error', 'Komentar tidak dapat ditambahkan karena tiket ini sudah ditutup (Selesai/Ditolak).');
        }

        $comment = $ticket->comments()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        $unassignedAttachments = CommentAttachment::whereNull('ticket_comment_id')->get();

        foreach ($unassignedAttachments as $attachment) {
            if (str_contains($comment->message, $attachment->url)) {
                $attachment->update(['ticket_comment_id' => $comment->id]);
            }
        }

        // Jika tiket belum ada petugas, dan yang sedang login (berkomentar) adalah Admin / Superuser
        if (is_null($ticket->assigned_to) && auth()->check()) {
            if (in_array(auth()->user()->role, [UserRole::ADMIN, UserRole::SUPERUSER])) {
                $ticket->update([
                    'assigned_to' => auth()->id(),
                ]);

                // Refresh data tiket agar relasi assignee langsung terbaca di baris berikutnya
                $ticket->refresh();
            }
        }

        // 2. Jika sudah ada petugas -> Kirim ke Petugas
        // Catatan Tambahan: Kita tambahkan kondisi "&& $ticket->assigned_to !== auth()->id()"
        // agar petugas yang membalas tiketnya sendiri tidak mendapat notifikasi notifikasi "Balasan Tamu".
        if ($ticket->assigned_to && $ticket->assigned_to !== auth()->id()) {
            $ticket->assignee->notify(new SystemNotification(
                'Balasan Tamu',
                "{$ticket->guestDetail->full_name} membalas tiket #{$ticket->ticket_code} yang Anda tangani.",
                route('tickets.show', $ticket),
                'info'
            ));
        }

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
