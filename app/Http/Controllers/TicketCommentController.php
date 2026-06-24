<?php

namespace App\Http\Controllers;

use App\Channels\WhatsAppChannel;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Helpers\ImageSanitizer;
use App\Models\CommentAttachment;
use App\Models\Ticket;
use App\Notifications\SystemNotification;
use App\Rules\SafeFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TicketCommentController extends Controller
{
    /**
     * Menyimpan komentar baru untuk tiket.
     */
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate(['message' => 'required|string|min:20']);

        if (! in_array($ticket->status, [TicketStatus::WAITING, TicketStatus::PROGRESS])) {
            return back()->with('error', 'Komentar tidak dapat ditambahkan karena tiket ini sudah ditutup (Selesai/Ditolak).');
        }

        $user = $request->user();

        if ($user->role === UserRole::USER && $ticket->user_id !== $user->id) {
            return back()->with('error', 'Anda tidak memiliki akses ke tiket ini.');
        }

        DB::transaction(function () use ($request, $ticket, $user) {
            $comment = $ticket->comments()->create([
                'user_id' => $user->id,
                'message' => $request->message,
            ]);

            $unassignedAttachments = CommentAttachment::whereNull('ticket_comment_id')->get();

            foreach ($unassignedAttachments as $attachment) {
                if (str_contains($comment->message, $attachment->url)) {
                    $attachment->update(['ticket_comment_id' => $comment->id]);
                }
            }

            $isStaff = in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER]);

            if ($isStaff && is_null($ticket->assigned_to)) {
                $ticket->update([
                    'assigned_to' => $user->id,
                    'assigned_at' => now(),
                    'status' => TicketStatus::PROGRESS,
                ]);
            }
        });

        $isStaff = in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER]);

        if ($isStaff) {
            $title = 'Balasan Terbaru pada Tiket Anda';
            $message = "Petugas kami (*{$user->name}*) baru saja menambahkan balasan pada tiket Anda (*#{$ticket->ticket_code}* - Layanan: *{$ticket->service->name}*). Silakan klik tautan di bawah ini untuk membaca pesan tersebut dan memberikan tanggapan kembali jika diperlukan.";
            $channels = ['database', 'mail', WhatsAppChannel::class];

            if ($ticket->user && $ticket->user_id !== $user->id) {
                $ticket->user->notify(new SystemNotification(
                    $title,
                    $message,
                    route('tickets.show', $ticket),
                    'info',
                    $channels
                ));
            } elseif ($ticket->guestDetail) {
                Notification::route('mail', $ticket->guestDetail->email)
                    ->route(WhatsAppChannel::class, $ticket->guestDetail->phone)
                    ->notify(new SystemNotification(
                        $title,
                        $message,
                        route('guest.tracking.show', $ticket->ticket_code),
                        'info',
                        ['mail', WhatsAppChannel::class]
                    ));
            }
        } else {
            if ($ticket->assigned_to && $ticket->assigned_to !== $user->id) {

                $title = 'Aksi Diperlukan: Tanggapan Baru dari Pelapor';
                $message = "Pelapor (*{$user->name}*) telah menambahkan tanggapan baru pada tiket *#{$ticket->ticket_code}* (Layanan: *{$ticket->service->name}*) yang sedang Anda tangani. Mohon segera periksa detail tiket untuk meninjau pesan tersebut dan memberikan tindak lanjut.";
                $channels = ['database', 'mail', WhatsAppChannel::class];

                $ticket->assignee->notify(new SystemNotification(
                    $title,
                    $message,
                    route('tickets.show', $ticket),
                    'info',
                    $channels
                ));
            }
        }

        return back()->with('success', 'Komentar berhasil dikirim.');
    }

    /**
     * Handle upload file Trix Editor.
     */
    public function storeEmbeddedFile(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:2048', 'mimes:jpg,jpeg,png,pdf', new SafeFile],
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('comment-attachments', 'public');

            ImageSanitizer::sanitize(storage_path('app/public/'.$path), $file->getClientOriginalExtension());

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
