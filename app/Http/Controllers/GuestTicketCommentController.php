<?php

namespace App\Http\Controllers;

use App\Channels\WhatsAppChannel;
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
            'message' => 'required|string|min:20',
            'g-recaptcha-response' => ['required', new ValidRecaptcha],
        ]);

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

        if (is_null($ticket->assigned_to) && auth()->check()) {
            if (in_array(auth()->user()->role, [UserRole::ADMIN, UserRole::SUPERUSER])) {
                $ticket->update([
                    'assigned_to' => auth()->id(),
                    'assigned_at' => now(),
                    'status' => TicketStatus::PROGRESS,
                ]);

                $ticket->refresh();
            }
        }

        if ($ticket->assigned_to && $ticket->assigned_to !== auth()->id()) {
            $title = 'Balasan Baru dari Tamu';
            $guestName = $ticket->guestDetail->full_name;
            $message = "Terdapat balasan terbaru dari pelapor (*{$guestName}*) pada tiket *#{$ticket->ticket_code}* (Layanan: *{$ticket->service->name}*). Mohon segera periksa detail tiket untuk meninjau pesan tersebut dan memberikan tindak lanjut.";
            $channels = ['database', 'mail', WhatsAppChannel::class];

            $ticket->assignee->notify(new SystemNotification(
                $title,
                $message,
                route('tickets.show', $ticket),
                'info',
                $channels
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
            'file' => ['required', 'file', 'max:2048', 'mimes:jpg,jpeg,png,pdf'],
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
